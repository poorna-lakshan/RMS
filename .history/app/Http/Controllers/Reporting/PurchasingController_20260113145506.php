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
            ->where('is_confirm', 1)
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


}
