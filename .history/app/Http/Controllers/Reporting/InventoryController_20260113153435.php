<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemBatch;
use App\Models\Inventory\ItemActivity;
use Carbon\Carbon;
use App\Models\Sales\InvoiceDetail;
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

    public function foodCostingNoBOM(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Get sold items (exclude voided invoices)
        $soldItems = InvoiceDetail::join('invoice_header', 'invoice_detail.reference_no', '=', 'invoice_header.id')
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$fromDate, $toDate])
            ->select(
                'invoice_detail.item_id',
                DB::raw('SUM(invoice_detail.qty) as sold_qty'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as total_sales'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_cost) as total_cost')
            )
            ->groupBy('invoice_detail.item_id')
            ->with('item') // eager load item info
            ->get()
            ->map(function ($row) {
                $profit = $row->total_sales - $row->total_cost;
                return [
                    'item_id' => $row->item_id,
                    'item_name' => optional($row->item)->description ?? 'Unknown',
                    'sold_qty' => $row->sold_qty,
                    'total_sales' => round($row->total_sales, 2),
                    'food_cost' => round($row->total_cost, 2),
                    'profit' => round($profit, 2),
                    'food_cost_percent' => $row->total_sales > 0 ? round(($row->total_cost / $row->total_sales) * 100, 2) : 0
                ];
            });

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'food_costing' => $soldItems
        ]);
    }

    public function reorderReport(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        $query = ItemBatch::with(['item.category', 'warehouse']);


        if ($warehouseId) {
            $query->where('ware_house_id', $warehouseId);
        }

        $reorderItems = $query
            ->select(
                'item_id',
                'ware_house_id',
                DB::raw('SUM(qty) as stock_qty'),
                DB::raw('SUM(qty * unit_cost) as stock_value')
            )
            ->groupBy('item_id', 'ware_house_id')
            ->havingRaw('SUM(qty) <= (
            SELECT minimum_qty 
            FROM item 
            WHERE item.id = item_id
        )')
            ->get()
            ->map(function ($row) {

                $minQty = $row->item->minimum_qty ?? 0;
                $reorderQty = $row->item->reorder_qty ?? 0;

                return [
                    'item_id' => $row->item_id,
                    'item_code' => $row->item->code,
                    'item_name' => $row->item->description,
                    'category' => optional($row->item->category)->name,
                    'warehouse_list' => $row->warehouse,
                    'warehouse' => optional($row->warehouse)->name,
                    'stock_qty' => (float) $row->stock_qty,
                    'minimum_qty' => (float) $minQty,
                    'reorder_qty' => (float) $reorderQty,
                    'suggested_reorder_qty' => max(
                        0,
                        $reorderQty > 0
                        ? ($reorderQty - $row->stock_qty)
                        : ($minQty - $row->stock_qty)
                    ),
                    'stock_value' => (float) $row->stock_value,
                    'status' => 'REORDER'
                ];
            });

        return response()->json([
            'warehouse_id' => $warehouseId,
            'total_items' => $reorderItems->count(),
            'reorder_items' => $reorderItems
        ]);
    }

    public function stockMovement()
    {
        // Get all stock movements that are not void
        $activities = ItemActivity::with([
            'item:id,code,description',
            'warehouse:id,name'
        ])
            ->where('is_void', false)
            ->orderBy('date', 'asc')
            ->get();

        // Format response
        $stockMovements = $activities->map(function ($act) {
            return [
                'date' => $act->date(),
                'doc_type' => $act->doc_type,       // e.g., GRN, SALES, ADJUSTMENT
                'reference_no' => $act->reference_no,
                'trans_type' => $act->trans_type,   // IN / OUT
                'doc_reference' => $act->doc_reference, // e.g., Invoice no
                'item_id' => $act->item_id,
                'item_code' => optional($act->item)->code,
                'item_name' => optional($act->item)->description,
                'warehouse' => optional($act->warehouse)->name,
                'qty' => (float) $act->qty,
                'unit_cost' => (float) $act->unit_cost,
                'qoh' => (float) $act->qoh,          // Quantity on hand after this movement
                'retail_price' => (float) $act->retail_price,
                'price_level1' => (float) $act->price_level1,
                'price_level2' => (float) $act->price_level2,
                'price_level3' => (float) $act->price_level3,
                'grn_no' => $act->grn_no,
            ];
        });

        return response()->json([
            'total_movements' => $stockMovements->count(),
            'stock_movements' => $stockMovements
        ]);
    }






}
