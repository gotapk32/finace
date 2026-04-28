<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // Global categories (user_id is null) + user's own categories
        return Category::whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);

        $category = auth()->user()->categories()->create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = auth()->user()->categories()->findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);
        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = auth()->user()->categories()->findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
