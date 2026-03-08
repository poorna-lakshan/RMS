<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\default\TableMaster;
use App\Models\Sales\InvoiceDetail;
use App\Models\Sales\InvoiceHeader;
use App\Models\Sales\InvPaymentMethod;
use App\Models\Sales\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Sales\Customer;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemCategory;
use App\Models\Sales\OrderHeader;
use App\Models\Sales\OrderDetail;
use App\Models\Sales\CashierSessions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\StockController;
use function PHPUnit\Framework\returnArgument;
use Carbon\Carbon;

class SyncClientDataController extends Controller
{
    public function SaveOrderSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_no' => 'required|string',
            'type' => 'required|string',
            'kot' => 'nullable|string',
            'order_code' => 'nullable|string',
            'category' => 'nullable|string',
            'ware_house_id' => 'required|integer',
            'table' => 'required|string',
            'user' => 'nullable|string',
            'steward_id' => 'required|integer',

            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.item_code' => 'nullable|string',
            'items.*.item_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {


            $customerId = $request->customer_id; // default

            if ($request->filled('customer')) {

                $cust = $request->customer;

                // Find customer by unique code (recommended)
                $customer = Customer::where('code', $cust['CutomerID'])->first();

                if (!$customer) {
                    // Create new customer
                    $customer = Customer::create([
                        'code' => $cust['CutomerID'],
                        'name' => $cust['CustomerName'],
                        'contact' => $cust['Phone1'] ?? null,
                        'address1' => $cust['Address1'] ?? null,
                        'address2' => $cust['Address2'] ?? null,
                        'email' => $cust['Email'] ?? null,
                        'vat_no' => $cust['VATNo'] ?? null,
                        'type' => $cust['Cus_Type'] ?? null,
                        'custom1' => $cust['Custom1'] ?? null,
                        'custom2' => $cust['Custom2'] ?? null,
                        'custom3' => $cust['Custom3'] ?? null,
                        'custom4' => $cust['Custom4'] ?? null,
                        'custom5' => $cust['Custom5'] ?? null,
                    ]);
                }

                // Use DB customer id
                $customerId = $customer->id;
            }

            /* ---------------- Order Header ---------------- */

            $headerData = [
                'reference_no' => $request->reference_no,
                'kot' => $request->kot,
                'type' => $request->type,
                'category' => $request->category,
                'ware_house_id' => $request->ware_house_id,
                'order_code' => $request->order_code,
                'table' => $request->table,
                'customer_id' => $customerId,
                'user' => $request->user ?? auth()->user()->name,
                'steward_id' => $request->steward_id,

                // sync flags (safe defaults)
                'is_void' => $request->is_void ?? 0,
                'is_invoice' => $request->is_invoice ?? 0,
                'invoice_id' => $request->invoice_id ?? 0,
                'is_paid' => $request->is_paid ?? 0,
            ];

            $OrderHeader = OrderHeader::create($headerData);

            /* ---------------- Order Items ---------------- */

