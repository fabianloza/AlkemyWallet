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
        Schema::create('roles', function (Blueprint $table) {  // Crea la tabla 'roles' en la base de datos

            $table->id();  // Define el campo 'id' como clave primaria autoincremental

            $table->string('name', 10)->notNullable();  // Define el campo 'name' como VARCHAR de longitud 10 y no nulo

            $table->string('description')->nullable(); // Define el campo 'description' como VARCHAR y permite valores nulos

            $table->timestamps();// Agrega automáticamente los campos 'created_at' y 'updated_at' para el registro de tiempo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
