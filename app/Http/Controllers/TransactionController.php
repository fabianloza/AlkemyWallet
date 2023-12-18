<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class TransactionController extends Controller
{
    public function deposit(Request $request)
    {
        // Se verifica que 'account_id' y 'amount' estén presentes en la solicitud y sean numéricos.
        // Además, se verifica que 'amount' sea mayor o igual a 0.
        $request->validate([
            'account_id' => 'required|numeric',
            'amount' => 'required|numeric|min:0',
        ]);

        // Intentar obtener la cuenta del usuario autenticado. 
        // Si no se encuentra, se lanza una excepción y se devuelve un error y un código de estado HTTP 422.
        try {
            $account = Account::findOrFail($request->input('account_id'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'La cuenta no existe.'], 422);
        }

        // Verificar que la cuenta obtenida pertenece al usuario logueado. 
        if ($account->user_id !== Auth::id()) {
            return response()->json(['error' => 'La cuenta no pertenece al usuario logueado.'], 422);
        }

        // Crear un nuevo registro en la tabla 'transactions'.
        $transaction = Transaction::create([
            'amount' => $request->input('amount'),
            'type' => 'DEPOSIT',
            'description' => 'Depósito en cuenta',
            'account_id' => $account->id,
            'transaction_date' => now(),
        ]);

        // Actualizar el balance de la cuenta sumándole el 'amount' proporcionado en la solicitud.
        $account->balance += $request->input('amount');
        $account->save();

        // Devolver el registro de la transacción creado y la cuenta con el balance actualizado en formato JSON.
        return response()->json(['transaction' => $transaction, 'account' => $account]);
    }
    public function sendMoney(Request $request)
    {
        // Obtener el usuario emisor desde el token
        $user = Auth::user();

        // Validar si el usuario tiene suficiente saldo y límite
        $account = Account::find($request->input('account_id'));

        if (!$account || $account->user_id !== $user->id) {
            return response()->json(['error' => 'La cuenta emisora no existe o no pertenece al usuario logueado.'], 422);
        }
    
        if ($account->balance < $request->input('amount')) {
            return response()->json(['error' => 'Saldo insuficiente'], 400);
        }
    
        if ($account->transaction_limit < $request->input('amount')) {
            return response()->json(['error' => 'Excede el límite permitido'], 400);
        }

        // Obtener el usuario receptor
        $recipient = Account::find($request->input('usuario_destino_id'));

        if (!$recipient) {
            return response()->json(['error' => 'Cuenta destino no encontrada'], 404);
        }
    
        // Validar si las monedas de las cuentas son iguales
        if ($account->currency !== $recipient->currency) {
            return response()->json(['error' => 'Las monedas de las cuentas no coinciden'], 400);
        }

        try {
            // Iniciar una transacción de base de datos
            DB::beginTransaction();

            // Crear transacción INCOME para el usuario receptor
            $incomeTransaction = new Transaction([
                'amount' => $request->input('amount'),
                'type' => 'INCOME',
                'description' => 'Transferencia recibida de ' . $user->name,
                'transaction_date' => now(),
            ]);

            // Relacionar la transacción con la cuenta del receptor
            $recipient->transactions()->save($incomeTransaction);

            // Crear transacción PAYMENT para el usuario emisor
            $paymentTransaction = new Transaction([
                'amount' => $request->input('amount'),
                'type' => 'PAYMENT',
                'description' => 'Transferencia enviada a ' . $recipient->user->name,
                'transaction_date' => now(),
            ]);

            // Relacionar la transacción con la cuenta del emisor
            $account->transactions()->save($paymentTransaction);

            // Actualizar el balance de las cuentas
            $account->update(['balance' => $account->balance - $request->input('amount')]);
            $recipient->update(['balance' => $recipient->balance + $request->input('amount')]);

            // Confirmar la transacción
            DB::commit();

            // Obtener el balance actualizado del usuario que envia dinero
            $updatedBalance = [
                'balance_actual' => $account->fresh()->balance,
            ];

            // Retornar una respuesta de éxito con los balances actualizados
            return response()->json(['message' => 'Transferencia exitosa', 'data' => [
                'transaccion_emisor' => $paymentTransaction,
                'transaccion_receptor' => $incomeTransaction,
                'balance' => $updatedBalance,
            ]]);

        } catch (\Exception $e) {
            // Si hay un error, revierte la transacción
            DB::rollBack();
            return response()->json(['error' => 'Error en la transacción: ' . $e->getMessage()], 500);
        }
    }
    public function index(Request $request)
    {
        $user = auth()->user();
    
        // Obtén el número de elementos por página (en este caso, 10 por página)
        $perPage = 10;
    
        // Obtén el número de la página actual del parámetro de la URL (?page=X)
        $currentPage = $request->query('page', 1);
    
        // Modifica la consulta para obtener solo las transacciones que pertenecen al usuario autenticado.
        $transactionsQuery = Transaction::with('account')
            ->whereHas('account', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
    
        // Utiliza el método simplePaginate para obtener la paginación de las transacciones.
        $transactions = $transactionsQuery->simplePaginate($perPage, ['*'], 'page', $currentPage);
    
        // Construye las URLs de las páginas anterior y siguiente
        $previousPageUrl = $transactions->previousPageUrl();
        $nextPageUrl = $transactions->nextPageUrl();
    
        $message = "Listado de transacciones de {$user->name} {$user->last_name}";
    
        // Retorna la respuesta JSON con la información paginada
        return response()->json([
            'message' => $message,
            'transactions' => $transactions,
            'previous_page_url' => $previousPageUrl,
            'next_page_url' => $nextPageUrl,
        ]);
    }
    public function updateDescription($id, Request $request)
    {
        // Validar que 'description' esté presente en la solicitud.
        // Si no se completa con un STRING devuelve un error.
        $request->validate([
            'description' => 'required|string',
        ]);

        // Obtener la transacción a actualizar.
        $transaction = Transaction::find($id);

        // Verificar si la transacción existe.
        if (!$transaction) {
            return response()->json(['error' => 'La transacción no existe.'], 404);
        }

        // Verificar si la transacción pertenece al usuario logueado.
        if ($transaction->account->user_id !== Auth::id()) {
            return response()->json(['error' => 'La transacción no pertenece al usuario logueado.'], 403);
        }

        // Actualizar la descripción de la transacción.
        $transaction->update([
            'description' => $request->input('description'),
        ]);

        // Devolver la transacción actualizada.
        return response()->json(['message' => 'Descripción de la transacción actualizada con éxito.', 'transaction' => $transaction]);
    }

    
    public function transactionDescription($transaction_id, Request $request)
    {
        // Buscar la transacción por el ID
        $transaction = Transaction::find($transaction_id);
    
        // Verificar si la transacción no existe
        if (!$transaction) {
            // Devolver respuesta con mensaje de error en español y código de estado 404 (Not Found)
            return response()->json(['error' => 'La transacción no existe.'], 404);
        }
    
        // Verificar si la transacción pertenece al usuario logueado
        if ($transaction->account->user_id !== Auth::id()) {
            // Devolver respuesta con mensaje de error y código de estado 403 (Forbidden)
            return response()->json(['error' => 'La transacción no pertenece al usuario logueado.'], 403);
        }
    
        // Construir la respuesta exitosa con el mensaje y la información de la transacción
        $response = [
            'message' => 'Descripción actualizada exitosamente',
            'data' => [$transaction],
        ];
    
        // Devolver respuesta exitosa y código de estado 201 (Created)
        return response()->json($response, 201);
    }
}