<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$debts = \App\Models\Expense::where('type', 'deuda')->get();
foreach($debts as $d) {
    echo "ID: {$d->id} | UserID: {$d->user_id} | Personal: " . ($d->is_personal ? 'YES' : 'NO') . " | Amount: {$d->amount} | Direction: {$d->debt_direction} | Paid: " . ($d->is_paid ? 'YES' : 'NO') . "\n";
}
