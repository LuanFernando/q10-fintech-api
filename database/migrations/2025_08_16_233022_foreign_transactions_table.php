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
        Schema::table('transactions', function (Blueprint $table) {
           $table->foreignId('account_id')
                  ->constrained('accounts')
                  ->cascadeOnDelete(); // Substitui ->onDelete('cascade')

            $table->foreignId('contact_id')
                  ->constrained('contacts')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['account_id', 'contact_id']);
        });
    }
};
