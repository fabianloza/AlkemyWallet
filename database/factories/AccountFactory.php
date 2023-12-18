<?php

namespace Database\Factories;
use App\Models\User;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $array_currency = ['ARS','USD'];//un array con 2 posibilidades 
        $currency = fake()->randomElement($array_currency);//elegimos una opcion random

        if ($currency === 'ARS') {//si es en pesos su limite sera de 30000 sino de 1000
            $transaction_limit = 300000;
        } else {
            $transaction_limit = 1000;
        }
        $user_id = User::all()->pluck('id')->toArray();// arma un array con todos los id usuarios posibles

        return [
            'currency' => $currency,// si es en pesos o no
            'transaction_limit' => $transaction_limit,//el limite que le corresponda 
            'balance' => 0,//cero por defecto
            'user_id' => fake()->randomElement($user_id),// elige un usuario id
            'cbu' => $this->faker->unique()->regexify('[0-9]{22}'),//un numero aleatorio de 20 cifras donde cada cifra puedas ser de 0 a 9
            'deleted' => false,// no borrada por defecto
        ];
    }
}
