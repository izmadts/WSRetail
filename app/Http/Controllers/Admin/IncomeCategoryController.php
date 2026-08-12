<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncomeCategoryController extends Controller
{
    public function index()
    {
        $categories = IncomeCategory::orderBy('name')->get();
        return view('admin.incomes.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:income_categories',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        IncomeCategory::create($validated);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, IncomeCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('income_categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $category->update($validated);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy(IncomeCategory $category)
    {
        if ($category->incomes()->count() > 0) {
            return back()->with('error', 'Cannot delete category with income records!');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
}
