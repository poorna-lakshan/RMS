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

    $query = GRNHeader::with('vendor')
        ->where('is_void', 0)
        ->where('is_confirm', 1);

    if ($vendorId) {
        $query->where('vendor_id', $vendorId);
    }

    $outstanding = $query
        ->select(
            'vendor_id',
            DB::raw('SUM(net_total) as total_purchase'),
            DB::raw('SUM(paid_amount) as total_paid'),
            DB::raw('SUM(net_total - paid_amount) as outstanding_amount')
        )
        ->groupBy('vendor_id')
        ->get()
        ->map(function ($row) {
            return [
                'vendor_id' => $row->vendor_id,
                'vendor_code' => $row->vendor->code,
                'vendor_name' => $row->vendor->name,
                'total_purchase' => (float) $row->total_purchase,
                'total_paid' => (float) $row->total_paid,
                'outstanding_amount' => (float) $row->outstanding_amount,
            ];
        });

    return response()->json([
        'total_suppliers' => $outstanding->count(),
        'supplier_outstanding' => $outstanding
    ]);
}

}
