<?php
/**
 * SCRIPT TEMPORAL PARA MIGRACIONES EN HOSTINGER
 * Sube este archivo a la raíz de tu proyecto en Hostinger y ábrelo en tu navegador.
 * Ejemplo: tudominio.com/gastos/migrate_remote.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Artisan;

echo "<h1>Ejecutando Migraciones de M&O Finance...</h1>";

try {
    Artisan::call('migrate', ['--force' => true]);
    echo "<pre>" . Artisan::output() . "</pre>";
    echo "<p style='color:green;'>✅ Migraciones completadas con éxito.</p>";
    echo "<p><b>IMPORTANTE:</b> Borra este archivo (migrate_remote.php) inmediatamente después de usarlo por seguridad.</p>";
} catch (\Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
