<?php

namespace App\Http\Controllers;

use App\Models\GoldType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GoldTypeController extends Controller
{
    public function create()
    {
        return Inertia::render('GoldType/Create', [
            // Fetch only active ones, or use withTrashed() if you want to show deleted
            'goldTypes' => GoldType::orderBy('display_order', 'asc')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|unique:gold_types,code',
            'category' => 'required|in:world,myanmar,other',
            'purity' => 'nullable|string|max:50',
            'unit' => 'required|string|max:50',
            'system' => 'nullable|in:new,old',
            'gram_conversion' => 'nullable|numeric',
            'color_code' => 'nullable|string|max:7',
        ]);

        GoldType::create(array_merge($validated, [
            'created_by' => Auth::id(),
            'is_active' => true,
            'display_order' => GoldType::count() + 1,
        ]));

        // Redirect back to the same page to show the updated list
        return back()->with('success', 'New Gold Type registered successfully.');
    }

    public function destroy(GoldType $goldTypes)
    {
        // This will now set 'deleted_at' instead of a hard delete
        $goldTypes->delete();

        return back()->with('success', 'Gold category moved to trash.');
    }

    public function restore($id)
    {
        // If you ever need to bring it back:
        GoldType::withTrashed()->findOrFail($id)->restore();

        return back()->with('success', 'Category restored successfully.');
    }
}
