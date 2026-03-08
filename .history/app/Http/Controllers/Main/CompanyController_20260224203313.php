<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\default\Company;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Validator;
class CompanyController extends Controller
{
    /**
     * Get all companies
     */
    public function index()
    {
        $companies = Company::all()->map(function ($company) {

         // $company->logo_url = asset('storage/app/public/images/company/logo.png');//after host
        $company->logo_url = asset('storage/images/company/logo.png');

            return $company;
        });

        return response()->json([
            'status' => true,
            'data' => $companies
        ]);
    }

    /**
     * Get single company by ID
     */
    public function show($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $company
        ]);
    }

    /**
     * Create new company
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'contry' => 'nullable|string',
            'phone' => 'nullable|string',
            'fax' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|string',
            'service_charge' => 'nullable|numeric',
            'invoice_sms' => 'nullable|boolean',
            'invoice_sms_template' => 'nullable|string',
            'sms_api' => 'nullable|string',
            'void_pin' => 'nullable|string',
        ]);


        $validatedData['is_sync'] = 0;

        if ($request->hasFile('image')) {
            $request->file('image')->storeAs(
                'images/company',
                'logo.png',        // fixed name
                'public'
            );
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get only validated data
        $validatedData = $validator->validated();

        // Always keep ONE company record
        $company = Company::updateOrCreate(
            ['id' => 1],          // condition
            $validatedData        // ✅ correct data
        );

        Warehouse::where('is_sync', operator: 0)->update(['is_sync' => 0]);

        return response()->json([
            'status' => true,
            'message' => 'Company saved successfully',
            'data' => $company
        ]);
    }

}
