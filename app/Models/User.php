<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'users';
     
    // ATRIBUTOS QUE SE PUEDEN ASIGNAR EN MASA
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'role_id',
        'deleted'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    // ATRIBUTOS OCULTOS PARA LA SERIALIZACIÓN
    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

    protected $with = [
         'role',
     ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];
    
// RELACIONES DE FOREIGN KEYS
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
    public function account(): HasMany
    {
        return $this->hasMany(Account::class);
    }
    public function transactions()
    {
        return $this->hasManyThrough(
            Transaction::class,
            Account::class,
            'user_id', // Foreign key en la tabla accounts que apunta al id de users
            'account_id', // Foreign key en la tabla transactions que apunta al id de accounts
            'id', // Local key en la tabla users
            'id' // Local key en la tabla accounts
        );
    }
}