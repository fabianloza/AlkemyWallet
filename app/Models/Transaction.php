<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model //Creacion de la nueva clase "Transaction"
{
    use HasFactory;

    protected $fillable  = [ //Configuracion de las propiedades rellenables en base de datos
        'amount', //Monto de la transaccion
        'type', //Tipo de transaccion (INCOME, PAYMENT, DEPOSIT)
        'description', //Descripcion de la operacion (puede ser valor nulo)
        'account_id', //Clave foranea hacia ID de tabla "account" correspondiente a la cuenta responsable de la operacion
        'transaction_date', //Registro de fecha y hora en que fue realizada la transaccion
    ];

    protected $dates = ['transaction_date'];

    protected $hidden = ['created_at', 'updated_at'];

    public function getTransactionDateAttribute($value)
    {
        return Carbon::parse($value)->timezone('America/Argentina/Buenos_Aires')->format('d-m-y H:i:s');
    }

    //Esta funcion indica que nuestro modelo "transaction" pertenece a "account" y asocia a los datos de la transaccion a la cuenta del cliente.
        public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
