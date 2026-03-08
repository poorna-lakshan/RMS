<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\InvoiceHeader;
use App\Models\Sales\InvoiceDetail;
use App\Models\Inventory\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Purchasing\GRNHeader;
class DashboardController extends Controller
{
    /**
     * Avg Daily Revenue + Lunch/Dinner + Top Seller + Margins
     */
    public function avgDailyRevenueWithLunchDinner(Request $request)
    {
        $from = $request->from ?? Carbon::now()->subDays(30)->toDateString();
        $to = $request->to ?? Carbon::now()->toDateString();


        return response()->json([
            'status' => true,
            'from' => $from,
            'to' => $to,

            'restaurantMetrics' => $this->restaurantMetrics($request),
            'zero_stock_items' => $this->zeroStockItems(),
            'food_cost' => $this->TodayFoodCostWithPercentage(),
            'revenueChart' => $this->revenueChart(),
            'categoryRevenue' => $this->categoryRevenue(),
            'orderCategorySummary' => $this->orderCategorySummary(),
            'fastMovingItems' => $this->fastMovingItemsAllPeriods(),
            'RevenueSummery' => $this->RevenueSummery(),


        ]);
    }


    public function RevenueSummery()
    {
        return [
            'stockHealth' => $this->stockHealth(),
            'billSettlement' => $this->billSettlement(),
            'todayProfitMargin' => $this->todayProfitMargin(),
        ];
    }

