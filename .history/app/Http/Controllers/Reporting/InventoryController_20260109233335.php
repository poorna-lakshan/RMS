<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemBatch;
use App\Models\Inventory\BOMHeader;
use App\Models\Inventory\BOMDetail;
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


     public function foodCosting(Request $request)
    {
        // Date range (default today)
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Get sold invoice details (excluding voids)
        $soldItems = InvoiceDetail::join('invoice_header', 'invoice_detail.reference_no', '=', 'invoice_header.id')
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$fromDate, $toDate])
            ->select(
                'invoice_detail.item_id',
                DB::raw('SUM(invoice_detail.qty) as sold_qty'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as total_sales')
            )
            ->groupBy('invoice_detail.item_id')
            ->get();

        $report = $soldItems->map(function ($sold) {

            // Get BOM for this menu/finished item
            $bom = BOMHeader::where('item_id', $sold->item_id)
                ->where('active', 1)
                ->with('details')
                ->first();

            $totalCost = 0;

            if ($bom && $bom->details) {
                foreach ($bom->details as $detail) {
                    // Cost = unit cost * qty used in BOM * sold quantity
                    $costPerUnit = optional($detail->item)->unit_cost ?? 0;
                    $totalCost += $costPerUnit * $detail->qty * $sold->sold_qty;
                }
            }

            $profit = $sold->total_sales - $totalCost;

            return [
                'item_id' => $sold->item_id,
                'item_name' => optional($bom->finalItem)->description ?? 'Unknown',
                'sold_qty' => $sold->sold_qty,
                'total_sales' => $sold->total_sales,
                'food_cost' => round($totalCost, 2),
                'profit' => round($profit, 2),
                'food_cost_percent' => $sold->total_sales > 0 ? round(($totalCost / $sold->total_sales) * 100, 2) : 0,
            ];
        });

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'food_costing' => $report
        ]);
    }
}
