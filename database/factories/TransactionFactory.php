<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition(): array  // Definicion de estructura del modelo Transaction
    {
        $currencies = Account::all()->pluck('currency')->toArray(); // Conversion a array de los 'currency' de las cuentas
        $currency = fake()->randomElement($currencies); // Toma una 'currency' aleatoria

        $amountRange = ($currency === 'ARS') ? [0, 300000] : [0, 1000]; // Genera un monto aleatorio basado en los rangos establecidos
        $amount = fake()->randomFloat(2, ...$amountRange);

        $accountIds = Account::all()->pluck('id')->toArray(); // Conversion a array de los id de las cuentas
        $accountId = fake()->randomElement($accountIds); // Toma un id aleatorio

        $typeArray = ['INCOME', 'PAYMENT', 'DEPOSIT']; // Tipos de transacciones validas
        $type = fake()->randomElement($typeArray); // Toma un elemento aleatorio del array de transacciones validas

        switch ($type) { // Segun el resultado obtenido, selecciona el array correspondiente a dicho resultado
            case 'INCOME':
                $descriptionArray = ['Cobro de salario', 'Transferencia recibida', 'Acreditación de plazo fijo', 'Honorarios'];
                break;
            case 'PAYMENT':
                $descriptionArray = ['Pago con QR', 'Pago de Servicios Digitales', 'Transferencia enviada', 'Debito automatico'];
                break;
            case 'DEPOSIT':
                $descriptionArray = ['Recarga DEBIN', 'Deposito a CVU', 'Deposito por cajero bancario'];
                break;
            default:
                $descriptionArray = ['Varios'];
                break;
        }

        $description = fake()->randomElement($descriptionArray); // Toma aleatoriamente algun elemento de los arrays de descripcion de transacciones

        return [ // Retorna el array correspondiente a los atributos de la transaccion con los datos generados
            'amount' => $amount,
            'description' => $description,
            'type' => $type,
            'account_id' => $accountId,
        ];
    }
}
