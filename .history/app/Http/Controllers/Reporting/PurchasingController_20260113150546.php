<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchasing\GRNHeader;
use DB;
class PurchasingController extends Controller
{
    public function supplierOutstanding(Request $request)
    {
        $vendorId = $request->vendor_id;

        $query = GRNHeader::with(['vendor', 'warehouse'])
            ->where('is_void', 0)
            ->whereColumn('net_total', '>', 'paid_amount');

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        $grns = $query
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($grn) {

                return [
                    'grn_id' => $grn->id,
                    'grn_code' => $grn->code,
                    'date' => $grn->date->toDateString(),
                    'vendor_id' => $grn->vendor_id,
                    'vendor_name' => optional($grn->vendor)->name,
                    'warehouse' => optional($grn->warehouse)->name,
                    'net_total' => (float) $grn->net_total,
                    'paid_amount' => (float) $grn->paid_amount,
                    'outstanding_amount' => (float) ($grn->net_total - $grn->paid_amount),
                ];
            });

        return response()->json([
            'total_unpaid_grns' => $grns->count(),
            'unpaid_grns' => $grns
        ]);
    }



    public function grnHistory(Request $request)
    {
        // Optional date range filter
        $fromDate = $request->from_date
            ? Carbon::parse($request->from_date)->startOfDay()
            : null;

        $toDate = $request->to_date
            ? Carbon::parse($request->to_date)->endOfDay()
            : null;

        // Query GRN headers with details, vendor, warehouse, and item info
        $query = GRNHeader::with([
            'vendor:id,name',
            'warehouse:id,name',
            'details.item:id,code,description',
            'details.sku:id,sku'
        ])->where('is_void', false)
            ->where('is_confirm', true)
            ->orderBy('date', 'desc');

        // Apply date range if provided
        if ($fromDate && $toDate) {
            $query->whereBetween('date', [$fromDate, $toDate]);
        }

        $grns = $query->get();

        // Format response
        $grnHistory = $grns->map(function ($grn) {
            return [
                'grn_id' => $grn->id,
                'grn_code' => $grn->code,
                'date' => $grn->date->toDateString(),
                'vendor_id' => $grn->vendor_id,
                'vendor_name' => optional($grn->vendor)->name,
                'warehouse' => optional($grn->warehouse)->name,
                'net_total' => (float) $grn->net_total,
                'paid_amount' => (float) $grn->paid_amount,
                'outstanding_amount' => (float) ($grn->net_total - $grn->paid_amount),
                'details' => $grn->details->map(function ($detail) {
                    return [
                        'item_id' => $detail->item_id,
                        'item_code' => optional($detail->item)->code,
                        'item_name' => optional($detail->item)->description,
                        'sku' => optional($detail->sku)->sku,
                        'qty' => (float) $detail->qty,
                        'unit_cost' => (float) $detail->unit_cost,
                        'total_cost' => (float) $detail->total_cost,
                        'expire_date' => $detail->expire_date?->toDateString(),
                    ];
                }),
            ];
        });

        return response()->json([
            'total_grns' => $grnHistory->count(),
            'grn_history' => $grnHistory
        ]);
    }



}
