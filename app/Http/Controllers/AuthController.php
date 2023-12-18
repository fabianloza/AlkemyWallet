<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Registro de usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validar los datos del formulario
        $validator = validator($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Comprobar si la validación falla y devolver una respuesta de error
        if ($validator->fails()) {
            return response()->badRequest();
        }

        // Hashear la contraseña antes de almacenarla
        $request['password'] = Hash::make($request['password']);

        // Verificar si el rol 'USER' existe
        $userRole = Role::where('name', 'USER')->first();
        if (!$userRole) {
            // Crear el rol 'USER' si no existe
            $userRole = Role::create(['name' => 'USER']);
        }

        // Crear un nuevo usuario con el rol de usuario regular (USER)
        $usuario = User::create(array_merge($request->toArray(), ['role_id' => $userRole->id]));

        // Validar que el CBU generado aleatoriamente no existe en la tabla accounts
        do {
            $cbuArs = $this->generarCbuAleatorio();
            $cbuUsd = $this->generarCbuAleatorio();
        } while (Account::where('cbu', $cbuArs)->exists() || Account::where('cbu', $cbuUsd)->exists());

        // Crear cuenta en pesos argentinos asociada al usuario
        Account::create([
            'currency' => 'ARS',
            'transaction_limit' => 300000,
            'balance' => 0,
            'user_id' => $usuario->id,
            'cbu' => $cbuArs,
            'deleted' => false,
        ]);

        // Crear cuenta en dólares asociada al usuario
        Account::create([
            'currency' => 'USD',
            'transaction_limit' => 1000,
            'balance' => 0,
            'user_id' => $usuario->id,
            'cbu' => $cbuUsd,
            'deleted' => false,
        ]);

        // Generar un token de acceso para el usuario recién registrado
        $token = $usuario->createToken('token')->accessToken;

        // Devolver una respuesta de éxito con el token y la información del usuario
        return response()->created(['token' => $token, 'usuario' => $usuario]);
    }

    // Función para generar número de CBU aleatorio
    private function generarCbuAleatorio()
    {
        return substr(str_shuffle(str_repeat('0123456789', 3)), 0, 22);
    }


    public function login(Request $request)
    {
        //busca en la base de datos si el email ingresado ya existe
        $user = User::where('email', $request->input('email'))->first();
        
        //valida que el email y contraseña sean requeridos
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        //si pasa las validaciones y el usuario esta autenticado 
        if (Auth::attempt($credentials)) {
            $user = User::find(Auth::user()->id); //trae al usuario por id 
            $token = $user->createToken('token')->accessToken; //muestra el token de acceso

            return response()->ok(['token' => $token, 'user' => $user]); //respuesta mostrando el token y el usuario
        }
        return response()->json(['error' => 'Usuario o contraseña inválido'], 404); //manejo de errores si el usuario no se encuentra
    }
}

