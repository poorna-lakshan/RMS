<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemBatch;
use Carbon\Carbon;
use DB;
class InventoryController extends Controller
{
     public function stockStatus(Request $request)
    {
        $warehouseId = $request->warehouse_id;
        $expiringDays = $request->expiring_days ?? 7;

        $query = ItemBatch::with(['item.category', 'warehouse'])
            ->where('qty', '>', 0)
            ->where('is_consumed', false);

        if ($warehouseId) {
            $query->where('ware_house_id', $warehouseId);
        }

        $stockStatus = $query
            ->select(
                'item_id',
                'ware_house_id',
                DB::raw('SUM(qty) as stock_qty'),
                DB::raw('SUM(qty * unit_cost) as stock_value'),
                DB::raw('MIN(expiry_date) as nearest_expiry')
            )
            ->groupBy('item_id', 'ware_house_id')
            ->get()
            ->map(function ($row) use ($expiringDays) {

                $row->status = 'OK';

                if ($row->nearest_expiry && Carbon::parse($row->nearest_expiry)->lt(now())) {
                    $row->status = 'EXPIRED';
                } elseif ($row->nearest_expiry && Carbon::parse($row->nearest_expiry)->lte(now()->addDays($expiringDays))) {
                    $row->status = 'EXPIRING_SOON';
                }

                // Reorder logic
                $minQty = optional($row->item)->minimum_qty ?? 0;
                if ($row->stock_qty <= $minQty) {
                    $row->status = 'REORDER';
                }

                return $row;
            });

        return response()->json([
            'warehouse_id' => $warehouseId,
            'stock_status' => $stockStatus
        ]);
    }
}
