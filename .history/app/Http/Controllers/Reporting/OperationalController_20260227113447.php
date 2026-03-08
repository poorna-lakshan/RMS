<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\OrderHeader;
use Carbon\Carbon;
use DB;

class OperationalController extends Controller
{
    public function tableTurnover(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Only consider completed / paid orders and not voided
        $tables = OrderHeader::where('is_void', 0)
            ->where('is_paid', 1)
            ->where('type', 'Table')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                'table',
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM((SELECT SUM(qty * unit_price) FROM order_detail WHERE order_id = order_header.id)) as total_sales')
            )
            ->groupBy('table')
            ->orderBy('total_orders', 'DESC')
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'table_turnover' => $tables
        ]);
    }

    public function performance(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $performance = OrderHeader::
           // whereBetween('order_header.created_at', [$fromDate, $toDate])

            // 🔥 Only Steward Role
            // whereHas('user.role', function ($q) {
            //     $q->where('name', 'steward');
            // })

            join('order_detail', 'order_detail.order_id', '=', 'order_header.id')
           // ->where('order_detail.is_void', 0)
            ->select(
                'order_header.user as steward_id',
                DB::raw('COUNT(DISTINCT order_header.id) as total_orders'),
                DB::raw('SUM(order_detail.qty * order_detail.unit_price) as total_sales'),
                DB::raw('SUM(order_detail.qty) as total_items')
            )
            ->groupBy('order_header.user')
            //->with('user:id,name')
            ->get()
            ->map(function ($row) {

                $avg_sale = $row->total_orders > 0
                    ? round($row->total_sales / $row->total_orders, 2)
                    : 0;

                return [
                    'steward_id' => $row->steward_id,
                    //'steward_name' => $row->user->name ?? 'Unknown',
                    'total_orders' => $row->total_orders,
                    'total_items' => $row->total_items,
                    'total_sales' => round($row->total_sales, 2),
                    'average_sale_per_order' => $avg_sale
                ];
            });

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'steward_performance' => $performance
        ]);
    }
}
