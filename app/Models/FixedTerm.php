<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedTerm extends Model
{
    use HasFactory;

    //asigna qué columnas de la base de datos se pueden llenar de forma masiva
    protected $fillable = [
        'amount',
        'account_id',
        'interest',
        'total',
        'duration'
    ];

    //oculta los siguientes campos para que no aparezcan al hacer una consulta a la base de datos
    protected $hidden = [
        'created_at',
        'updated_at',
        'closed_at'
    ];

    //relacion con el modelo account 
      public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }  

}
