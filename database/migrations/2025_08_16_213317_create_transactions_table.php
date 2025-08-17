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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // id autoincremento
            $table->decimal('valor', 15, 2); // valor da transação
            $table->date('data_transacao');  // data da transação
            $table->time('hora_transacao');  // hora da transação

            $table->enum('status', ['sucesso', 'bloqueado', 'cancelada'])->default('sucesso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
