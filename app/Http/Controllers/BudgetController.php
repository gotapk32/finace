<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Expense;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::with('category')
            ->where('user_id', auth()->id())
            ->get();

        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $startOfMonth = Carbon::now()->startOfMonth();

        foreach ($budgets as $budget) {
            $spent = Expense::whereIn('user_id', $userIds)
                ->where('category_id', $budget->category_id)
                ->where('type', 'gasto')
                ->where(function ($q) use ($startOfMonth) {
                    $q->where(function($sq) use ($startOfMonth) {
                        $sq->where('is_recurring', false)
                           ->where('date', '>=', $startOfMonth);
                    })->orWhere(function($sq) {
                        $sq->where('is_recurring', true)
                           ->where('is_active', true);
                    });
                })
                ->where(function ($q) {
                    $q->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
                })
                ->sum('amount');
            
            $budget->spent = $spent;
            $budget->progress = $budget->amount > 0 ? min(100, ($spent / $budget->amount) * 100) : 0;
        }

        return $budgets;
    }

    public function report()
    {
        $budgets = Budget::with('category')
            ->where('user_id', auth()->id())
            ->get();

        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $startOfMonth = Carbon::now()->startOfMonth();

        return $budgets->map(function ($b) use ($userIds, $startOfMonth) {
            $spent = Expense::whereIn('user_id', $userIds)
                ->where('category_id', $b->category_id)
                ->where('type', 'gasto')
                ->where(function ($q) use ($startOfMonth) {
                    $q->where(function($sq) use ($startOfMonth) {
                        $sq->where('is_recurring', false)
                           ->where('date', '>=', $startOfMonth);
                    })->orWhere(function($sq) {
                        $sq->where('is_recurring', true)
                           ->where('is_active', true);
                    });
                })
                ->where(function ($q) {
                    $q->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
                })
                ->sum('amount');
            
            return [
                'id' => $b->id,
                'category_id' => $b->category_id,
                'category_name' => $b->category->name,
                'category_icon' => $b->category->icon,
                'limit' => (float)$b->amount,
                'spent' => (float)$spent,
                'percentage' => $b->amount > 0 ? round(($spent / $b->amount) * 100, 1) : 0
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $budget = Budget::updateOrCreate(
            ['user_id' => auth()->id(), 'category_id' => $validated['category_id']],
            ['amount' => $validated['amount']]
        );

        return response()->json($budget, 201);
    }

    public function destroy($id)
    {
        $budget = Budget::where('user_id', auth()->id())->findOrFail($id);
        $budget->delete();
        return response()->json(null, 204);
    }
}
