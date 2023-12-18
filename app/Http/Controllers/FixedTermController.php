<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Http\UserBalanceDTO;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use App\Models\FixedTerm;
use Illuminate\Validation\Rule;

class FixedTermController extends Controller
{
    public function create(Request $req)
{
    // Obtención del usuario autenticado
    $user = Auth::user();

    // Buscamos la cuenta en pesos que pertenece al usuario
    $account = Account::where('id', $req->account_id)
        ->where('user_id', $user->id)
        ->where('currency', 'ARS')
        ->where('deleted', 0)
        ->first();

    // Validación de la solicitud
    $req->validate([
        'account_id' => Rule::exists('accounts', 'id')
            ->where('user_id', $user->id)
            ->where('currency', 'ARS')
            ->where('deleted', false),
        'amount' => "numeric|gte:1000|lte:{$account->transaction_limit}",
        'duration' => 'numeric|gte:30',
    ]);

    // Verificación del dinero en cuenta, si no hay suficiente, retorna un mensaje
    if ($account->balance < $req->amount) {
        return response()->json(['error' => 'No dispone del dinero suficiente para realizar esta operación'], 422);
    }

    // Obtención de interés diario vía variable de entorno
    $fixedTermInterest = env('FIXED_TERM_INTEREST');

    // Calculamos el total del plazo fijo
    $fixedTermTotal = $req->amount + ((($req->amount * $fixedTermInterest) / 100) * $req->duration);

    // Creación del nuevo plazo fijo
    $fixedTerm = new FixedTerm([
        'amount' => $req->amount,
        'duration' => $req->duration,
        'account_id' => $account->id, // Utilizamos el ID de la cuenta obtenido
        'interest' => $fixedTermInterest,
        'total' => $fixedTermTotal,
        'closed_at' => Carbon::now()->addDays(intval($req->duration)),
    ]);

    // Se actualiza el balance de la cuenta
    $account->balance -= $req->amount;

    // Guardado y actualización de la cuenta con el plazo fijo
    $fixedTerm->save();
    $account->save();

    // Carga la cuenta para devolverla en el JSON de la respuesta
    $fixedTerm->load('account');

    // Retorna una respuesta de creación exitosa
    return response()->json(['message' => 'Su plazo fijo ha sido creado exitosamente', 'fixed_term' => $fixedTerm], 201);
}
    public function simulate(Request $req)
    {
        $user = Auth::user(); //usuario autenticado
        $account = Account::where('id', $req->account_id)
         ->where('user_id', $user->id)
        ->where('currency', 'ARS')
        ->where('deleted', 0)
        ->first();
      
        $req->validate([ // valido que el dinero a meter en el plazo fijo sea mayor o igual a 1000, que la duración de este sea mayor o igual a 30 dias y que el id de la cuenta pertenezca al usuario        
            'account_id' => Rule::exists('accounts', 'id')->where('user_id', $user->id)->where('currency', 'ARS')->where('deleted', false),
            'amount' => "numeric|gte:1000|lte:{$account->transaction_limit}",
            'duration' => 'numeric|gte:30',
        ]);

        $enoughMoney = $account->balance >= $req->amount;

        if (!$enoughMoney) { // devuelvo un código de error 422 con un mensaje indicando que no tiene suficiente dinero para crear el plazo fijo
            return response()->unprocessableContent([], 'You do not have enough money in your account');
        }

        $fixedTermInterest = env('FIXED_TERM_INTEREST');

        $fixedTermTotal = $req->amount + ((($req->amount * $fixedTermInterest) / 100) * $req->duration); // sumo el dinero más lo que se tiene que agregar por dia multiplicado a la duración
        $created_at = now(); // la fecha de creación en formato UTC (como se guarda en la base de datos)
        $closed_at = Carbon::parse($created_at)->addDays(intval($req->duration)); // calculo la fecha de cierre
        $amount = $req->amount; // el monto que quiere poner en el plazo fijo el usuario

        return response()->created([
            'message' => 'Fixed term simulation successfully made',
            'fixed_term' => [
                'creation_date' => $created_at,
                'finalization_date' => $closed_at,
                'amount' => $amount,
                'total_interest' => $fixedTermTotal - $amount,
                'total_amount' => $fixedTermTotal,
            ]
        ]);
    }
}
