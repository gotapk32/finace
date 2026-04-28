<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('salary')->nullable()->change();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('amount')->change();
            $table->text('payer')->change();
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('salary', 15, 2)->nullable()->change();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('name')->change();
            $table->decimal('amount', 15, 2)->change();
            $table->string('payer')->change();
        });
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }
};
