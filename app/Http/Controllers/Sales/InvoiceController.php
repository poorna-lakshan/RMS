<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\Customer;
use App\Models\Inventory\Item;
use App\Models\Inventory\ItemCategory;


class InvoiceController extends Controller
{
   public function getAllData()
    {
        // Retrieve all data
        $items = Item::all();
        $categories = ItemCategory::all();
        $customers = Customer::all();

        // Structure the response
        $response = [
            'success' => true,
            'data' => [
                'items' => $items->toArray(),
                'categories' => $categories->toArray(),
                'customers' => $customers->toArray()
            ],
            'message' => 'All data retrieved successfully'
        ];

        return response()->json($response);
    }
}
