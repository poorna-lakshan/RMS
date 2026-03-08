<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use Illuminate\Http\Request;
use App\Models\Sales\InvoiceHeader;
use App\Models\Sales\InvoiceDetail;
use App\Models\Sales\CashierSessions;
use App\Models\Sales\OrderHeader;
use App\Models\Purchasing\PayDetail;
use App\Models\Sales\Expences;
use App\Models\Sales\InvPaymentMethod;

use Carbon\Carbon;
use DB;


class SalesController extends Controller
{
    /**
     * Daily Sales Summary
     */
    public function dailySalesSummary(Request $request)
    {

        // Date range (default = today)
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $dailySummary = InvoiceHeader::where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(gross_total) as gross_total'),
                DB::raw('SUM(service_charge_total) as service_charge'),
                DB::raw('SUM(delivery_charge) as delivery_charge'),
                DB::raw('SUM(dis_total) as discount_total'),
                DB::raw('SUM(net_total) as net_total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date', 'ASC')
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'daily_sales_summary' => $dailySummary
        ]);
    }



    public function dailySalesDetails(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Fetch invoice headers with details and payment methods
        $invoices = InvoiceHeader::with([
            'details.item',
            'customer',
            'warehouse',
            'user',
            'payments.paymentMethod'  // <-- eager-load payments
        ])
            ->where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('reference_no', 'DESC')
            ->get();

        // Map invoices to include full date-time and payment details
        $invoiceDetails = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'reference_no' => $invoice->reference_no,
                'created_at' => $invoice->created_at->toDateTimeString(),
                'category' => $invoice->category,
                'customer' => $invoice->customer ? $invoice->customer->name : null,
                'warehouse' => $invoice->warehouse ? $invoice->warehouse->name : null,
                'user' => $invoice->user ? $invoice->user : null,
                'gross_total' => $invoice->gross_total,
                'dis_per' => $invoice->dis_per,
                'dis_total' => $invoice->dis_total,
                'service_charge_per' => $invoice->service_charge_per,
                'service_charge_total' => $invoice->service_charge_total,
                'delivery_charge' => $invoice->delivery_charge,
                'is_full_pay' => $invoice->is_full_pay,
                'net_total' => $invoice->net_total,
                'details' => $invoice->details->map(function ($detail) {
                    return [
                        'item_name' => $detail->item ? $detail->item->description : null,
                        'qty' => $detail->qty,
                        'unit_price' => $detail->unit_price,
                        'total_price' => $detail->qty * $detail->unit_price,
                    ];
                }),
                'payments' => $invoice->payments->map(function ($payment) {
                    return [
                        'payment_method' => $payment->paymentMethod ? $payment->paymentMethod->description : null,
                        'amount' => $payment->amount,
                    ];
                }),
            ];
        });

        return response()->json([
            'from_date' => $fromDate->toDateTimeString(),
            'to_date' => $toDate->toDateTimeString(),
            'invoices' => $invoiceDetails,
        ]);
    }


    public function fastNonMovingReport(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay(); // default last 30 days

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Items sales summary
        $itemSales = DB::table('invoice_detail as d')
            ->join('invoice_header as h', 'h.id', '=', 'd.reference_no')
            ->join('item as i', 'i.id', '=', 'd.item_id')
            ->where('h.is_void', 0)
            ->whereBetween('h.created_at', [$fromDate, $toDate])
            ->select(
                'i.id as item_id',
                'i.code',
                'i.description as item_name',
                DB::raw('SUM(d.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT h.id) as invoice_count')
            )
            ->groupBy('i.id', 'i.code', 'i.description')
            ->orderByDesc('total_qty')
            ->get();

        // Fast moving (top sellers)
        $fastMoving = $itemSales->filter(fn($i) => $i->total_qty > 0);


        $itemSales2 = DB::table('invoice_detail as d')
            ->join('invoice_header as h', 'h.id', '=', 'd.reference_no')
            ->join('item as i', 'i.id', '=', 'd.item_id')
            ->where('h.is_void', 0)
            ->whereBetween('h.created_at', [$fromDate, $toDate])
            ->select(
                'i.id as item_id',
                'i.code',
                'i.description as item_name',
                DB::raw('SUM(d.qty) as total_qty'),
                DB::raw('COUNT(DISTINCT h.id) as invoice_count')
            )
            ->groupBy('i.id', 'i.code', 'i.description')
            ->orderBy('total_qty')
            ->get();

        // Fast moving (top sellers)
        $nonMoving = $itemSales2->filter(fn($i) => $i->total_qty > 0);

        $totalInvoices = DB::table('invoice_header')
            ->where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();



        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'fast_moving_items' => $fastMoving,
            'non_moving_items' => $nonMoving,
            'totalInvoices' => $totalInvoices,
        ]);
    }






    public function salesByItem(Request $request)
    {
        // Date range (default = today)
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $itemsSales = InvoiceDetail::join(
            'invoice_header',
            'invoice_detail.reference_no',
            '=',
            'invoice_header.id'
        )
            ->join(
                'item',
                'invoice_detail.item_id',
                '=',
                'item.id'
            )
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$fromDate, $toDate])
            ->select(
                'item.id as item_id',
                'item.description',
                DB::raw('SUM(invoice_detail.qty) as total_qty'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as gross_sales'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_cost) as total_cost'),
                DB::raw('SUM((invoice_detail.qty * invoice_detail.unit_price) - (invoice_detail.qty * invoice_detail.unit_cost)) as profit')
            )
            ->groupBy('item.id', 'item.description')
            ->orderBy('gross_sales', 'DESC')
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'sales_by_item' => $itemsSales
        ]);
    }

    public function salesByCategory(Request $request)
    {
        // Date range (default = today)
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $categorySales = InvoiceDetail::join(
            'invoice_header',
            'invoice_detail.reference_no',
            '=',
            'invoice_header.id'
        )
            ->join(
                'item',
                'invoice_detail.item_id',
                '=',
                'item.id'
            )
            ->join(
                'item_category',
                'item.category_id',
                '=',
                'item_category.id'
            )
            ->where('invoice_header.is_void', 0)
            ->whereBetween('invoice_header.created_at', [$fromDate, $toDate])
            ->select(
                'item_category.id as category_id',
                'item_category.description as category_name',
                DB::raw('SUM(invoice_detail.qty) as total_qty'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_price) as gross_sales'),
                DB::raw('SUM(invoice_detail.qty * invoice_detail.unit_cost) as total_cost'),
                DB::raw('SUM((invoice_detail.qty * invoice_detail.unit_price) - (invoice_detail.qty * invoice_detail.unit_cost)) as profit')
            )
            ->groupBy('item_category.id', 'item_category.description')
            ->orderBy('gross_sales', 'DESC')
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'sales_by_category' => $categorySales
        ]);
    }


    public function salesByChannel(Request $request)
    {
        // Date range (default = today)
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        $channelSales = InvoiceHeader::where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                'category as channel',
                DB::raw('COUNT(id) as invoice_count'),
                DB::raw('SUM(item_count) as total_items'),
                DB::raw('SUM(gross_total) as gross_total'),
                DB::raw('SUM(dis_total) as discount_total'),
                DB::raw('SUM(service_charge_total) as service_charge'),
                DB::raw('SUM(delivery_charge) as delivery_charge'),
                DB::raw('SUM(net_total) as net_total')
            )
            ->groupBy('category')
            ->orderBy('net_total', 'DESC')
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'sales_by_channel' => $channelSales
        ]);
    }




    public function allSessionsReport(Request $request)
    {


        // Parse dates
        $fromDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // Filter sessions overlapping the range
        $sessions = CashierSessions::where('in_time', '<=', $toDate)
            ->where(function ($query) use ($fromDate) {
                $query->where('out_time', '>=', $fromDate) // Closed sessions
                    ->orWhereNull('out_time');          // Open sessions
            })
            ->orderBy('id', 'desc')->get();


        //  $sessions = CashierSessions::all();

        $report = [];

        foreach ($sessions as $session) {

            $invoices = InvoiceHeader::with([
                'details.item',
                'payments.paymentMethod'
            ])
                ->where('user', $session->user)
                ->whereBetween('created_at', [
                    $session->in_time,
                    $session->out_time ?? now()
                ])
                ->where('is_void', 0)
                ->get();

            // Basic totals
            $summary = [
                'invoice_count' => $invoices->count(),
                'total_sale' => $invoices->sum('net_total'),
                'gross_total' => $invoices->sum('gross_total'),
                'discount_total' => $invoices->sum('dis_total'),
                'service_charge_total' => $invoices->sum('service_charge_total'),
                'delivery_total' => $invoices->sum('delivery_charge'),
                'vat_total' => $invoices->sum('vat_total'),
                'nbt_total' => $invoices->sum('nbt_total'),
                'paid_total' => $invoices->sum('paid_total'),
                'credit_total' => $invoices->sum('credit_total'),
            ];

            // Payment method wise
            $paymentSummary = [];
            foreach ($invoices as $invoice) {
                foreach ($invoice->payments as $payment) {
                    $methodName = $payment->paymentMethod->description ?? 'Unknown';
                    $paymentSummary[$methodName] = ($paymentSummary[$methodName] ?? 0) + $payment->amount;
                }
            }

            // Invoice type/category wise
            $categorySummary = $invoices
                ->groupBy('category')
                ->map(function ($rows) {
                    return [
                        'invoice_count' => $rows->count(),
                        'total_amount' => $rows->sum('net_total')
                    ];
                });

            $report[] = [
                'session' => $session,
                'summary' => $summary,
                'payment_summary' => $paymentSummary,
                'category_summary' => $categorySummary,
                'invoices' => $invoices
            ];
        }

        return response()->json($report);
    }




    public function getVoidOrders(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // 🔹 Void Orders (Filtered by date)
        $orders = OrderHeader::whereBetween('created_at', [$fromDate, $toDate])
            ->whereHas('details', function ($query) {
                $query->where('is_void', 1);
            })
            ->with([
                'details' => function ($query) {
                    $query->where('is_void', 1)
                        ->with('item:id,description'); // 🔥 Load item description
                }
            ])
            ->get();

        // 🔹 Void Invoices (Filtered by date)
        $invoices = InvoiceHeader::where('is_void', 1)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->with([
                'payments',
                'details' => function ($query) {
                    $query->with('item:id,description');
                }
            ])
            ->get();

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'orders' => $orders,
            'invoices' => $invoices
        ]);
    }





    public function getPNLReport(Request $request)
    {
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : Carbon::today()->startOfDay();

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : Carbon::today()->endOfDay();

        // 🔹 Sales
        $totalSales = InvoiceHeader::where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('net_total');

        // 🔹 Cost of Sales
        $totalCost = InvoiceHeader::where('is_void', 0)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_cost');

        $grossProfit = $totalSales - $totalCost;

        // 🔹 Operating Expenses
        $totalExpenses = Expences::whereBetween('created_at', [$fromDate, $toDate])
            ->sum('amount');

        // 🔹 Supplier Payments (exclude void)
        $supplierPayments = PayDetail::whereHas('Header', function ($q) use ($fromDate, $toDate) {
            $q->where('is_void', 0)
                ->whereBetween('date', [$fromDate, $toDate]);
        })
            ->sum('paid');

        // 🔹 Net Profit (Cash Based)
        $netProfit = $grossProfit - $totalExpenses;

        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),

            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,

            'operating_expenses' => $totalExpenses,
            'supplier_payments' => $supplierPayments,

            'net_profit' => $netProfit,
        ]);
    }

    public function totalCustomers()
    {
        return Customer::count();
    }

    public function newCustomersThisMonth()
    {
        return Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function totalSalesMonth()
    {
        return InvoiceHeader::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('is_void', 0)
            ->sum('net_total');
    }

    public function totalInvoicesToday()
    {
        return InvoiceHeader::whereDate('created_at', today())
            ->where('is_void', 0)
            ->count();
    }

    public function topCustomers()
    {
        return InvoiceHeader::selectRaw('customer_id, SUM(net_total) as total')
            ->with('customer')
            ->where('is_void', 0)
            ->where('customer_id', '<>', '1')
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->get();
    }

    public function topItems()
    {
        return InvoiceDetail::selectRaw('item_id, SUM(qty) as total_qty')
            ->with('item')
            ->groupBy('item_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }
    public function salesByPayment()
    {
        return InvPaymentMethod::selectRaw('payment_method_id, SUM(amount) as total')
            ->with('paymentMethod')
            ->groupBy('payment_method_id')
            ->get();
    }

    public function monthlySales()
    {
        return InvoiceHeader::selectRaw('MONTH(created_at) as month, SUM(net_total) as total')
            ->whereYear('created_at', now()->year)
            ->where('is_void', 0)
            ->groupByRaw('MONTH(created_at)')
            ->orderBy('month')
            ->get();
    }

    public function recentInvoices()
    {
        return InvoiceHeader::with('customer')
            ->where('is_void', 0)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function totalSalesToday()
    {
        return InvoiceHeader::whereDate('created_at', today())
            ->where('is_void', 0)
            ->sum('net_total');
    }

    public function getCreditInvoices()
    {
        return InvoiceHeader::with('customer')
            ->where('customer_id', '<>', 1)
            ->where('balance', '>', 0)
            ->where('is_void', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function dashboard()
    {
        return response()->json([
            'customers' => $this->totalCustomers(),
            'new_customers' => $this->newCustomersThisMonth(),
            'sales_today' => $this->totalSalesToday(),
            'sales_month' => $this->totalSalesMonth(),
            'invoices_today' => $this->totalInvoicesToday(),
            'top_customers' => $this->topCustomers(),
            'top_items' => $this->topItems(),
            'payment_methods' => $this->salesByPayment(),
            'monthly_sales' => $this->monthlySales(),
            'recent_invoices' => $this->recentInvoices(),
               'getCreditInvoices' => $this->getCreditInvoices()
        ]);
    }



}