    public function restaurantMetrics(Request $request)
    {
        $from = $request->from ?? Carbon::now()->subDays(30)->toDateString();
        $to = $request->to ?? Carbon::now()->toDateString();
        /* =========================
           DAILY REVENUE
        ========================= */


        $daily = InvoiceHeader::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw("
    SUM(net_total) as total,

    SUM(
        CASE 
            WHEN TIME(created_at) < '17:00:00'
            THEN net_total ELSE 0
        END
    ) as lunch_total,

    SUM(
        CASE 
            WHEN TIME(created_at) >= '17:00:00'
            THEN net_total ELSE 0
        END
    ) as dinner_total
")
        )
            ->where('is_void', 0)
            ->whereDate('created_at', Carbon::today())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        $avgDaily = round($daily->avg('total'), 2);
        $avgLunch = round($daily->avg('lunch_total'), 2);
        $avgDinner = round($daily->avg('dinner_total'), 2);

        /* =========================
           ORDERS TODAY
        ========================= */
        $ordersToday = $this->ordersTodayByCategory();
        $ordersTotal = array_sum($ordersToday);

        /* =========================
           ADVANCED METRICS
        ========================= */
        $advanced = $this->dashboardAdvancedMetrics($from, $to);
        $foodCost = $this->AlltodayFoodCostWithPercentage();
        /* =========================
           INVENTORY ALERT
        ========================= */
        $inventory = $this->inventoryAlert();

        /* =========================
           DASHBOARD CARDS
        ========================= */
        return [
            'metrics' => [

                [
                    'name' => 'Today Revenue',
                    'value' => '' . number_format($avgDaily, 2),
                    'change' => '+18.2%',
                    'target' => '40,000',
                    'color' => 'success',
                    'icon' => 'DollarCircleOutlined',
                    'trend' => 'up',
                    'detail' => 'Lunch: ' . number_format($avgLunch, 2)
                        . ' | Dinner: ' . number_format($avgDinner, 2),
                    'status' => $avgDaily >= 40000 ? 'Exceeding Target' : 'Below Target'
                ],

                [
                    'name' => 'Top Seller',
                    'value' => $advanced['top_seller']['item_name'] ?? 'N/A',
                    'change' => ($advanced['top_seller']['orders'] ?? 0) . ' orders',
                    'target' => '' . number_format($advanced['top_seller']['sales'] ?? 0, 2),
                    'color' => 'error',
                    'icon' => 'FireOutlined',
                    'trend' => 'up',
                    'detail' => 'Margins: '
                        . ($advanced['top_seller']['margin_percentage'] ?? '0%')
                        . ' | Popular: '
                        . ($advanced['popular_meal_time'] ?? 'N/A'),
                    'status' => 'Best Performer'
                ],

                [
                    'name' => 'Orders Today',
                    'value' => (string) $ordersTotal,
                    'change' => '+32',
                    'target' => '120 Target',
                    'color' => 'info',
                    'icon' => 'CheckCircleOutlined',
                    'trend' => 'up',
                    'detail' => 'Dine-in: ' . $ordersToday['dine_in']
                        . ' | Takeaway: ' . $ordersToday['takeaway']
                        . ' | Delivery: ' . $ordersToday['delivery'],
                    'status' => $ordersTotal >= 120 ? 'Above Target' : 'Below Target'
                ],

                [
                    'name' => 'Inventory Alert',
                    'value' => $inventory['count'] . ' Items',
                    'change' => $inventory['count'] > 0 ? 'Low Stock' : 'All Good',
                    'target' => 'Reorder Now',
                    'color' => $inventory['count'] > 0 ? 'orange' : 'success',
                    'icon' => 'ExclamationCircleFilled',
                    'trend' => $inventory['count'] > 0 ? 'down' : 'up',
                    'detail' => $inventory['count'] > 0
                        ? collect($inventory['items'])->pluck('name')->take(3)->implode(' | ')
                        : 'No low stock items',
                    'status' => $inventory['count'] > 0
                        ? 'Attention Needed'
                        : 'Healthy Stock'
                ],

                [
                    'name' => 'Food Cost %',
                    'value' => $foodCost['percentage'] . '%',
                    'change' => $foodCost['percentage'] <= 30 ? '-Good' : '+High',
                    'target' => '30% Target',
                    'color' => $foodCost['percentage'] <= 30 ? 'teal' : 'error',
                    'icon' => 'PieChartOutlined',
                    'trend' => $foodCost['percentage'] <= 30 ? 'down' : 'up',
                    'detail' => 'Cost: ' . number_format($foodCost['food_cost'], 2)
                        . ' | Revenue: ' . number_format($foodCost['revenue'], 2),
                    'status' => $foodCost['percentage'] <= 30
                        ? 'Within Budget'
                        : 'Over Budget'
                ],

                [
                    'name' => 'Customer Rating',
                    'value' => ($advanced['rating'] ?? 4.7) . '★',
                    'change' => '+0.2',
                    'target' => '4.5★ Target',
                    'color' => 'amber',
                    'icon' => 'StarFilled',
                    'trend' => 'up',
                    'detail' => 'Reviews: '
                        . ($advanced['reviews'] ?? 24)
                        . ' | Complaints: '
                        . ($advanced['complaints'] ?? 2),
                    'status' => 'Excellent'
                ]
            ]
        ];
    }

