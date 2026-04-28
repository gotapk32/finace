<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Expense;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $michelle = User::updateOrCreate(
            ['email' => 'michelle@demo.com'],
            ['name' => 'Michelle', 'password' => Hash::make('password')]
        );

        $omer = User::updateOrCreate(
            ['email' => 'omer@demo.com'],
            ['name' => 'Omer', 'password' => Hash::make('password'), 'partner_id' => $michelle->id]
        );

        $michelle->update(['partner_id' => $omer->id]);

        // 2. Create Payment Methods
        $methods = [
            ['user_id' => $michelle->id, 'name' => 'Visa Michelle', 'type' => 'credito'],
            ['user_id' => $michelle->id, 'name' => 'Vales Despensa', 'type' => 'vales'],
            ['user_id' => $omer->id, 'name' => 'BBVA Omer', 'type' => 'debito'],
            ['user_id' => $omer->id, 'name' => 'Amex Omer', 'type' => 'credito'],
        ];

        foreach ($methods as $m) {
            PaymentMethod::firstOrCreate($m);
        }

        $michelleCard = PaymentMethod::where('user_id', $michelle->id)->first();
        $omerCard = PaymentMethod::where('user_id', $omer->id)->first();

        // 3. Create Expenses
        $data = [
            // Shared
            ['name' => 'Supermercado', 'amount' => 1200, 'date' => Carbon::now()->subDays(2), 'payer' => 'Michelle', 'user_id' => $michelle->id, 'type' => 'gasto', 'is_personal' => false],
            ['name' => 'Cena Sushi', 'amount' => 850, 'date' => Carbon::now()->subDays(1), 'payer' => 'Omer', 'user_id' => $omer->id, 'type' => 'gasto', 'is_personal' => false],
            ['name' => 'Gasolina', 'amount' => 600, 'date' => Carbon::now()->subDays(5), 'payer' => 'Omer', 'user_id' => $omer->id, 'type' => 'gasto', 'is_personal' => false],
            
            // Personal
            ['name' => 'Regalo Sorpresa', 'amount' => 500, 'date' => Carbon::now()->subDays(3), 'payer' => 'Michelle', 'user_id' => $michelle->id, 'type' => 'gasto', 'is_personal' => true],
            ['name' => 'Videojuego', 'amount' => 1200, 'date' => Carbon::now()->subDays(4), 'payer' => 'Omer', 'user_id' => $omer->id, 'type' => 'gasto', 'is_personal' => true],
            
            // Deuda
            ['name' => 'Préstamo Amigo', 'amount' => 2000, 'date' => Carbon::now()->subDays(10), 'payer' => 'Omer', 'user_id' => $omer->id, 'type' => 'deuda', 'is_personal' => false],
            
            // Recurring
            ['name' => 'Netflix', 'amount' => 219, 'date' => Carbon::now()->startOfMonth(), 'payer' => 'Michelle', 'user_id' => $michelle->id, 'type' => 'gasto', 'is_personal' => false, 'is_recurring' => true, 'due_day' => 15],
            ['name' => 'Renta Depa', 'amount' => 8500, 'date' => Carbon::now()->startOfMonth(), 'payer' => 'Omer', 'user_id' => $omer->id, 'type' => 'gasto', 'is_personal' => false, 'is_recurring' => true, 'due_day' => 5],
        ];

        foreach ($data as $d) {
            Expense::create($d);
        }
    }
}
