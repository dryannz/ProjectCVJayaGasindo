<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idcust' => fake()->unique()->bothify('CUST-####'),
            'notelp_pt' => fake()->numerify('08##########'),
            'nama_pt' => fake()->company(),
            'alamat_pt' => fake()->address(),
            'pic' => fake()->name(),
        ];
    }
}