    /* ============================================================
       INVENTORY ALERT
    ============================================================ */
    private function inventoryAlert()
    {
        $items = Item::select(
            'item.id',
            'item.code',
            'item.description',
            DB::raw('COALESCE(SUM(item_ware_house.qty), 0) as total_qty')
        )
            ->leftJoin('item_ware_house', 'item.id', '=', 'item_ware_house.item_id')
            ->where('class_id', '<>', 3)
            ->groupBy('item.id', 'item.code', 'item.description')
            ->having('total_qty', '<=', 0)
            ->orderBy('item.description')
            ->get();

        return [
            'count' => $items->count(),
            'items' => $items->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->description,
            ])->values()
        ];
    }




    public function billSettlement()
    {
        $today = Carbon::today();

        // Total GRNs (excluding voided)
        $totalGRNs = GRNHeader::where('is_void', 0)->count();

        // Settled GRNs (paid_amount > 0)
        $settledGRNs = GRNHeader::where('is_void', 0)
            ->where('paid_amount', '>', 0)
            ->count();

        // Pending GRNs (unpaid)
        $pendingGRNs = $totalGRNs - $settledGRNs;

        // GRNs settled today
        $todayGRNs = GRNHeader::whereDate('date', $today)
            ->where('is_void', 0)
            ->where('paid_amount', '>', 0)
            ->count();

        // Overdue: unpaid GRNs older than today
        $overdueGRNs = GRNHeader::where('is_void', 0)
            ->where('paid_amount', '=', 0)
            ->whereDate('date', '<', $today)
            ->count();

        // Settlement % (value)
        $value = $totalGRNs > 0 ? round(($settledGRNs / $totalGRNs) * 100) : 100;

        // Average settlement amount
        $avgPaid = GRNHeader::where('is_void', 0)
            ->where('paid_amount', '>', 0)
            ->avg('paid_amount');

        // Trend placeholder (compare last period for real trend)
        $trend = [
            'icon' => 'mdi-trending-up',
            'text' => '+3.1%',
            'color' => 'success'
        ];

        return [
            'value' => $value,
            'color' => '#8B5CF6',
            'icon' => 'mdi-cash-clock',
            'trend' => $trend,
            'avgTime' => round($avgPaid, 2), // average paid amount
            'pending' => $pendingGRNs,
            'today' => $todayGRNs,
            'overdue' => $overdueGRNs,
        ];
    }


    public function stockHealth()
    {
        $items = Item::with('warehouses')  // assuming the relationship in Item model is called 'warehouses'
            ->whereIn('class_id', [1, 2])
            ->get();

        $totalItems = $items->count();
        $lowStock = 0;
        $outOfStock = 0;

        foreach ($items as $item) {
            // Sum of qty across all warehouses
            $totalQty = $item->warehouses->sum('pivot.qty');

            if ($totalQty <= 0) {
                $outOfStock++;
            } elseif ($totalQty <= $item->minimum_qty) {
                $lowStock++;
            }
        }

        // Stock health %
        $value = $totalItems > 0
            ? round((($totalItems - $lowStock - $outOfStock) / $totalItems) * 100)
            : 100;

        // Status, color, trend
        if ($value >= 75) {
            $status = 'Good';
            $color = '#10B981';
            $trendColor = 'success';
            $trendIcon = 'mdi-trending-up';
            $trendText = '+5.2%';
        } elseif ($value >= 50) {
            $status = 'Moderate';
            $color = '#F59E0B';
            $trendColor = 'warning';
            $trendIcon = 'mdi-trending-flat';
            $trendText = '+2.1%';
        } else {
            $status = 'Critical';
            $color = '#EF4444';
            $trendColor = 'error';
            $trendIcon = 'mdi-trending-down';
            $trendText = '-4.5%';
        }

        return [
            'value' => $value,
            'color' => $color,
            'icon' => 'mdi-check-circle',
            'status' => $status,
            'trend' => [
                'icon' => $trendIcon,
                'text' => $trendText,
                'color' => $trendColor
            ],
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'totalItems' => $totalItems
        ];
    }


    public function fastMovingItemsAllPeriods($limit = 10)
    {
        $periods = [
            'today' => [Carbon::today(), Carbon::today()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        ];

        $colors = ['primary', 'success', 'warning', 'error', 'info', 'purple'];
        $icons = ['mdi-food', 'mdi-rice', 'mdi-grill', 'mdi-baguette', 'mdi-glass-mug-variant', 'mdi-cupcake'];

        $result = [];

        foreach ($periods as $key => [$start, $end]) {
            $rows = InvoiceDetail::join('invoice_header', 'invoice_header.id', '=', 'invoice_detail.reference_no')
                ->join('item', 'item.id', '=', 'invoice_detail.item_id')
                ->where('invoice_header.is_void', 0)
                ->whereDate('invoice_header.created_at', '>=', $start)
                ->whereDate('invoice_header.created_at', '<=', $end)
                ->select(
                    'item.id',
                    'item.description as name',
                    DB::raw('SUM(invoice_detail.qty) as sales'),
                    DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as revenue')
                )
                ->groupBy('item.id', 'item.description')
                ->orderByDesc('sales')
                ->limit($limit)
                ->get();

            $result[$key] = $rows->map(function ($item, $index) use ($colors, $icons) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sales' => (int) $item->sales,
                    'revenue' => (float) $item->revenue,
                    'color' => $colors[$index % count($colors)],
                    'icon' => $icons[$index % count($icons)],
                ];
            })->toArray(); // ensure it's always an array, even if empty
        }

        return $result;
    }

    public function orderCategorySummary()
    {
        return [
            'today' => $this->categoryStats(
                Carbon::today(),
                Carbon::now()
            ),

            'week' => $this->categoryStats(
                Carbon::now()->startOfWeek(),
                Carbon::now()
            ),

            'month' => $this->categoryStats(
                Carbon::now()->startOfMonth(),
                Carbon::now()
            ),
        ];
    }

    private function categoryStats(Carbon $from, Carbon $to)
    {
        $rows = InvoiceHeader::where('is_void', 0)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('
            (category) as category,
            COUNT(*) as orders,
            SUM(net_total) as revenue
        ')
            ->groupBy(DB::raw('(category)'))
            ->get()
            ->keyBy('category');

        $categories = [
            'dinein',
            'Take Away',
            'delivery',
            'pickup',
            'uber',
            'pickme',
            'direct'
        ];

        $data = [
            'totalOrders' => 0,
            'totalRevenue' => 0,
            'categories' => []
        ];

        foreach ($categories as $cat) {
            $orders = (int) ($rows[$cat]->orders ?? 0);
            $revenue = (float) ($rows[$cat]->revenue ?? 0);

            $data['categories'][$cat] = [
                'orders' => $orders,
                'revenue' => $revenue
            ];

            $data['totalOrders'] += $orders;
            $data['totalRevenue'] += $revenue;
        }

        return $data;
    }


    public function categoryRevenue()
    {
        return [
            'today' => $this->categoryByPeriod('today'),
            'week' => $this->categoryByPeriod('week'),
            'month' => $this->categoryByPeriod('month'),
        ];
    }

    private function categoryByPeriod(string $period)
    {
        // Date range
        switch ($period) {
            case 'today':
                $from = Carbon::today();
                $to = Carbon::now();
                break;

            case 'week':
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
                break;

            case 'month':
                $from = Carbon::now()->startOfMonth();
                $to = Carbon::now()->endOfMonth();
                break;
        }

        $rows = InvoiceDetail::join('invoice_header', 'invoice_header.id', '=', 'invoice_detail.reference_no')
            ->join('item', 'item.id', '=', 'invoice_detail.item_id')
            ->join('item_category', 'item_category.id', '=', 'item.category_id')
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$from, $to])
            ->selectRaw('
        LOWER(item_category.description) as category,
        SUM(invoice_detail.qty * invoice_detail.unit_price) as total
    ')
            ->groupBy(DB::raw('LOWER(item_category.description)'))
            ->get();


        $categories = [];
        $grandTotal = 0;

        foreach ($rows as $row) {
            $categories[$row->category] = round($row->total, 2);
            $grandTotal += $row->total;
        }

        return [
            'total' => round($grandTotal, 2),
            'categories' => $categories
        ];
    }

    /**
     * Top Seller + Margin + Popular Meal Time
     */
    private function dashboardAdvancedMetrics($from, $to)
    {
        // =========================
        // TOP SELLER ITEM
        // =========================
        $topItem = InvoiceDetail::select(
            'invoice_detail.item_id',
            DB::raw('SUM(invoice_detail.qty) as total_qty'),
            DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as total_sales'),
            DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_cost) as total_cost')
        )
            ->join('invoice_header', 'invoice_header.id', '=', 'invoice_detail.reference_no')
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$from, $to])
            ->groupBy('invoice_detail.item_id')
            ->orderByDesc('total_qty')
            ->with('item:id,description')
            ->first();

        $margin = 0;
        if ($topItem && $topItem->total_sales > 0) {
            $margin = round(
                (($topItem->total_sales - $topItem->total_cost) / $topItem->total_sales) * 100,
                2
            );
        }

        // =========================
        // POPULAR MEAL TIME
        // =========================
        $mealPopularity = InvoiceHeader::select(
            DB::raw("
                    SUM(CASE 
                        WHEN TIME(created_at) BETWEEN '10:00:00' AND '16:59:59' 
                        THEN net_total ELSE 0 END) as lunch_total,
                    SUM(CASE 
                        WHEN TIME(created_at) BETWEEN '17:00:00' AND '23:59:59' 
                        THEN net_total ELSE 0 END) as dinner_total
                ")
        )
            ->where('is_void', 0)
            ->whereBetween('created_at', [$from, $to])
            ->first();

        $popularMeal =
            ($mealPopularity->dinner_total ?? 0) > ($mealPopularity->lunch_total ?? 0)
            ? 'Dinner'
            : 'Lunch';

        return [
            'top_seller' => [
                'item_id' => $topItem?->item_id,
                'item_name' => $topItem?->item?->description,
                'quantity_sold' => (int) ($topItem?->total_qty ?? 0),
                'sales' => round($topItem?->total_sales ?? 0, 2),
                'margin_percentage' => $margin . '%',
            ],
            'popular_meal_time' => $popularMeal,
            'meal_revenue' => [
                'lunch' => round($mealPopularity->lunch_total ?? 0, 2),
                'dinner' => round($mealPopularity->dinner_total ?? 0, 2),
            ],
        ];
    }


    private function ordersTodayByCategory()
    {
        $today = Carbon::today()->toDateString();

        $orders = InvoiceHeader::select(
            'category',
            DB::raw('COUNT(*) as total')
        )
            ->where('is_void', 0)
            ->whereDate('created_at', $today)
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        return [
            'dine_in' => (int) ($orders['DineIn'] ?? 0),
            'takeaway' => (int) ($orders['Take Away'] ?? 0),
            'delivery' => (int) ($orders['Delivery'] ?? 0),
        ];
    }

    private function zeroStockItems()
    {
        $items = Item::select(
            'item.id',
            'item.code',
            'item.description',
            DB::raw('COALESCE(SUM(item_ware_house.qty), 0) as total_qty')
        )
            ->leftJoin('item_ware_house', 'item.id', '=', 'item_ware_house.item_id')
            ->where('class_id', '<>', 3)
            ->groupBy('item.id', 'item.code', 'item.description')
            ->having('total_qty', '<=', 0)
            ->orderBy('item.description')
            ->get();

        return [
            'count' => $items->count(),
            'items' => $items->map(fn($item) => [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->description,
            ])
        ];
    }


    private function AlltodayFoodCostWithPercentage()
    {
        $today = Carbon::today();

        $data = InvoiceHeader::whereDate('created_at', $today)
            ->where('is_void', 0)
            ->selectRaw('
            COALESCE(SUM(total_cost),0) as food_cost,
            COALESCE(SUM(net_total),0) as revenue
        ')
            ->first();

        $percentage = 0;
        if ($data->revenue > 0) {
            $percentage = round(($data->food_cost / $data->revenue) * 100, 2);
        }

        return [
            'food_cost' => round($data->food_cost, 2),
            'revenue' => round($data->revenue, 2),
            'percentage' => $percentage
        ];
    }




    private function todayProfitMargin()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ---------- TODAY ----------
        $todayData = InvoiceHeader::whereDate('created_at', $today)
            ->where('is_void', 0)
            ->selectRaw('
            SUM(net_total) as revenue,
            SUM(total_cost) as cogs
        ')
            ->first();

        $revenue = (float) $todayData->revenue;
        $cogs = (float) $todayData->cogs;
        $grossProfit = $revenue - $cogs;

        $profitMargin = $revenue > 0
            ? round(($grossProfit / $revenue) * 100, 2)
            : 0;

        // ---------- YESTERDAY (for trend) ----------
        $yesterdayData = InvoiceHeader::whereDate('created_at', $yesterday)
            ->where('is_void', 0)
            ->selectRaw('
            SUM(net_total) as revenue,
            SUM(total_cost) as cogs
        ')
            ->first();

        $yRevenue = (float) $yesterdayData->revenue;
        $yCogs = (float) $yesterdayData->cogs;
        $yProfit = $yRevenue - $yCogs;

        $yMargin = $yRevenue > 0
            ? round(($yProfit / $yRevenue) * 100, 2)
            : 0;

        $change = round($profitMargin - $yMargin, 2);

        return [
            'value' => $profitMargin,
            'color' => '#3B82F6',
            'icon' => 'mdi-chart-line',

            'trend' => [
                'icon' => $change >= 0 ? 'mdi-trending-up' : 'mdi-trending-down',
                'text' => ($change >= 0 ? '+' : '') . $change . '%',
                'color' => $change >= 0 ? 'success' : 'error',
            ],

            'revenue' => round($revenue),
            'cogs' => round($cogs),
            'grossProfit' => round($grossProfit),
        ];
    }

    private function TodayFoodCostWithPercentage()
    {
        $today = Carbon::today();

        $data = InvoiceHeader::whereDate('created_at', $today)
            ->where('is_void', 0)
            ->selectRaw('
            SUM(total_cost) as food_cost,
            SUM(net_total) as revenue
        ')
            ->first();

        $percentage = 0;
        if ($data->revenue > 0) {
            $percentage = round(($data->food_cost / $data->revenue) * 100, 2);
        }

        return [
            'food_cost' => round($data->food_cost, 2),
            'revenue' => round($data->revenue, 2),
            'food_cost_percentage' => $percentage . '%'
        ];
    }





    public function revenueChart()
    {
        return [
            'today' => $this->todayRevenue(),
            'week' => $this->weekRevenue(),
            'month' => $this->monthRevenue(),
        ];
    }

    /**
     * TODAY – fixed time slots
     */
    private function todayRevenue()
    {
        $from = Carbon::now()->subHours(23)->startOfHour();
        $to = Carbon::now()->endOfHour();

        // Get revenue grouped by hour
        $data = InvoiceHeader::whereBetween('created_at', [$from, $to])
            ->where('is_void', 0)
            ->selectRaw("
            DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
            SUM(net_total) as total
        ")
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $categories = [];
        $values = [];

        // Build continuous 24-hour timeline
        for ($i = 23; $i >= 0; $i--) {
            $time = Carbon::now()->subHours($i)->startOfHour();
            $key = $time->format('Y-m-d H:00:00');

            $categories[] = $time->format('g A');
            $values[] = round($data[$key] ?? 0, 2);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $values
                ]
            ]
        ];
    }


    /**
     * WEEK – Mon to Sun
     */
    private function weekRevenue()
    {
        $data = InvoiceHeader::whereBetween(
            'created_at',
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
        )
            ->where('is_void', 0)
            ->selectRaw('DAYOFWEEK(created_at) as day, SUM(net_total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        // MySQL: 1=Sun ... 7=Sat
        $daysMap = [
            2 => 'Mon',
            3 => 'Tue',
            4 => 'Wed',
            5 => 'Thu',
            6 => 'Fri',
            7 => 'Sat',
            1 => 'Sun'
        ];

        $values = [];
        foreach (array_keys($daysMap) as $day) {
            $values[] = round($data[$day] ?? 0, 2);
        }

        return [
            'categories' => array_values($daysMap),
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $values
                ]
            ]
        ];
    }

    /**
     * MONTH – Week 1–4
     */
    private function monthRevenue()
    {
        $data = InvoiceHeader::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('is_void', 0)
            ->selectRaw('
                WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY), 1) + 1 as week,
                SUM(net_total) as total
            ')
            ->groupBy('week')
            ->pluck('total', 'week');

        $values = [];
        for ($i = 1; $i <= 4; $i++) {
            $values[] = round($data[$i] ?? 0, 2);
        }

        return [
            'categories' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $values
                ]
            ]
        ];
    }


}
