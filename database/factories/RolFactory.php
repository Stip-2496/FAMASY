<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition()
    {
        return [
            // ajusta a tus columnas reales
            'nomRol' => $this->faker->randomElement(['admin','usuario','apprentice']),
            'desRol' => $this->faker->sentence(),
            'estRol' => 'activo',
        ];
    }
}
