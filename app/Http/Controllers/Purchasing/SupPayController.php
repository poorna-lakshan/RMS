<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Models\Purchasing\GRNHeader;
use App\Models\Purchasing\PayHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class SupPayController extends Controller
{
    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('pay');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);

        $warehouse_list = $warehouse->index()->getData(true);

        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);

        $GRN_list = GRNHeader::whereColumn('net_total', '>', 'paid_amount')->get();


        return response()->json([
            'code' => $grn_code,
            'warehouse_list' => $warehouse_list,
            'vendor_list' => $vendor_list,
            'pending_list' => $GRN_list,
        ]);
    }

    public function index()
    {
        // Retrieve all ItemCategories
        $itemCategories = PayHeader::with('vendor', 'warehouse')->orderBy('id', 'desc')->get();

        return response()->json($itemCategories);
    }

    public function getById($id)
    {
        try {
            $grn = PayHeader::with([
                'vendor',
                'warehouse',
                'details',
            ])->findOrFail($id);

            return response()->json($grn);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Payment not found'], 404);
        }
    }

    public function void($id)
    {
        DB::beginTransaction();

        try {
            $pay = PayHeader::with('details')->findOrFail($id);

            if ($pay->is_void) {
                return response()->json([
                    'error' => 'Payment already voided'
                ], 400);
            }

            foreach ($pay->details as $detail) {

                $grn = GRNHeader::findOrFail($detail->grn_id);

                $newPaid = $grn->paid_amount - $detail->paid;

                if ($newPaid < 0) {
                    throw new \Exception('Invalid GRN paid amount after void');
                }

                $grn->update([
                    'paid_amount' => $newPaid
                ]);
            }

            // Mark payment as void
            $pay->update([
                'is_void' => true,
                'is_confirm' => false
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Payment voided successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function create(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'vendor_id' => 'required|integer',
            'ware_house_id' => 'required|integer',
            'payment_method_id' => 'required|integer',
            'details' => 'required|array|min:1',
            'details.*.grn_id' => 'required|integer',
            'details.*.paid' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Generate code
            $df = new DefaultController();
            $code = $df->generateCode('pay');

            // Create payment header
            $payHeader = PayHeader::create([
                'code' => $code,
                'date' => $request->date,
                'vendor_id' => $request->vendor_id,
                'ware_house_id' => $request->ware_house_id,
                'payment_method_id' => $request->payment_method_id,
                'gl_acc' => $request->gl_acc ?? null,
                'is_confirm' => false,
                'is_void' => false,
            ]);

            // Loop payment details
            foreach ($request->details as $row) {

                $grn = GRNHeader::findOrFail($row['grn_id']);

                $due = $grn->net_total - $grn->paid_amount;

                if ($row['paid'] > $due) {
                    throw new \Exception("Paid amount exceeds GRN due");
                }

                // Insert detail
                $payHeader->details()->create([
                    'grn_id' => $grn->id,
                    'total' => $grn->net_total,
                    'due' => $due,
                    'paid' => $row['paid'],
                ]);

                // Update GRN paid amount
                $grn->update([
                    'paid_amount' => $grn->paid_amount + $row['paid']
                ]);
            }

             $code = $df->UpdateCode('pay');


            DB::commit();

            return response()->json([
                'message' => 'Supplier payment created successfully',
                'id' => $payHeader->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
