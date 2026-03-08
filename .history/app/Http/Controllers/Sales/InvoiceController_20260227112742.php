<?php

namespace App\Http\Controllers\Sales;

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
class InvoiceController extends Controller
{


    public function index()
    {
        $boms = InvoiceHeader::with(['customer', 'details.item'])->orderBy('created_at', 'desc')->get();
        return response()->json($boms);
    }

    public function getAllData()
    {
        // Load items with their category
        $items = Item::with('category')->whereIn('class_id', [1, 3])->get();
        $categories = ItemCategory::where('type', 3)->get();
        $customers = Customer::all();

        $steward = new StewardController();
        $stewards = $steward->index()->getData(true);

        $customer = new CustomerController();
        $customers = $customer->index()->getData(true);

        $Addones = new AddoneController();
        $Addones = $Addones->index()->getData(true);


        // Transform items so category description is included
        $itemsTransformed = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->description,
                'price' => $item->price_level1,
                'category' => $item->category ? $item->category->description : null,
                'image' => $item->image_url,
            ];
        });

        $PaymentMethods = PaymentMethod::all();

        // Structure the response
        $response = [
            'success' => true,
            'data' => [
                'items' => $itemsTransformed,
                'addons' => $Addones,
                'categories' => $categories,
                'customers' => $customers,
                'stewards' => $stewards,
                'payment_method' => $PaymentMethods,

            ],
            'message' => 'All data retrieved successfully'
        ];

        return response()->json($response);
    }


    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('grn');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);

        $item_list = Item::with(['category', 'itemClass', 'warehouses', 'skus'])->whereIn('class_id', [1, 3])->get();

        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);


        return response()->json([
            'grn_code' => $grn_code,
            'item_list' => $item_list,
            'warehouse_list' => $warehouse_list,
            'vendor_list' => $vendor_list,
            // 'vendor_list' => $customer_list,
            // 'vendor_list' => $steward_list,
        ]);
    }


    // public function SaveOrder(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'type' => 'required|string',
    //         'ware_house_id' => 'required|integer',
    //         'customer_id' => 'required|integer',
    //         'table' => 'required|string',
    //         'steward_id' => 'required|integer',
    //         'items' => 'required|array|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $df = new DefaultController();
    //         $order_code = $df->generateCode('ord');
    //         $kot_num = $this->generateKOTNumber();

    //         $headerData = [
    //             'reference_no' => $order_code,
    //             'kot' => $kot_num,
    //             'type' => $request->type,
    //             'category' => $request->category,
    //             'ware_house_id' => $request->ware_house_id,
    //             'customer_id' => $request->customer_id,
    //             'table' => $request->table,
    //             'user' => auth()->user()->name,
    //             'steward' => $request->steward_id,
    //         ];

    //         $OrderHeader = TableMaster::where('name', $request->table)
    //             ->update([
    //                 'is_booking' => 1
    //             ]);

    //         $OrderHeader = OrderHeader::updateOrCreate(
    //             ['id' => $request->id ?? 0],
    //             $headerData
    //         );

    //         $items = $request->items;
    //         foreach ($items as $item) {
    //             OrderDetail::create([
    //                 'order_id' => $OrderHeader->id,
    //                 'item_id' => $item['id'],
    //                 'comment' => $item['comment'] ?? '',
    //                 'qty' => $item['quantity'] ?? 0,
    //                 'unit_price' => $item['price'] ?? 0,
    //                 'status' => 'pending',
    //             ]);
    //         }

    //         if (!$request->id) {
    //             $df = new DefaultController();
    //             $df->UpdateCode('ord');
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'message' => $request->id ? 'Order updated successfully' : 'Order created successfully',
    //         ], $request->id ? 200 : 201);



    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'error' => 'Failed to create/update Order: ' . $e->getMessage()
    //         ], 500);
    //     }

    // }





    public function SaveOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',           // dineIn, takeAway
            'ware_house_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'table' => 'required|string',
            'steward_id' => 'required|integer',
            'items' => 'required|array|min:1',    // Items with id, quantity, price, comment
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $df = new DefaultController();
            $order_code = $df->generateCode('ord');
            $items = $request->items;

            // Determine categories (KOT/BOT/COT) dynamically from items' kitchens
            $categories = collect($items)->map(function ($item) {
                $kitchen = Item::find($item['id'])->kitchans;
                return $kitchen ? $kitchen->name : "-"; // 'KOT', 'BOT', etc.
            })->unique()->filter()->toArray();

            $parent_order_id = null;

            foreach ($categories as $cat) {

                // Generate category-wise KOT number
                $kot_num = $this->generateKOTNumber($cat);

                // Create order header for this category
                $headerData = [
                    'reference_no' => $order_code,
                    'kot' => $kot_num,
                    'type' => $request->type,
                    'category' => $cat,
                    'ware_house_id' => $request->ware_house_id,
                    'customer_id' => $request->customer_id,
                    'order_code' => '',
                    'table' => $request->table,
                    'user' => auth()->user()->name,
                    'steward_id' => $request->steward_id,
                ];

                $OrderHeader = OrderHeader::create($headerData);


                // Save only items belonging to this category
                foreach ($items as $item) {
                    $itemKitchen = Item::find($item['id'])->kitchans;
                    if ($itemKitchen && $itemKitchen->name == $cat) {
                        OrderDetail::create([
                            'order_id' => $OrderHeader->id,
                            'item_id' => $item['id'],
                            'comment' => $item['comment'] ?? '',
                            'qty' => $item['quantity'] ?? 0,
                            'unit_price' => $item['price'] ?? 0,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            // Mark table as booked
            TableMaster::where('name', $request->table)
                ->update(['is_booking' => 1]);

            // Update order code counter
            $df->UpdateCode('ord');

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create Order: ' . $e->getMessage()
            ], 500);
        }
    }






















    public function VoidOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string',
            'item_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $updated = OrderDetail::where('order_id', $request->order_id)
            ->where('item_id', $request->item_id)
            ->update([
                'is_void' => 1
            ]);

        // Check if there are any non-voided items left for this order
        $nonVoidCount = OrderDetail::where('order_id', $request->order_id)
            ->where('is_void', 0)
            ->count();

        // If no non-voided items, mark the header as void
        if ($nonVoidCount == 0) {
            $updatedHeader = OrderHeader::where('id', $request->order_id)
                ->update([
                    'is_void' => 1
                ]);
        }

        if ($updated) {
            return response()->json([
                'message' => 'Item voided successfully',
                'status' => true
            ]);
        } else {
            return response()->json([
                'message' => 'No matching item found for this order',
                'status' => false
            ], 404);
        }
    }


    protected function generateKOTNumber($category = 'KOT')
    {
        $lastKOT = OrderHeader::where('category', $category)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastKOT && $lastKOT->kot) {
            return $lastKOT->kot + 1;
        }

        return 1; // Start from 1 if no previous KOT
    }

    // public function generateKOTNumber()
    // {
    //     // Fetch latest KOT number for today
    //     $latestKot = DB::table('order_header')
    //         ->whereDate('created_at', now()->toDateString())
    //         ->orderBy('id', 'desc')
    //         ->value('kot');

    //     if ($latestKot) {
    //         // Extract last number (e.g., from KOT-20251101-005)
    //         $newNumber = (int) $latestKot + 1;
    //     } else {
    //         $newNumber = '1';
    //     }

    //     return $newNumber;
    // }




    public function get_tables()
    {
        $tables = TableMaster::all();

        foreach ($tables as $table) {

            // ALL active orders (not void/invoice/paid)
            $orders = OrderHeader::where('table', $table->name)
                ->where('is_void', 0)
                // ->where('is_invoice', 0)
                ->where('is_paid', 0)
                ->orderBy('id')
                ->get(); // ascending → first order is oldest

            if ($orders->count() > 0) {

                // 1️⃣ First (oldest) order
                $firstOrder = $orders->first();

                // 2️⃣ Calculate grand total across ALL orders
                $pendingTotal = 0;


                foreach ($orders as $order) {
                    $total = OrderDetail::where('order_id', $order->id)
                        ->where('is_void', 0)
                        ->selectRaw('SUM(qty * unit_price) as total')
                        ->value('total');

                    $pendingTotal += ($total ?? 0);
                }

                if ($firstOrder->type == 'Table') {
                    $pendingTotal = $pendingTotal * 1.1;
                }

                // 3️⃣ Time of first order
                $firstTime = $firstOrder->created_at->format('h:i A');

                // 4️⃣ Duration from first order
                $duration = now()->diffInMinutes($firstOrder->created_at);

                // Assign values
                $table->pending_total = number_format($pendingTotal, 2, '.', '');
                $table->duration = $duration;
                $table->time = $firstTime;
                $table->status = 1;
                if ($firstOrder->is_invoice) {
                    $table->status = 2;
                    $table->invoice_id = $firstOrder->invoice_id;
                } else {
                    $table->status = 1;
                }

            } else {
                $table->pending_total = 0;
                $table->duration = null;
                $table->time = null;
                $table->status = 0;
            }
        }

        return response()->json($tables);
    }







    public function get_order($table)
    {
        // Fetch ALL active orders for the table
        $orders = OrderHeader::with([
            'customer',
            'steward',
            'details' => function ($q) {
                $q->where('is_void', 0)
                    ->with('item');
            }
        ])
            ->where('table', $table)
            ->where('is_void', 0)
            ->where('is_invoice', 0)
            ->where('is_paid', 0)
            ->orderBy('id', 'ASC')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No active order for this table',
                'data' => null
            ]);
        }

        // Add total & duration for each order
        foreach ($orders as $order) {

            // Total using only NON-VOID details (already filtered above)
            $order->total = $order->details->sum(function ($detail) {
                return $detail->qty * $detail->unit_price;
            });

            // Duration in minutes from first order time
            $order->duration = now()->diffInMinutes($order->created_at);
        }

        return response()->json($orders);
    }





    public function SaveInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ware_house_id' => 'required|integer',
            'customer_id' => 'required|integer',
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

        try {
            $df = new DefaultController();

            //----------------------------
            // 🔹 CHECK IF UPDATE OR CREATE
            //----------------------------
            $existing = null;
            if ($request->id) {
                $existing = InvoiceHeader::find($request->id);
                if (!$existing) {
                    return response()->json(['error' => 'Invoice not found'], 404);
                }
            }


            $OrderHeader = TableMaster::where('name', $request->table)
                ->update([
                    'is_booking' => 2
                ]);

            //----------------------------
            // 🔹 GENERATE CODES ONLY FOR NEW
            //----------------------------
            if ($existing) {
                $code = $existing->reference_no;
                $kot_num = $existing->kot;
            } else {
                $code = $df->generateCode('inv');
                $kot_num = $this->generateKOTNumber();
            }


            //----------------------------
            // 🔹 BUILD HEADER DATA
            //----------------------------
            $headerData = [
                'reference_no' => $code,
                'kot' => $kot_num,
                'type' => $request->type,
                'category' => $request->category,
                'ware_house_id' => $request->ware_house_id,
                'customer_id' => $request->customer_id,
                'order_code' => $request->order_code,
                'user' => auth()->user()->name,
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
            $InvoiceHeader = InvoiceHeader::updateOrCreate(
                ['id' => $request->id],
                $headerData
            );


            $createdOrderIds = [];

            if ($request->category <> 'Table') {
                $totalPaid = 0;
                $pm = '';
                $i = 0;
                foreach ($request->payments as $pay) {
                    $i++;
                    if (count($request->payments) == 1) {
                        $pm = $pay['method_name'];
                        InvPaymentMethod::create([
                            'trans_id' => $InvoiceHeader->id,
                            'payment_method_id' => $pay['method_id'],
                            'amount' => $pay['invoice_total'] ?? $pay['amount'],
                        ]);
                    } else {
                        $pm .= ($i == 1 ? '' : '/') . $pay['method_name'];
                        InvPaymentMethod::create([
                            'trans_id' => $InvoiceHeader->id,
                            'payment_method_id' => $pay['method_id'],
                            'amount' => $pay['amount'],
                        ]);
                    }
                    $totalPaid += $pay['amount'];
                }

                $df = new DefaultController();
                $order_code = $df->generateCode('ord');

                // Determine categories (KOT/BOT/COT) based on kitchen
                $categories = collect($request->items)->map(function ($item) {
                    $k = Item::find($item['id'])->kitchans;
                    return $k ? $k->name : 'KOT';
                })->unique()->toArray();

                foreach ($categories as $cat) {

                    // Generate category-wise KOT
                    $kot_num = $this->generateKOTNumber($cat);

                    // Create header
                    $orderHeader = OrderHeader::create([
                        'reference_no' => $order_code,
                        'kot' => $kot_num,
                        'type' => $request->category,
                        'is_invoice' => '1',
                        'invoice_id' => $InvoiceHeader->id,
                        'category' => $cat,
                        'ware_house_id' => $request->ware_house_id,
                        'customer_id' => $request->customer_id,
                        'order_code' => $request->order_code,
                        'table' => '-',
                        'user' => auth()->user()->name,
                        'steward_id' => $request->steward_id,
                    ]);

                    $createdOrderIds[] = $orderHeader->id;

                    // Items for this category
                    foreach ($request->items as $it) {
                        $k = Item::find($it['id'])->kitchans;
                        if (!$k || $k->name != $cat)
                            continue;

                        OrderDetail::create([
                            'order_id' => $orderHeader->id,
                            'item_id' => $it['id'],
                            'comment' => $it['comment'] ?? '',
                            'qty' => $it['quantity'],
                            'unit_price' => $it['price'],
                            'status' => 'pending',
                        ]);
                    }
                }
                $df->UpdateCode('ord');

                InvoiceHeader::where('id', $InvoiceHeader->id)->update([
                    'payment_method' => $pm,
                    'paid_total' => $totalPaid,
                    'balance' => $InvoiceHeader->net_total - $totalPaid,
                    'is_full_pay' => $totalPaid >= $InvoiceHeader->net_total ? 1 : 0,
                ]);
            }


            //----------------------------
            // 🔹 DELETE OLD DETAILS IF UPDATE
            //----------------------------
            if ($request->id) {
                InvoiceDetail::where('reference_no', $InvoiceHeader->id)->delete();
            }

            // Extract all order IDs from uniqueId field in ONE line
            $orderIds = collect($request->items)
                ->pluck('uniqueId')
                ->flatMap(fn($id) => explode('|', $id))
                ->unique()
                ->toArray();

            // Update all in one query
            OrderHeader::whereIn('id', $orderIds)->update([
                'is_invoice' => 1,
                'invoice_id' => $InvoiceHeader->id
            ]);
            //----------------------------
            // 🔹 SAVE INVOICE DETAILS
            //----------------------------
            $line = 1;

            $stockController = new StockController();

            foreach ($request->items as $item) {

                $ids = array_map(fn($p) => explode('|', $p)[0], explode(',', $item['uniqueId']));

                OrderHeader::whereIn('id', $ids)->update([
                    'is_invoice' => 1,
                    'invoice_id' => $InvoiceHeader->id
                ]);

                $class_id = Item::where('id', $item['id'])->value('class_id');
                if ($class_id == 3) {
                    $unitCost = $stockController->consumeBOM(
                        $item['id'],
                        $request->ware_house_id,
                        $item['quantity'],
                    );
                }
                if ($class_id == 1) {
                    $unitCost = $stockController->updateStockAndActivity(
                        $code,
                        'INV',
                        $request->ware_house_id,
                        $item['id'],
                        $item['quantity'] * -1,
                        false,
                    );
                }
                InvoiceDetail::create([
                    'reference_no' => $InvoiceHeader->id,
                    'line_no' => $line++,
                    'item_id' => $item['id'],
                    'comment' => $item['comment'] ?? '',
                    'qty' => $item['quantity'] ?? 0,
                    'unit_cost' => $unitCost ?? 0,
                    'unit_price' => $item['price'] ?? 0,
                ]);

            }

            //----------------------------
            // 🔹 UPDATE COUNTER ONLY FOR NEW
            //----------------------------
            if (!$request->id) {
                $df->UpdateCode('inv');
            }

            DB::commit();

            return response()->json([
                'message' => $request->id ? 'Invoice updated successfully' : 'Invoice created successfully',
                'invoice_id' => $InvoiceHeader->id,
                'reference_no' => $InvoiceHeader->reference_no,
                'created_at' => $InvoiceHeader->created_at->format('Y-m-d h:i A'),
                'user' => auth()->user()->name
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



    public function saveInvoicePayments(Request $request, $invoiceId)
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array|min:1',
            'payments.*.method_id' => 'required|integer|exists:payment_method,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Delete old payments (if updating)
            InvPaymentMethod::where('trans_id', $invoiceId)->delete();

            $totalPaid = 0;
            $totalPaid_wb = 0;
            $pm = '';
            $i = 0;

            foreach ($request->payments as $pay) {
                $i++;
                if (count($request->payments) == 1) {
                    $pm = $pay['method_name'];
                    InvPaymentMethod::create([
                        'trans_id' => $invoiceId,
                        'payment_method_id' => $pay['method_id'],
                        'amount' => $pay['invoice_total'],
                    ]);
                    $totalPaid_wb += $pay['invoice_total'];
                } else {
                    if (1 == $i) {
                        $pm .= $pay['method_name'] . '/';
                    } else {
                        $pm .= $pay['method_name'];
                    }
                    InvPaymentMethod::create([
                        'trans_id' => $invoiceId,
                        'payment_method_id' => $pay['method_id'],
                        'amount' => $pay['amount'],
                    ]);
                    $totalPaid_wb += $pay['amount'];
                }

                $totalPaid += $pay['amount'];
            }

            // Update invoice paid + balance
            InvoiceHeader::where('id', $invoiceId)->update([
                'payment_method' => $pm,
                'paid_total' => $totalPaid,
                'paid_total_wb' => $totalPaid_wb,
                'balance' => DB::raw("net_total - $totalPaid"),
                'is_full_pay' => $totalPaid >= InvoiceHeader::find($invoiceId)->net_total ? 1 : 0,
            ]);

            OrderHeader::where('invoice_id', $invoiceId)->update([
                'is_paid' => 1,
            ]);

            DB::commit();

            return response()->json(['message' => 'Payments saved'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }





    public function check_cashier_session()
    {
        $cashierSession = CashierSessions::where('user', auth()->user()->name)
            ->where('is_in', 1)
            ->where('is_out', 0)
            ->orderBy('in_time', 'desc')
            ->first();


        if ($cashierSession) {

            $invoices = InvoiceHeader::whereBetween('created_at', [$cashierSession->in_time, now()])
                ->where('user', $cashierSession->user)
                ->where('is_void', false)
                ->get();

            $payment_summary = InvPaymentMethod::select(
                'payment_method_id',
                DB::raw('SUM(amount) as total_amount')
            )
                ->with('paymentMethod:id,description')
                ->whereIn('trans_id', $invoices->pluck('id'))
                ->groupBy('payment_method_id')
                ->get();

            $cash_total = $payment_summary
                ->firstWhere('paymentMethod.description', 'cash')
                ->total_amount ?? 0;

            $cashierSession->cash_total = $cash_total + $cashierSession->in_amt;


        }








        return response()->json([
            'active' => true,
            'current_session' => $cashierSession,
        ]);


    }


    public function saveCashierSession(Request $request)
    {
        $request->validate([
            'in_amt' => 'required|numeric|min:0',
        ]);

        $user = auth()->user()->name; // or ->id if you prefer

        DB::beginTransaction();
        try {

            // 🔴 Prevent multiple open sessions
            $existingSession = CashierSessions::where('user', $user)
                ->where('is_in', 1)
                ->where('is_out', 0)
                ->first();

            if ($existingSession) {
                return response()->json([
                    'message' => 'Cashier session already open'
                ], 409);
            }

            // 🔹 Generate session code
            $code = 'CS-' . now()->format('YmdHis');

            // 🔹 Create session
            $session = CashierSessions::create([
                'code' => $code,
                'in_time' => Carbon::now(),
                'user' => $user,
                'is_in' => 1,
                'is_out' => 0,
                'in_amt' => $request->in_amt,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Cashier session started successfully',
                'data' => $session
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function closeCashierSession(Request $request)
    {
        $request->validate([
            'out_amt' => 'required|numeric|min:0',
        ]);

        $user = auth()->user()->name;

        $session = CashierSessions::where('user', $user)
            ->where('is_in', 1)
            ->where('is_out', 0)
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No active cashier session found'
            ], 404);
        }

        $summary = $this->getSessionSummary($session->id);

        $session->update([
            'out_time' => now(),
            'out_amt' => $request->out_amt,
            'is_out' => 1,
            'is_in' => 0,

        ]);

        return response()->json([
            'message' => 'Cashier session closed successfully',
            'data' => $session,
            'summary' => $summary,
        ]);
    }




    private function getSessionSummary($sessionId)
    {
        $session = CashierSessions::findOrFail($sessionId);

        $invoices = InvoiceHeader::whereBetween('created_at', [$session->in_time, now()])
            ->where('user', $session->user)
            ->where('is_void', false)
            ->get();

        $invoice_items = InvoiceDetail::select(
            'item_id',
            'unit_price',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(qty) * unit_price as total_price') // optional, if you have total_price column
        )
            ->with(['item'])
            ->whereIn('reference_no', $invoices->pluck('id'))
            ->groupBy('item_id', 'unit_price')
            ->get();

        $payment_summary = InvPaymentMethod::select(
            'payment_method_id',
            DB::raw('SUM(amount) as total_amount')
        )
            ->with('paymentMethod:id,description')
            ->whereIn('trans_id', $invoices->pluck('id'))
            ->groupBy('payment_method_id')
            ->get();

        $cash_total = $payment_summary
            ->firstWhere('paymentMethod.description', 'cash')
            ->total_amount ?? 0;
        $category_totals = $invoices
            ->groupBy('category')
            ->map(function ($items) {
                return [
                    'total_invoices' => $items->count(),
                    'total_sales' => $items->sum('net_total'),
                ];
            });

        $summary = [
            'total_invoices' => $invoices->count(),
            'total_sales' => $invoices->sum('net_total'),
            'total_payments' => $invoices->sum('paid_total'),
            'total_balance' => $invoices->sum('balance'),
            'total_discount' => $invoices->sum('dis_total'),
            'total_service_charge' => $invoices->sum('service_charge_total'),
            'total_delivery_charge' => $invoices->sum('delivery_charge'),
            'cash_sales' => $invoices->where('payment_method', 'cash')->sum('paid_total'),
            'card_sales' => $invoices->where('payment_method', 'card')->sum('paid_total'),
            'credit_sales' => $invoices->where('payment_method', 'credit')->sum('paid_total'),
            'other_sales' => $invoices->whereNotIn('payment_method', ['cash', 'card', 'credit'])->sum('paid_total'),
            'opening_amount' => $session->in_amt,
            'expected_total' => $session->in_amt + $invoices->where('payment_method', 'cash')->sum('paid_total'),
            'invoice_items' => $invoice_items,
            'payment_summary' => $payment_summary,
            'category_totals' => $category_totals,
            'expences' => 0,
            'total_deduct' => 0,
            'cash_balance' => $cash_total + $session->in_amt,

        ];

        return $summary;
    }












}
