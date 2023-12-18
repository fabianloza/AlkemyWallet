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
        // funcion para crear las tablas en la base de datos 
        Schema::create('fixed_terms', function (Blueprint $table) {
            $table->id(); //columna id autoincremental
            $table->double('amount'); //monto de la transaccion
            $table->foreignId('account_id')->constrained('accounts'); //vinculacion con la tabla account
            $table->double('interest'); //interes de la transaccion
            $table->double('total'); //valor total de la transccion
            $table->integer('duration');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
        

    
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_terms'); //funcion para eliminar la tabla fixed_terms si es necesario
    }
};
