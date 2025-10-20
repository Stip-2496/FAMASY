<?php

namespace Database\Factories;

use App\Models\Contacto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactoFactory extends Factory
{
    protected $model = Contacto::class;

    public function definition()
    {
        return [
            'celCon' => $this->faker->numerify('3217445269'), // ejemplo celular colombiano, no es real. Ok
        ];
    }
}