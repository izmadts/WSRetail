<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount('sales')->orderBy('name')->get();
        return view('admin.settings.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.settings.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:locations',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'pos_type' => 'required|in:retail,wholesale,both',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Location::create($validated);

        return redirect()->route('admin.settings.locations.index')
            ->with('success', 'Location created successfully!');
    }

    public function edit(Location $location)
    {
        return view('admin.settings.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('locations')->ignore($location->id)],
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'pos_type' => 'required|in:retail,wholesale,both',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $location->update($validated);

        return redirect()->route('admin.settings.locations.index')
            ->with('success', 'Location updated successfully!');
    }

    public function destroy(Location $location)
    {
        if ($location->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete a location with sales recorded against it!');
        }

        $location->delete();

        return redirect()->route('admin.settings.locations.index')
            ->with('success', 'Location deleted successfully!');
    }
}