            foreach ($request->items as $item) {
                OrderDetail::create([
                    'order_id' => $OrderHeader->id,
                    'item_id' => $item['item_id'],
                    'comment' => $item['comment'] ?? '',
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $OrderHeader->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create Order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orderVoidSync(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            /* ----------- Find Order ----------- */
            $order = OrderHeader::where('reference_no', $request->reference_no)->first();

            if (!$order) {
                return response()->json([
                    'error' => 'Order not found'
                ], 404);
            }



            /* ----------- Void Order Items ----------- */
            OrderDetail::where('order_id', $order->id)
                ->update([
                    'is_void' => 1,
                    'void_reason' => $request->void_reason ?? '',
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Order voided successfully',
                'reference_no' => $request->reference_no
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to void order: ' . $e->getMessage()
            ], 500);
        }
    }


    public function SaveInvoiceSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ware_house_id' => 'required|integer',
            'customer_id' => 'required|string',
            'category' => 'required|string',
            'payment_method' => 'required|string',
            'item_count' => 'required|integer',
            'gross_total' => 'required',
            'dis_per' => 'required',
            'dis_total' => 'required',
            'vat_per' => 'required',
            'vat_total' => 'required',
            'nbt_per' => 'required',
            'nbt_total' => 'required',
            'service_charge_per' => 'required',
            'service_charge_total' => 'required',
            'delivery_charge' => 'required',
            'net_total' => 'required',
            'total_cost' => 'required',
            'credit_total' => 'required',
            'paid_total' => 'required',
            'balance' => 'required',
            'items' => 'required|array',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $customer = Customer::where('code', $request->customer_id)->first();

        try {

            $headerData = [
                'reference_no' => $request->reference_no,
                'type' => $request->type,
                'category' => $request->category,
                'ware_house_id' => $request->ware_house_id,
                'customer_id' => $customer->id,
                'order_code' => $request->order_code,
                'user' => $request->user,
                'payment_method' => $request->payment_method,
                'item_count' => $request->item_count,
                'gross_total' => $request->gross_total,
                'dis_per' => $request->dis_per,
                'dis_total' => $request->dis_total,
                'vat_per' => $request->vat_per,
                'vat_total' => $request->vat_total,
                'nbt_per' => $request->nbt_per,
                'nbt_total' => $request->nbt_total,
                'service_charge_per' => $request->service_charge_per,
                'service_charge_total' => $request->service_charge_total,
                'delivery_charge' => $request->delivery_charge,
                'net_total' => $request->net_total,
                'total_cost' => $request->total_cost,
                'credit_total' => $request->credit_total,
                'paid_total' => $request->paid_total,
                'balance' => $request->balance,
                'is_full_pay' => $request->paid_total >= $request->net_total ? 1 : 0,
                'steward' => $request->steward_id,
                'table' => $request->table,
                'is_void' => 0,
            ];

            //----------------------------
            // 🔹 CREATE OR UPDATE HEADER
            //----------------------------
            $InvoiceHeader = InvoiceHeader::create(
                $headerData
            );

            if (!empty($request->order_code)) {

                $orderIds = collect(explode('|', $request->order_code))
                    ->filter(fn($id) => is_numeric($id))
                    ->unique()
                    ->toArray();

                if (!empty($orderIds)) {
                    OrderHeader::whereIn('kot', $orderIds)->update([
                        'is_invoice' => 1,
                        'invoice_id' => $InvoiceHeader->id,
                    ]);
                }
            }



            $stockController = new StockController();

            foreach ($request->items as $item) {
                $class_id = Item::where('id', $item['item_id'])->value('class_id');
                if ($class_id == 3) {
                    $unitCost = $stockController->consumeBOM(
                        $item['id'],
                        $request->ware_house_id,
                        $item['qty'],
                    );
                }
                if ($class_id == 1) {
                    $unitCost = $stockController->updateStockAndActivity(
                        $request->reference_no,
                        'INV',
                        $request->ware_house_id,
                        $item['item_id'],
                        $item['qty']*-1,
                        false,
                    );
                }
                $line = 1;
                InvoiceDetail::create([
                    'reference_no' => $InvoiceHeader->id,
                    'line_no' => $line++,
                    'item_id' => $item['item_id'],
                    'comment' => $item['comment'] ?? '',
                    'qty' => $item['qty'] ?? 0,
                    'unit_cost' => $unitCost ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);

            }

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice_id' => $InvoiceHeader->id,
                'reference_no' => $InvoiceHeader->reference_no,
                'created_at' => $InvoiceHeader->created_at->format('Y-m-d h:i A'),
                'user' => $request->user ?? auth()->user()->name
            ], $request->id ? 200 : 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create/update Invoice: ' . $e->getMessage(),
            ], 500);
        }
    }




    public function voidInvoiceByReference(Request $request)
    {
        $request->validate([
            'reference_no' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            $invoice = InvoiceHeader::where('reference_no', $request->reference_no)
                ->where('is_void', 0)
                ->firstOrFail();

            // 1️⃣ Mark invoice as void
            $invoice->update([
                'is_void' => 1
            ]);

            // 2️⃣ Void invoice details
            $details = InvoiceDetail::where('reference_no', $invoice->id)->get();

            $stockController = new StockController();

            foreach ($details as $detail) {

                $class_id = Item::where('id', $detail->item_id)->value('class_id');

                // 🔹 BOM item → restore components
                if ($class_id == 3) {
                    $stockController->restoreBOM(
                        finishedItemId: $detail->item_id,
                        warehouseId: $invoice->ware_house_id,
                        producedQty: $detail->qty,
                        referenceNo: $invoice->reference_no
                    );
                }

                // 🔹 Normal item → add stock back
                if ($class_id == 1) {
                    $stockController->updateStockAndActivity(
                        reference_no: $invoice->reference_no,
                        trans_type: 'INV-VOID',
                        warehouseId: $invoice->ware_house_id,
                        itemId: $detail->item_id,
                        qty: $detail->qty, // ADD BACK
                        doc_reference: true,
                        unitCost: $detail->unit_cost,
                        isNegativeAllowed: true
                    );
                }
            }


            DB::commit();

            return response()->json([
                'message' => 'Invoice voided successfully',
                'reference_no' => $invoice->reference_no
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function invoicePaymentSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|integer|exists:payment_method,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $InvoiceHeader = InvoiceHeader::where('reference_no', $request->reference_no)->first();

        try {
            // Delete old payments (if updating)
            InvPaymentMethod::where('trans_id', $request->reference_no)->delete();

            $totalPaid = 0;
            $totalPaid_wb = 0;
            $pm = '';
            $i = 0;

            foreach ($request->payments as $pay) {
                $i++;
                $pm_name = PaymentMethod::where('id', $pay['payment_method_id'])->value('description');
                if (count($request->payments) == 1) {
                    $pm = $pm_name;
                    InvPaymentMethod::create([
                        'trans_id' => $InvoiceHeader->id,
                        'payment_method_id' => $pay['payment_method_id'],
                        'amount' => $pay['amount'],
                    ]);
                    $totalPaid_wb += $pay['amount'];
                } else {
                    if (1 == $i) {
                        $pm .= $pm_name . '/';
                    } else {
                        $pm .= $pm_name;
                    }
                    InvPaymentMethod::create([
                        'trans_id' => $InvoiceHeader->id,
                        'payment_method_id' => $pay['payment_method_id'],
                        'amount' => $pay['amount'],
                    ]);
                    $totalPaid_wb += $pay['amount'];
                }

                $totalPaid += $pay['amount'];
            }

            // Update invoice paid + balance
            InvoiceHeader::where('id', $InvoiceHeader->id)->update([
                'payment_method' => $pm,
                'paid_total' => $totalPaid,
                'paid_total_wb' => $totalPaid_wb,
                'balance' => DB::raw("net_total - $totalPaid"),
                'is_full_pay' => $totalPaid >= InvoiceHeader::find($InvoiceHeader->id)->net_total ? 1 : 0,
            ]);

            OrderHeader::where('invoice_id', $InvoiceHeader->id)->update([
                'is_paid' => 1,
            ]);

            DB::commit();

            return response()->json(['message' => 'Payments saved'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
