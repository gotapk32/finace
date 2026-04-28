<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $col) {
            $col->boolean('is_paid')->default(false);
            $col->string('payment_proof')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $col) {
            $col->dropColumn(['is_paid', 'payment_proof']);
        });
    }
};
