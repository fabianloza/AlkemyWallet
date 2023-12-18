<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Account extends Model
{
    use HasFactory;

    protected $table = "accounts";

    protected $fillable = [ //Las propiedades que se rellenaran en la base de datos
        'currency', // Divisa a utilizar (ARS, USD)
        'transaction_limit', // Limite de transacción
        'balance', // Balance
        'user_id', // Clave foranea hacia ID de User
        'cbu', // Numero de CBU de 22 digitos
        'deleted' // Borrado logico
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
