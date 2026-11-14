<?php

namespace Database\Factories;

use App\Models\Petugas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Petugas>
 */
class PetugasFactory extends Factory
{
    public function definition(): array
    {
        return [
            'idpetugas' => fake()->unique()->bothify('PTG####'),
            'namapetugas' => fake()->name(),
            'notelp' => fake()->numerify('08##########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'admin_sales',
        ];
    }
}
