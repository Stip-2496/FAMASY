<?php

namespace Database\Factories;

use App\Models\Direccion;
use App\Models\Contacto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DireccionFactory extends Factory
{
    protected $model = Direccion::class;

    public function definition()
    {
        // Necesita el contacto (idConDir) en la tabla direccion
        return [
            'idConDir' => Contacto::factory(),
            'calDir' => $this->faker->streetName(),
            'barDir' => $this->faker->streetSuffix(),
            'ciuDir' => $this->faker->city(),
            'depDir' => $this->faker->state(),
            'codPosDir' => $this->faker->postcode(),
            'paiDir' => $this->faker->country(),
        ];
    }
}