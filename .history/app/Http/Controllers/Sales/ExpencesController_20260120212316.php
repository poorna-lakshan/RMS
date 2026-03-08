<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\ExpencesCategory;
use App\Models\Sales\Expences;
use Illuminate\Support\Facades\Validator;
use App\Helpers\LogHelper;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Main\DefaultController;

class ExpencesController extends Controller
{
    // Start ExpencesCategory
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($request->id != 0) {
            $ExpencesCategory = ExpencesCategory::find($request->id);

            if (!$ExpencesCategory) {
                return response()->json(['error' => 'Expences Category not found'], 404);
            }

            $ExpencesCategory->update([
                'description' => $request->description,
            ]);
        } else {
            $ExpencesCategory = ExpencesCategory::create([
                'description' => $request->description,
            ]);
        }

        LogHelper::log(
            auth()->user()->name,
            $request->id != 0 ? 'update' : 'create',
            'ExpencesCategory',
            ($request->id != 0 ? 'Update' : 'Create') . ' category: ' . $request->description
        );

        if ($request->id != 0) {
            return response()->json(['message' => 'Expences Category updated successfully'], 200);
        } else {
            return response()->json(['message' => 'Expences Category created successfully'], 201);
        }
    }

    public function deleteCategory($id)
    {
        $ExpencesCategory = ExpencesCategory::find($id);

        if (!$ExpencesCategory) {
            return response()->json(['error' => 'Expences Category not found'], 404);
        }

        // Check if category is being used in expenses
        $expensesCount = Expences::where('category_id', $id)->count();
        if ($expensesCount > 0) {
            return response()->json(['error' => 'Cannot delete category. It is being used in ' . $expensesCount . ' expense(s).'], 400);
        }

        $ExpencesCategory->delete();

        LogHelper::log(
            auth()->user()->name,
            'delete',
            'ExpencesCategory',
            'Delete category: ' . $ExpencesCategory->description
        );

        return response()->json(['message' => 'Expences Category deleted successfully'], 200);
    }

    public function indexCategory()
    {
        $ExpencesCategory = ExpencesCategory::orderBy('description')->get();

        LogHelper::log(
            auth()->user()->name,
            'view',
            'ExpencesCategory',
            'View all categories'
        );

        return response()->json($ExpencesCategory);
    }
    // End ExpencesCategory

    // Start Expences CRUD Operations
    public function createExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:expences,code',
            'ware_house_id' => 'required|integer',
            'category_id' => 'required|integer|exists:expence_category,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $expense = Expences::create([
            'code' => $request->code,
            'ware_house_id' => $request->ware_house_id,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'user' => auth()->user()->name,
        ]);

        $df = new DefaultController();
        $df->UpdateCode('exp');

        LogHelper::log(
            auth()->user()->name,
            'create',
            'Expences',
            'Create expense: ' . $request->code . ' - Amount: ' . $request->amount
        );

        return response()->json([
            'message' => 'Expense created successfully',
            'expense' => $expense
        ], 201);
    }

    public function updateExpense(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'ware_house_id' => 'required|integer',
            'category_id' => 'required|integer|exists:expence_category,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $expense = Expences::find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        // Check if user can edit (optional: add permission check)
        $expense->update([
            'ware_house_id' => $request->ware_house_id,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'created_at' => $request->created_at,
            'user' => auth()->user()->name,
        ]);

        LogHelper::log(
            auth()->user()->name,
            'update',
            'Expences',
            'Update expense: ' . $expense->code . ' - Amount: ' . $request->amount
        );

        return response()->json([
            'message' => 'Expense updated successfully',
            'expense' => $expense
        ], 200);
    }

    public function deleteExpense($id)
    {
        $expense = Expences::find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        // Optional: Add business logic checks before deletion
        // For example, check if expense is from a reconciled period

        $expense->delete();

        LogHelper::log(
            auth()->user()->name,
            'delete',
            'Expences',
            'Delete expense: ' . $expense->code . ' - Amount: ' . $expense->amount
        );

        return response()->json(['message' => 'Expense deleted successfully'], 200);
    }

    public function getExpense($id)
    {
        $expense = Expences::with(['category', 'warehouse', 'user'])
            ->find($id);

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        LogHelper::log(
            auth()->user()->name,
            'view',
            'Expences',
            'View expense: ' . $expense->code
        );

        return response()->json($expense);
    }

    public function indexExpenses(Request $request)
    {
        $query = Expences::with(['category', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->orderBy('created_at', 'desc');

        // Add filters
        if ($request->has('ware_house_id')) {
            $query->where('ware_house_id', $request->ware_house_id);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        $perPage = $request->has('per_page') ? $request->per_page : 15;
        $expenses = $query->paginate($perPage);

        LogHelper::log(
            auth()->user()->name,
            'view',
            'Expences',
            'View expenses list'
        );

        return response()->json($expenses);
    }

    public function getExpenseSummary(Request $request)
    {
        $query = Expences::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->has('ware_house_id')) {
            $query->where('ware_house_id', $request->ware_house_id);
        }

        // Summary by category
        $categorySummary = $query->clone()
            ->join('expence_category', 'expences.category_id', '=', 'expence_category.id')
            ->selectRaw('expence_category.description as category, SUM(expences.amount) as total_amount')
            ->groupBy('expences.category_id', 'expence_category.description')
            ->get();

        // Total expenses
        $totalExpenses = $query->clone()->sum('amount');

        // Daily totals
        $dailySummary = $query->clone()
            ->selectRaw('created_at, SUM(amount) as daily_total')
            ->groupBy('created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        $detailsQuery = clone $query;
        $details = $detailsQuery->orderBy('created_at', 'desc')->get();


        LogHelper::log(
            auth()->user()->name,
            'view',
            'Expences',
            'View expense summary'
        );

        return response()->json([
            'total_expenses' => $totalExpenses,
            'category_summary' => $categorySummary,
             'daily_summary' => $dailySummary,
            'details' => $details,
        ]);
    }

    public function getDefault()
    {
        $df = new DefaultController();
        $code = $df->generateCode('exp');

        $item_categories = $this->indexCategory()->getData(true);

        $warehouse = new WarehouseController();
        $warehouse_list = $warehouse->index()->getData(true);

        // Get payment methods (you can define these in a config or database table)
        $paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'Check', 'Digital Payment'];

        return response()->json([
            'code' => $code,
            'categories' => $item_categories,
            'warehouse_list' => $warehouse_list,
            'payment_methods' => $paymentMethods,
        ]);
    }
}