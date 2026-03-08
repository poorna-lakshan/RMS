<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\InvoiceHeader;
use App\Models\Sales\InvoiceDetail;
use App\Models\Sales\CashierSessions;
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
            ->orderBy('created_at', 'ASC')
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



        return response()->json([
            'from_date' => $fromDate->toDateString(),
            'to_date' => $toDate->toDateString(),
            'fast_moving_items' => $fastMoving,
            'non_moving_items' => $nonMoving,
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

}
