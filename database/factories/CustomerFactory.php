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
            'idcust' => 'CUST-'.fake()->unique()->numerify('####'),
            'notelp_pt' => fake()->numerify('08##########'),
            'nama_pt' => fake()->company(),
            'kode_singkat' => null,
            'alamat_pt' => fake()->address(),
            'pic' => fake()->name(),
        ];
    }

    public function withKodeSingkat(string $kodeSingkat = 'PPKS'): static
    {
        return $this->state(fn (): array => [
            'kode_singkat' => strtoupper($kodeSingkat),
        ]);
    }
}
