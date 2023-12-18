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
        Schema::create('transactions', function (Blueprint $table) {  //Funcion creadora de la tabla "transactions" en db
            $table->id(); //Columna autoincremental 'id'
            $table->double('amount'); //Columna perteneciente al monto numerico de la transaccion
            $table->string('type', 20); //Columna de tipo de transaccion (INCOME, PAYMENT, DEPOSIT)
            $table->string('description')->nullable(); //Columna descripcion, puede dejarse en blanco
            $table->foreignId('account_id')->constrained('accounts'); //Columna de id de la cuenta dueña de transaccion, relacionada con la tabla 'accounts'
            $table->timestamp('transaction_date');//Columna recopiladora de la fecha en la que se realiza la transaccion del usuario
            $table->timestamps();//Activado de timestamps como registro en db
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    { {
            Schema::dropIfExists('transactions');//Rollback de tabla de db
        }
    }
};
