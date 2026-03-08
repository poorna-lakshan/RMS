<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\OrderHeader;
use App\Models\Sales\OrderDetail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class KdsController extends Controller
{
    public function getOrders()
    {
        // Fetch all orders including voided ones
        $orders = OrderHeader::with(['details.item.kitchans', 'steward'])
            ->where(function ($q) {
                $q->where('type', 'DineIn')
                    ->where('is_invoice', 0);      // Only dinein requires invoice = 0
            })
            ->orWhere(function ($q) {
                $q->where('type', '<>', 'DineIn'); // Other types: take all, no invoice filter
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $now = Carbon::now();

        $result = $orders->map(function ($order) use ($now) {
            $items = $order->details->filter(function ($detail) use ($now) {
                if ($detail->status === 'served') {
                    return false;
                }
                if ($detail->is_void == 1) {
                    return $detail->created_at->diffInMinutes($now) <= 1;
                }
                return true;
            })->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'name' => $detail->item ? $detail->item->description : '',
                    'note' => $detail->comment ? $detail->comment : '',
                 'quantity' => (int) $detail->qty,
                    'price' => $detail->unit_price,
                    'status' => $detail->status,
                    'category' => $detail->item && $detail->item->kitchans ? $detail->item->kitchans->name : '',
                    'is_void' => $detail->is_void,
                    'created_at' => $detail->created_at->toIso8601String(), // send timestamp
                ];
            });

            if ($items->isEmpty())
                return null;

            return [
                'id' => $order->id,
                'table' => $order->table,
                'type' => $order->type,
                'order_code' => $order->order_code,
                'kot' => $order->kot,
                'steward' => $order->steward ? $order->steward->name : '',
                'kitchan' => $order->category,
                'time' => $order->created_at->format('h:i A'),
                'created_at' => $order->created_at->toIso8601String(), // for duration
                'is_void' => $order->is_void,
                'items' => $items,
            ];
        })->filter();

        return response()->json($result);

    }

    public function updateStatus(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'item_id' => 'required|integer',
            'status' => 'required|string|in:pending,preparing,ready,served',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Fetch the item and ensure it belongs to the given order
        $item = OrderDetail::where('id', $request->item_id)
            ->where('order_id', $request->order_id) // check belongs to order
            ->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in the given order'
            ], 404);
        }

        // Update status
        $item->status = $request->status;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'item' => $item
        ]);
    }


}
