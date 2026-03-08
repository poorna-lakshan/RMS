<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Main\DefaultController;
use App\Http\Controllers\Inventory\WarehouseController;
class SupPayController extends Controller
{
    public function getDefault()
    {
        $df = new DefaultController();
        $grn_code = $df->generateCode('grn');

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);



        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);


        return response()->json([
            'grn_code' => $grn_code,
            'warehouse_list' => $warehouse_list,
            'vendor_list' => $vendor_list,
        ]);
    }
}
