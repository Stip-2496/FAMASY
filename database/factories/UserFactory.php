<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Rol;
use App\Models\Contacto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $contacto = Contacto::factory()->create(); // crea contacto y devolver idCon
        // crea rol
        $rol = Rol::factory()->create();

        return [
            // campos según tu tabla users
            'idRolUsu' => $rol->idRol,
            'idConUsu' => $contacto->idCon,
            'tipDocUsu' => 'CC',
            'numDocUsu' => $this->faker->unique()->numerify('#########'),
            'nomUsu' => $this->faker->firstName(),
            'apeUsu' => $this->faker->lastName(),
            'fecNacUsu' => $this->faker->date(),
            'sexUsu' => $this->faker->randomElement(['Hombre','Mujer']),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // password
            'remember_token' => Str::random(10),
        ];
    }
}
