<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Expense;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function viewIndex() { return view('trips.index'); }
    public function viewCreate() { return view('trips.create'); }
    public function viewShow($id) { return view('trips.show', compact('id')); }

    public function index()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) {
            $userIds[] = auth()->user()->partner_id;
        }

        return Trip::whereIn('user_id', $userIds)
            ->where(function ($query) {
                $query->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
            })
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_personal' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_personal'] = $request->boolean('is_personal');
        $validated['is_active'] = true;

        $trip = Trip::create($validated);
        return response()->json($trip, 201);
    }

    public function update(Request $request, $id)
    {
        $trip = Trip::where('user_id', auth()->id())->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|max:255',
            'destination' => 'string|max:255',
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_personal' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->has('is_personal')) $validated['is_personal'] = $request->boolean('is_personal');
        if ($request->has('is_active')) $validated['is_active'] = $request->boolean('is_active');

        $trip->update($validated);
        return response()->json($trip);
    }

    public function destroy($id)
    {
        $trip = Trip::where('user_id', auth()->id())->findOrFail($id);
        $trip->delete();
        return response()->json(null, 204);
    }

    public function summary($id)
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $trip = Trip::whereIn('user_id', $userIds)->findOrFail($id);

        $expenses = Expense::where('trip_id', $id)
            ->with('category')
            ->orderBy('date', 'desc')
            ->get();

        $totalSpent = $expenses->sum(fn($e) => (float)$e->amount);

        return response()->json([
            'trip' => $trip,
            'expenses' => $expenses,
            'total_spent' => $totalSpent,
            'budget' => (float)$trip->budget,
            'remaining' => (float)$trip->budget - $totalSpent,
        ]);
    }
}
