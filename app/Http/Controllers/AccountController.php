<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Http\UserBalanceDTO;
use App\Models\User;

class AccountController extends Controller
{

    public function index()
    {
        // Query para traer todas las cuentas que no esten eliminadas y paginar los resultados
        $accounts = Account::where('deleted', false)->simplePaginate(10);
    
        // Respuesta JSON con las cuentas encontradas
        return response()->ok(['accounts' => $accounts]);
    }
    
    public function account($user_id)
    {
        // Query para traer las cuentas del usuario por su ID y que no estén eliminadas y paginar los resultados
        $accounts = Account::where('user_id', $user_id)->where('deleted', false)->simplePaginate(10);
    
        // Respuesta JSON con las cuentas encontradas
        return response()->ok(['accounts' => $accounts]);
    }

    public function createAccount(Request $request)
    {
        // Validación de la solicitud
        // Asegura que se ha proporcionado una moneda y que es una de las opciones permitidas ('ARS' o 'USD').
        $request->validate([
            'currency' => 'required|in:ARS,USD',
        ]);

        // Crear una nueva cuenta
        $account = new Account();
        $account->user_id = Auth::id(); // Asociar al usuario autenticado
        $account->currency = $request->input('currency'); // Trae los datos del request
        $account->balance = 0; // Establece el balance inicial en cero
        $account->transaction_limit = ($request->input('currency') == 'ARS') ? 300000 : 1000; // Limite de transaccion
        $account->cbu = $this->generarCbuAleatorio(); // Generar CBU aleatorio

        $account->save();

        // Devuelve una respuesta JSON con los datos de la cuenta creada y una respuesta HTTP 201.
        return response()->json($account, 201);
    }

    // Utiliza la función str_shuffle para mezclar una cadena de números y luego selecciona los primeros 22 caracteres.
    private function generarCbuAleatorio()
    {
        return substr(str_shuffle(str_repeat('0123456789', 3)), 0, 22);
    }

    
    public function balance()
    {
        $userId = Auth::id();
        $user = User::find($userId);
    
        if (!$user) {
            // Manejar el caso en que el usuario no se encuentra
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    
        // Obtener las cuentas del usuario
        $accounts = $user->account;
    
        // Verificar si hay cuentas antes de intentar iterar
        if ($accounts) {
            $arsBalance = 0;
            $usdBalance = 0;
    
            // Calcular el balance para cuentas ARS y USD
            foreach ($accounts as $account) {
                if ($account->currency === 'ARS') {
                    $arsBalance += $account->balance;
                } elseif ($account->currency === 'USD') {
                    $usdBalance += $account->balance;
                }
            }
        } else {
            // Si no hay cuentas, inicializar los saldos en cero
            $arsBalance = 0;
            $usdBalance = 0;
        }
    
        $balanceData = [
            'ARS accounts balance' => $arsBalance,
            'USD accounts balance' => $usdBalance,
        ];
    
        // Crear una instancia de UserBalanceDTO y pasar el balance
        $userBalanceDTO = new UserBalanceDTO($balanceData);
    
        // Obtener los datos de balance del DTO
        $userData = $userBalanceDTO->toArray();
        // Devuelve una respuesta con el nombre y apellido de quien lo consulta
        return response()->json(["Balance de la cuenta de {$user->name} {$user->last_name}" => $userData]);
    }

    public function editAccount($id, Request $request)
    {
        // Validar que 'transaction_limit' esté presente en la solicitud.
        // Si no se completa con un NUMBER devuelve un error.
        $request->validate([
            'transaction_limit' => 'required|numeric',
        ]);

        // Obtener la cuenta a editar.
        $account = Account::find($id);

        // Verificar si la cuenta existe.
        if (!$account) {
            return response()->json(['error' => 'La cuenta no existe.'], 404);
        }

        // Verificar si la cuenta pertenece al usuario logueado.
        if ($account->user_id !== Auth::id()) {
            return response()->json(['error' => 'La cuenta no pertenece al usuario logueado.'], 403);
        }

        // Actualizar el tope de transferencia de la cuenta.
        $account->update([
            'transaction_limit' => $request->input('transaction_limit'),
        ]);

        // Devolver la cuenta actualizada.
        return response()->json(['message' => 'Cuenta actualizada con éxito.', 'account' => $account]);
    }
}
