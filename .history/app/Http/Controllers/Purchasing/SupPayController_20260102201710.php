<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Models\Purchasing\GRNHeader;
use App\Models\Purchasing\PayHeader;
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

    public function create(Request $request)
    {

    }
}
