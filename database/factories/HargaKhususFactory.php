<?php

namespace Database\Factories;

use App\Models\HargaKhusus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HargaKhusus>
 */
class HargaKhususFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idcust' => 'CUST-0001',
            'idbarangjasa' => 'BRG-0001',
            'price' => 100000,
        ];
    }
}
