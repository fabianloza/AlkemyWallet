<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {    $role_id = Role::all()->pluck('id')->toArray();// define una lista de roles posibles
         $name = fake('es_ES')->firstName($gender = 'male'|'female');//define un nombre femenino o masculino
         $last_name = fake('es_ES')->lastName();//apellido
         $userName = strtolower($name . '.' . $last_name);
         $email = fake()->freeEmailDomain();
        return [
            'name' => $name,//nombre
            'last_name' => $last_name,//apellido
            'email' => $userName  . '@' . $email,//email
            'password' => Hash::make(fake()->password()),//contraseña
            'role_id' => fake()->randomElement($role_id),//elige un rol
            'deleted'=>0//un campo borrado por defecto en 0

        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
