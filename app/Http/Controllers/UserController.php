<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class UserController extends Controller
{   
    // Método index para listar los usuarios 
    public function index(Request $request)
    {   
        // Aca recuperamos el role_id
        $roleId = $request->user()->role_id;
        // Aca buscamos el registro en la tabla roles con ese id 
        $role = Role::find($roleId);
        // Aca buscamos el contenido de la columna name de ese registro
        $userRole = $role->name;

        if ($userRole == "ADMIN" ) {
            // Si el name de ese registro es admin devolvemos una lista de usuarios
            $users = User::where('deleted', false);
            
            // Si envía un número de página se devuelve paginado, sino se devuelve todo
            if ($request->has('page')) {
                $users = $users->simplePaginate(10);
            } else {
                $users = $users->get();
            }

            return response()->json(['users' => $users], 200);
        } else {
            // Sino devolvemos un mensaje de error
            return response()->json(['message' => 'No tienes permiso para realizar esta acción'], 403);
        }
    }


    public function delete(Request $request, $id)
    {
        // Verifica si el usuario tiene permisos de ADMIN
        if ($request->user()->role->name === 'ADMIN' || $request->user()->id == $id) {
            // Busca al usuario en la base de datos por su ID
            $user = User::find($id);

            if ($user) {
                // Actualiza la columna 'deleted' a 1 para realizar el borrado lógico
                $user->update(['deleted' => 1]);
                // Retorna una respuesta JSON indicando que el usuario fue dado de baja exitosamente
                return response()->json(['message' => 'Usuario dado de baja exitosamente']);
            } else {
                // Retorna una respuesta JSON indicando que el usuario no fue encontrado (código 404)
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }
        } else {
            // Retorna una respuesta JSON indicando que el usuario no tiene permisos para realizar la acción (código 403)
            return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], 403);
        }
    }

    public function userDetails()
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Devolver la información del usuario
        return response()->json(['user' => $user]);
    }

    public function updateUserProfile(Request $request)
    {
        // Obtener el usuario autenticado
        $authenticatedUser = $request->user();
    
        // Validar que solo el usuario autenticado puede realizar la actualización
        if ($authenticatedUser->id !== $request->user_id) {
            return response()->json(['message' => 'No tienes permiso para actualizar este perfil'], 403);
        }
    
        // Validar los campos que se pueden actualizar
        $request->validate([
            'name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'password' => 'string|min:6',
        ]);
    
        // Actualizar los campos permitidos
        $authenticatedUser->update([
            'name' => $request->input('name', $authenticatedUser->name),
            'last_name' => $request->input('last_name', $authenticatedUser->last_name),
            'password' => $request->has('password') ? bcrypt($request->input('password')) : $authenticatedUser->password,
        ]);
    
        // Retornar una respuesta JSON indicando que la actualización fue exitosa
        return response()->json(['message' => 'Datos de usuario actualizados correctamente']);
    }

}