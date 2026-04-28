<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

foreach(\App\Models\User::all() as $u) {
    echo "{$u->email} | Admin: " . ($u->is_admin ? 'YES' : 'NO') . "\n";
}
