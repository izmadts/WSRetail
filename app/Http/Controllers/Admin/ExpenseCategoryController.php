<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('admin.expenses.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        ExpenseCategory::create($validated);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $category->update($validated);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category with expenses!');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
}
