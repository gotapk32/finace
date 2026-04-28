<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\AdminController;

Route::middleware('auth')->group(function () {
    // Page Routes
    Route::get('/', function() { return redirect()->route('dashboard'); });
    Route::get('/dashboard', function() { return view('dashboard'); })->name('dashboard');
    Route::get('/gastos', [ExpenseController::class, 'viewIndex'])->name('expenses.index');
    Route::get('/gastos/nuevo', [ExpenseController::class, 'viewCreate'])->name('expenses.create');
    Route::get('/gastos/recurrentes', [ExpenseController::class, 'viewRecurring'])->name('expenses.recurring');
    Route::get('/wallet', function() { 
        $invitation = \App\Models\UserInvitation::where('created_by_user_id', auth()->id())->first();
        return view('wallet', compact('invitation')); 
    })->name('wallet');
    Route::get('/presupuestos', function() { return view('budgets.index'); })->name('budgets.index');
    Route::get('/ajustes', function() { return view('settings'); })->name('settings');

    Route::post('/link-partner', [AuthController::class, 'linkPartner'])->name('link-partner');
    Route::post('/unlink-partner', [AuthController::class, 'unlinkPartner'])->name('unlink-partner');
    Route::post('/invitations/generate', [AuthController::class, 'storeUserInvitation'])->name('user.invitations.store');
    Route::post('/invitations/{id}/accept', [AuthController::class, 'acceptInvitation'])->name('invitations.accept');
    Route::post('/invitations/{id}/reject', [AuthController::class, 'rejectInvitation'])->name('invitations.reject');
    Route::post('/update-salary', [AuthController::class, 'updateSalary'])->name('update-salary');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // API Routes
    Route::prefix('api')->group(function() {
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);
        Route::post('/expenses/{id}/settle', [ExpenseController::class, 'settleDebt']);
        Route::get('/expenses/summary', [ExpenseController::class, 'summary']);
        Route::get('/expenses/trend', [ExpenseController::class, 'getTrendData']);
        Route::get('/expenses/export/pdf', [ExpenseController::class, 'exportPDF'])->name('expenses.export.pdf');
        Route::get('/expenses/export/csv', [ExpenseController::class, 'exportCSV'])->name('expenses.export.csv');

        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);

        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::put('/budgets/{id}', [BudgetController::class, 'update']);
        Route::delete('/budgets/{id}', [BudgetController::class, 'destroy']);
        Route::get('/budgets/report', [BudgetController::class, 'report']);
    });
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/invitation/{token}', [AuthController::class, 'showRegisterByToken'])->name('invitation.register');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function() {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/invitations', [AdminController::class, 'storeInvitation'])->name('admin.invitations.store');
    Route::delete('/invitations/{id}', [AdminController::class, 'deleteInvitation'])->name('admin.invitations.destroy');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
});
