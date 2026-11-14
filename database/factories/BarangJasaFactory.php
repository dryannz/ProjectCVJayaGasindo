<?php

namespace Database\Factories;

use App\Models\BarangJasa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BarangJasa>
 */
class BarangJasaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'idbarangjasa' => fake()->unique()->bothify('BRG-####'),
            'kategori_id' => null,
            'tipe' => 'fisik',
            'nama_barang' => fake()->words(3, true),
            'deskripsi' => fake()->sentence(),
            'ukuran' => '1/2 inch',
            'unit' => 'pcs',
            'mode_harga' => 'fix',
            'price' => 125000,
            'stok' => 10,
            'gambar' => null,
            'is_active' => true,
        ];
    }
}
