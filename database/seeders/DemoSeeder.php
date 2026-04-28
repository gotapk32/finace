<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\PaymentMethod;
use App\Models\Budget;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'michelle@demo.com')->first() ?: User::first();
        if (!$user) return;
        
        $partner = $user->partner;
        if (!$partner) {
            $partner = User::create([
                'name' => 'Omer',
                'email' => 'omer@demo.com',
                'password' => bcrypt('password'),
            ]);
            $user->partner_id = $partner->id;
            $user->save();
            $partner->partner_id = $user->id;
            $partner->save();
        }

        // Limpiar datos previos para el demo
        Expense::whereIn('user_id', [$user->id, $partner->id])->delete();
        Budget::whereIn('user_id', [$user->id, $partner->id])->delete();
        PaymentMethod::whereIn('user_id', [$user->id, $partner->id])->delete();

        // 1. Métodos de Pago
        $mCard = PaymentMethod::create(['user_id' => $user->id, 'name' => 'Visa Michelle', 'type' => 'debito']);
        $oCard = PaymentMethod::create(['user_id' => $partner->id, 'name' => 'Amex Omer', 'type' => 'credito']);

        // 2. Categorías (Asegurar que existan las básicas)
        $catSúper = Category::firstOrCreate(['name' => 'Supermercado'], ['icon' => '🛒']);
        $catComida = Category::firstOrCreate(['name' => 'Comida'], ['icon' => '🍔']);
        $catRenta = Category::firstOrCreate(['name' => 'Renta'], ['icon' => '🏠']);
        $catOcio = Category::firstOrCreate(['name' => 'Ocio'], ['icon' => '🎬']);

        // 3. Gastos Compartidos (Mes Actual)
        Expense::create([
            'user_id' => $user->id, 'category_id' => $catSúper->id, 'payment_method_id' => $mCard->id,
            'name' => 'Súper Semanal', 'amount' => 1200, 'date' => now()->subDays(2), 
            'payer' => $user->name, 'type' => 'gasto', 'is_personal' => false
        ]);

        Expense::create([
            'user_id' => $partner->id, 'category_id' => $catComida->id, 'payment_method_id' => $oCard->id,
            'name' => 'Cena Sushi', 'amount' => 850, 'date' => now()->subDays(1), 
            'payer' => $partner->name, 'type' => 'gasto', 'is_personal' => false
        ]);

        // 4. Gastos Personales
        Expense::create([
            'user_id' => $user->id, 'category_id' => $catOcio->id, 
            'name' => 'Suscripción Gym', 'amount' => 600, 'date' => now()->subDays(5), 
            'payer' => $user->name, 'type' => 'gasto', 'is_personal' => true
        ]);

        // 5. Gastos Recurrentes (Activos)
        Expense::create([
            'user_id' => $user->id, 'category_id' => $catRenta->id, 
            'name' => 'Renta Departamento', 'amount' => 8000, 'date' => now()->startOfMonth(), 
            'payer' => 'Michelle', 'type' => 'gasto', 'is_personal' => false, 'is_recurring' => true, 'is_active' => true, 'due_day' => 1
        ]);

        // 6. Deudas
        Expense::create([
            'user_id' => $user->id, 'category_id' => $catComida->id, 
            'name' => 'Préstamo para café', 'amount' => 50, 'date' => now(), 
            'payer' => $user->name, 'type' => 'deuda', 'debt_direction' => 'to_me'
        ]);

        Expense::create([
            'user_id' => $user->id, 'category_id' => $catSúper->id, 
            'name' => 'Debo de la pizza', 'amount' => 200, 'date' => now(), 
            'payer' => $user->name, 'type' => 'deuda', 'debt_direction' => 'to_them'
        ]);

        // 7. Presupuestos
        Budget::create(['user_id' => $user->id, 'category_id' => $catSúper->id, 'amount' => 5000]);
        Budget::create(['user_id' => $user->id, 'category_id' => $catComida->id, 'amount' => 3000]);
        Budget::create(['user_id' => $user->id, 'category_id' => $catOcio->id, 'amount' => 1000]);
    }
}
