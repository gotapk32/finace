<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ExpenseController extends Controller
{
    public function viewIndex() { return view('expenses.index'); }
    public function viewCreate() { return view('expenses.create'); }
    public function viewRecurring() { return view('expenses.recurring'); }

    public function index()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) {
            $userIds[] = auth()->user()->partner_id;
        }

        return Expense::whereIn('user_id', $userIds)
            ->where(function ($query) {
                $query->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'payer' => 'required|string',
            'type' => 'required|in:gasto,deuda',
            'debt_direction' => 'nullable|in:to_me,to_them',
            'image_file' => 'nullable|image|max:5120',
            'is_personal' => 'boolean',
            'is_recurring' => 'boolean',
            'category_id' => 'required|exists:categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'due_day' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('receipts', 'public');
            $validated['image'] = $path;
        }

        $validated['user_id'] = auth()->id();
        $validated['is_personal'] = $request->boolean('is_personal');
        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $expense = Expense::create($validated);
        return response()->json($expense, 201);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::where('user_id', auth()->id())->findOrFail($id);
        $validated = $request->validate([
            'name' => 'string|max:255',
            'amount' => 'numeric',
            'date' => 'date',
            'payer' => 'string',
            'is_personal' => 'boolean',
            'is_recurring' => 'boolean',
            'is_active' => 'boolean',
            'category_id' => 'exists:categories,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'due_day' => 'nullable|integer',
        ]);

        if ($request->has('is_personal')) $validated['is_personal'] = $request->boolean('is_personal');
        if ($request->has('is_recurring')) $validated['is_recurring'] = $request->boolean('is_recurring');
        if ($request->has('is_active')) $validated['is_active'] = $request->boolean('is_active');

        $expense->update($validated);
        return response()->json($expense);
    }

    public function destroy($id)
    {
        $expense = Expense::where('user_id', auth()->id())->findOrFail($id);
        $expense->delete();
        return response()->json(null, 204);
    }

    public function summary()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $baseQuery = Expense::whereIn('expenses.user_id', $userIds)
            ->where(function ($query) {
                $query->where('expenses.is_personal', false)
                      ->orWhere('expenses.user_id', auth()->id());
            });

        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth();

        // Totales básicos (Gasto real este mes)
        $monthQuery = (clone $baseQuery)->where('date', '>=', $startOfMonth)->where('type', 'gasto');
        $monthExpenses = $monthQuery->get();
        
        $dayTotal = $monthExpenses->filter(fn($e) => $e->date == $today)->sum(fn($e) => (float)$e->amount);
        
        // Suma de gastos normales + recurrentes activos
        $recurringExpenses = (clone $baseQuery)->where('is_recurring', true)->where('is_active', true)->where('type', 'gasto')->get();
        $recurringTotal = $recurringExpenses->sum(fn($e) => (float)$e->amount);
        
        $monthTotal = $monthExpenses->filter(fn($e) => !$e->is_recurring)->sum(fn($e) => (float)$e->amount) + $recurringTotal;

        // Desglose Compartido vs Personal (Mes actual)
        $sharedTotal = $monthExpenses->filter(fn($e) => !$e->is_personal && !$e->is_recurring)->sum(fn($e) => (float)$e->amount) + $recurringTotal;
        $personalTotal = $monthExpenses->filter(fn($e) => $e->is_personal)->sum(fn($e) => (float)$e->amount);

        // Deudas (Saldo histórico acumulado NO pagado)
        $debts = (clone $baseQuery)->where('type', 'deuda')->where('is_paid', false)->get();
        $meDeben = $debts->where('debt_direction', 'to_me')->sum(fn($e) => (float)$e->amount);
        $yoDebo = $debts->where('debt_direction', 'to_them')->sum(fn($e) => (float)$e->amount);

        // Cálculo de Liquidación (Michelle vs Omer)
        $sharedMonthExpenses = $monthExpenses->filter(fn($e) => !$e->is_personal);
        $paidByUser = $sharedMonthExpenses->where('payer', auth()->user()->name)->sum(fn($e) => (float)$e->amount);
        $paidByPartner = 0;
        if (auth()->user()->partner) {
            $paidByPartner = $sharedMonthExpenses->where('payer', auth()->user()->partner->name)->sum(fn($e) => (float)$e->amount);
        }
        
        $totalShared = $paidByUser + $paidByPartner;
        $fairShare = $totalShared / 2;
        $netBalance = $paidByUser - $fairShare;

        // Integrar la liquidación en los totales de deudas para evitar confusión
        if ($netBalance > 0) {
            $meDeben += $netBalance;
        } elseif ($netBalance < 0) {
            $yoDebo += abs($netBalance);
        }

        // Desglose por Categoría (Mes actual)
        $categories = $monthExpenses->groupBy(function($e) {
            $cat = $e->category;
            return ($cat->icon ?? '💰') . ' ' . ($cat->name ?? 'Sin Categoría');
        })->map(function($group, $label) {
            return ['label' => $label, 'total' => (float)$group->sum(fn($e) => (float)$e->amount)];
        })->values()->sortByDesc('total');

        // Desglose por Pagador (Mes actual)
        $byPayer = $monthExpenses->groupBy('payer')->map(function($group, $payer) {
            return ['payer' => $payer, 'total' => (float)$group->sum(fn($e) => (float)$e->amount)];
        })->values();

        // Presupuestos Totales
        $allBudgets = Budget::where('user_id', auth()->id())->get();
        $totalLimit = $allBudgets->sum('amount');
        $totalSpentInBudgetedCategories = 0;
        foreach($allBudgets as $b) {
            $totalSpentInBudgetedCategories += $monthExpenses->where('category_id', $b->category_id)->sum(fn($e) => (float)$e->amount);
        }

        // Invitaciones de Pareja Pendientes
        $invitations = \App\Models\PartnerInvitation::where('receiver_id', auth()->id())
            ->where('status', 'pending')
            ->with('sender')
            ->get()
            ->map(function($inv) {
                return [
                    'id' => $inv->id,
                    'sender_name' => $inv->sender->name,
                    'type' => 'invitation'
                ];
            });

        return response()->json([
            'day' => $dayTotal,
            'month' => $monthTotal,
            'shared' => $sharedTotal,
            'personal' => $personalTotal,
            'me_deben' => $meDeben,
            'yo_debo' => $yoDebo,
            'net_balance' => $netBalance,
            'total_budget_limit' => $totalLimit,
            'total_budget_spent' => $totalSpentInBudgetedCategories,
            'paid_by_me' => $paidByUser,
            'paid_by_partner' => $paidByPartner,
            'by_category' => $categories,
            'by_payer' => $byPayer,
            'user_name' => auth()->user()->name,
            'partner_name' => auth()->user()->partner?->name,
            'monthly_salary' => $this->getMonthlySalary(),
            'reminders' => $this->getReminders(),
            'invitations' => $invitations
        ]);
    }

    private function getMonthlySalary()
    {
        $user = auth()->user();
        if (!$user->salary) return 0;
        
        switch ($user->salary_period) {
            case 'semanal': return $user->salary * 4.33; // Promedio semanas mes
            case 'quincenal': return $user->salary * 2;
            case 'mensual': return $user->salary;
            default: return 0;
        }
    }

    private function getReminders()
    {
        $reminders = [];
        $today = now()->day;
        
        // 1. Credit Card Reminders
        $cards = auth()->user()->paymentMethods()->where('type', 'credito')->get();
        foreach ($cards as $card) {
            if ($card->cut_day) {
                $daysToCut = $card->cut_day - $today;
                if ($daysToCut >= 0 && $daysToCut <= 5) {
                    $reminders[] = [
                        'type' => 'card_cut',
                        'title' => "Corte de {$card->name}",
                        'message' => $daysToCut == 0 ? "¡Hoy es el día de corte!" : "Faltan $daysToCut días para el corte.",
                        'color' => 'var(--primary)'
                    ];
                }
            }
            if ($card->payment_day) {
                $daysToPay = $card->payment_day - $today;
                if ($daysToPay >= 0 && $daysToPay <= 5) {
                    $reminders[] = [
                        'type' => 'card_payment',
                        'title' => "Pago de {$card->name}",
                        'message' => $daysToPay == 0 ? "¡Hoy es el último día de pago!" : "Faltan $daysToPay días para la fecha límite de pago.",
                        'color' => 'var(--secondary)'
                    ];
                }
            }
        }

        // 2. Recurring Expenses Reminders
        $recurring = Expense::where('user_id', auth()->id())
            ->where('is_recurring', true)
            ->where('is_active', true)
            ->get();
            
        foreach ($recurring as $rec) {
            if ($rec->due_day) {
                $daysToDue = $rec->due_day - $today;
                if ($daysToDue >= 0 && $daysToDue <= 3) {
                    $reminders[] = [
                        'type' => 'recurring_due',
                        'title' => "Cargo de {$rec->name}",
                        'message' => $daysToDue == 0 ? "Hoy se cobra {$rec->name}." : "En $daysToDue días se cobrará {$rec->name}.",
                        'color' => 'var(--accent)'
                    ];
                }
            }
        }

        return $reminders;
    }

    public function getTrendData()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $last6Months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = (clone $month)->startOfMonth();
            $end = (clone $month)->endOfMonth();

            $total = Expense::whereIn('user_id', $userIds)
                ->where('type', 'gasto')
                ->whereBetween('date', [$start, $end])
                ->where(function ($query) {
                    $query->where('is_personal', false)
                          ->orWhere('user_id', auth()->id());
                })
                ->get()
                ->sum(fn($e) => (float)$e->amount);

            $last6Months[] = [
                'month' => $month->translatedFormat('M'),
                'total' => (float)$total
            ];
        }

        return response()->json($last6Months);
    }

    public function exportPDF()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $expenses = Expense::with('category')
            ->whereIn('user_id', $userIds)
            ->where('date', '>=', now()->startOfMonth())
            ->where('type', 'gasto')
            ->where(function ($query) {
                $query->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
            })
            ->orderBy('date', 'desc')
            ->get();

        $total = $expenses->sum('amount');

        $pdf = Pdf::loadView('exports.expenses_pdf', compact('expenses', 'total'));
        return $pdf->download('Reporte_Gastos_' . now()->format('Y_m') . '.pdf');
    }

    public function exportCSV()
    {
        $userIds = [auth()->id()];
        if (auth()->user()->partner_id) $userIds[] = auth()->user()->partner_id;

        $expenses = Expense::with('category')
            ->whereIn('user_id', $userIds)
            ->where('type', 'gasto')
            ->where(function ($query) {
                $query->where('is_personal', false)
                      ->orWhere('user_id', auth()->id());
            })
            ->orderBy('date', 'desc')
            ->get();

        $callback = function() use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fecha', 'Concepto', 'Categoria', 'Pagador', 'Monto', 'Tipo']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date,
                    $expense->name,
                    $expense->category->name,
                    $expense->payer,
                    $expense->amount,
                    $expense->is_personal ? 'Privado' : 'Compartido'
                ]);
            }
            fclose($file);
        };

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Gastos_' . now()->format('Y_m_d') . '.csv"',
        ];

        return response()->stream($callback, 200, $headers);
    }

    public function settleDebt(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->is_paid = true;
        
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('receipts', 'public');
            $expense->payment_proof = $path;
        }

        $expense->save();
        return response()->json(['message' => 'Deuda saldada correctamente']);
    }
}
