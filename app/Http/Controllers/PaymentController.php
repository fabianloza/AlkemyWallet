<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Account;

class PaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'amount' => 'required|numeric|min:0.01', // se debe tener saldo en la cuenta para hacer pagos
            'account_id' => 'required|exists:accounts,id',
        ]);

        // Obtener la cuenta
        $account = Account::findOrFail($request->input('account_id'));

        // Validar que la cuenta pertenece al usuario autenticado
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['error' => 'La cuenta no pertenece al usuario autenticado.'], 422);
        }

        // Validar que la cuenta tiene saldo suficiente
        if ($account->balance < $request->amount) {
            return response()->json(['error' => 'Saldo insuficiente para realizar el pago.'], 400);
        }

        // Crear un registro en la tabla transactions
        $transaction = Transaction::create([
            'amount' => $request->amount,
            'type' => 'PAYMENT',
            'description' => 'Pago realizado',
            'account_id' => $account->id,
            'transaction_date' => now(),
        ]);

        // Actualizar el balance de la cuenta
        $account->balance -= $request->amount;
        $account->save();

        // Devolver la respuesta con el registro generado y la cuenta afectada, excluyendo ciertos campos
        return response()->json([
            'transaction' => $transaction->only(['amount', 'type', 'description', 'transaction_date', 'id']),
            'account' => $account->makeHidden(['created_at', 'updated_at', 'deleted']),
        ], 200); // Código de respuesta HTTP 200 OK
    }
}