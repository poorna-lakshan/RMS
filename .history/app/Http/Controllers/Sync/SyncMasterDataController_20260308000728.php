<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\OrderHeader;
use Illuminate\Http\Request;
use App\Models\Inventory\ItemCategory;
use App\Models\default\DeletedRows;
use App\Models\default\TableMaster;
use App\Models\Sales\Steward;
use App\Models\Sales\Addone;
use App\Models\Sales\PaymentMethod;
use App\Http\Controllers\Sales\CustomerController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Models\Inventory\Kitchen;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\default\Company;
class SyncMasterDataController extends Controller
{
    public function getItemCategory()
    {
        // Retrieve all ItemCategories
        $itemCategories = ItemCategory::where('type', 3)
            ->where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'ItemCategory')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $itemCategories,
            'deleted' => $deletedRows
        ]);
    }

    public function markItemCategorySynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            ItemCategory::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'ItemCategory')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }

    public function markItemsSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            Item::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'Item')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }

    public function getItems()
    {
        // Retrieve all ItemCategories
        $items = Item::where('type', '<>', value: 2)
            ->where('is_sync', 0)
            ->where('class_id', '<>', 2)
            ->get();


        $deletedRows = DeletedRows::where('type', 'Item')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $items,
            'deleted' => $deletedRows
        ]);
    }


    public function getTableMaster()
    {
        // Retrieve all ItemCategories
        $TableMaster = TableMaster::
            where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'TableMaster')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }

    public function markTableMasterSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            TableMaster::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'TableMaster')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }



    public function getSteward()
    {
        // Retrieve all ItemCategories
        $TableMaster = Steward::
            where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'Steward')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }

    public function markStewardSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            Steward::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'Steward')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }



    public function getAddone()
    {
        // Retrieve all ItemCategories
        $TableMaster = Addone::
            where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'Addone')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }

    public function markAddoneSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            Addone::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'Addone')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }



    public function getKitchen()
    {
        // Retrieve all ItemCategories
        $TableMaster = Kitchen::
            where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'Kitchen')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }

    public function markKitchenSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            Kitchen::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'Kitchen')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }



    public function getPaymentMethod()
    {
        // Retrieve all ItemCategories
        $TableMaster = PaymentMethod::
            where('is_sync', 0)
            ->get();


        $deletedRows = DeletedRows::where('type', 'Kitchen')
            ->where('is_sync', 0)
            ->get(['key']); // only send the ID


        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }

    public function markPaymentMethodSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            PaymentMethod::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'PaymentMethod')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }




    public function getUsers($wh)
    {
        $TableMaster = User::with('role:id,name')
            ->where('is_sync', 0)
            ->where('ware_house_id', $wh)
            ->get()
            ->makeVisible(['password'])
            ->makeHidden(['role']); // hide nested role object

        $deletedRows = DeletedRows::where('type', 'User')
            ->where('is_sync', 0)
            ->get(['key']);

        return response()->json([
            'updated' => $TableMaster,
            'deleted' => $deletedRows
        ]);
    }


    public function markUsersSynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            User::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'User')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }





    public function getCompany()
    {
        // Get the company record
        $company = Company::first();

        $companyName = $company->name ?? '';
        $companyLogo = $company
            ? asset('storage/images/company/logo.png')
            : '';

        // Get all warehouses pending sync
        $warehouses = Warehouse::where('is_sync', 0)
            ->get()
            ->map(function ($wh) use ($companyName, $companyLogo) {
                return [
                    'id' => $wh->id,
                    'name' => $companyName,
                    'code' => $companyName,
                    'logo_url' => $companyLogo, // ✅ Add logo here
                    'address1' => $wh->address1,
                    'address2' => $wh->address2,
                    'city' => $wh->city,
                    'state' => $wh->state,
                    'contry' => $wh->contry,
                    'phone' => $wh->phone,
                    'fax' => $wh->fax,
                    'email' => $wh->email,
                    'price_level' => $wh->price_level,
                    'service_charge' => $wh->service_charge,
                    'void_pin' => $wh->void_pin,
                ];
            });

        // Get deleted rows for warehouses
        $deletedRows = DeletedRows::where('type', 'Warehouse')
            ->where('is_sync', 0)
            ->get(['key']);

        return response()->json([
            'updated' => $warehouses,
            'deleted' => $deletedRows
        ]);
    }
    public function markCompanySynced(Request $request)
    {
        // Expect arrays of IDs
        $updatedIds = $request->input('updated', []); // array of updated category IDs
        $deletedKeys = $request->input('deleted', []); // array of deleted row keys

        // Update updated categories
        if (!empty($updatedIds)) {
            Warehouse::whereIn('id', $updatedIds)->update(['is_sync' => 1]);
        }

        // Update deleted rows
        if (!empty($deletedKeys)) {
            DeletedRows::where('type', 'Warehouse')->whereIn('key', $deletedKeys)->update(['is_sync' => 1]);
        }

        return response()->json([
            'status' => 'success',
            'updated_synced' => count($updatedIds),
            'deleted_synced' => count($deletedKeys)
        ]);
    }


    public function verify()
    {
        // Retrieve all ItemCategories
        $Warehouse = Warehouse::all();
        $Company = Company::all();

        return response()->json([
            'verify' => 1,
            'Warehouse' => $Warehouse,
            'Company' => $Company,
        ]);
    }



    public function customerSync(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'name' => 'required|string',
            'contact' => 'nullable|string',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'email' => 'nullable|email',
            'vat_no' => 'nullable|string',
            'type' => 'nullable|string',
            'custom1' => 'nullable|string',
            'custom2' => 'nullable|string',
            'custom3' => 'nullable|string',
            'custom4' => 'nullable|string',
            'custom5' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Directly call CustomerController::create()
        $customerController = new CustomerController();
        return $customerController->create($request);
    }


    public function orderSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'ware_house_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'table' => 'required|string',
            'steward_id' => 'required|integer',
            'items' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $orderController = new InvoiceController();
        return $orderController->SaveOrder($request);
    }



    public function invoiceSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ware_house_id' => 'required|integer',
            'customer_id' => 'required|integer',
            'category' => 'required|string',
            'payment_method' => 'required|string',
            'item_count' => 'required|integer',
            'gross_total' => 'required',
            'dis_per' => 'required',
            'dis_total' => 'required',
            'vat_per' => 'required',
            'vat_total' => 'required',
            'nbt_per' => 'required',
            'nbt_total' => 'required',
            'service_charge_per' => 'required',
            'service_charge_total' => 'required',
            'delivery_charge' => 'required',
            'net_total' => 'required',
            'total_cost' => 'required',
            'credit_total' => 'required',
            'paid_total' => 'required',
            'balance' => 'required',
            'items' => 'required|array',
            'table' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $orderController = new InvoiceController();
        return $orderController->SaveInvoice($request);
    }



    public function invoice_paymentSync(Request $request, $invoiceId)
    {
        $validator = Validator::make($request->all(), [
            'payments' => 'required|array|min:1',
            'payments.*.method_id' => 'required|integer|exists:payment_method,id',
            'payments.*.amount' => 'required|numeric|min:0.01',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $orderController = new InvoiceController();
        return $orderController->saveInvoicePayments($request, $invoiceId);
    }

    public function updatePrintStatus(Request $request)
    {
        $order = OrderHeader::find($request->id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->is_print = true;
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Print status updated successfully'
        ]);
    }


}
