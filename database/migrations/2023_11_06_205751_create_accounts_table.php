<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Correr las migraciones.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            //se creara la tabla "accounts" dentro de la base de datos con los siguientes campos
            $table->id();
            $table->string('currency', 3)->notNull(); // Divisa a utilizar (ARS,USD)
            $table->double('transaction_limit')->notNull(); // Limite de transacción
            $table->double('balance')->notNull(); // Balance de la cuenta
            $table->foreignId('user_id')->constrained('users'); // Clave foranea hacia ID de User
            $table->string('cbu', 22)->unique()->notNull(); // Numero de CBU de 22 digitos
            $table->timestamps(); // Fecha de creación y de actualización
            $table->boolean('deleted')->default(false); // Borrado logico
        });
    }

    /**
     * Retrotraer las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
