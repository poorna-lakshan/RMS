<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventory\ItemController;
use Illuminate\Http\Request;

class GRNController extends Controller
{
     public function getDefault()
    {
        $df = new DefaultController();
        $item_code = $df->generateCode('grn');


        $ItemController = new ItemController();
        $item_list = $ItemController->index()->getData(true);

        $vendor = new VendorController();
        $vendor_list = $vendor->index()->getData(true);


        return response()->json([
            'item_list' => $item_list,
            'vendor_list' => $vendor_list,
        ]);
    }
}
