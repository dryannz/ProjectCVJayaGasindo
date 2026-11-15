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

    public function fisik(): static
    {
        return $this->state(fn (array $attributes): array => [
            'tipe' => 'fisik',
            'stok' => $attributes['stok'] ?? 10,
        ]);
    }

    public function jasa(): static
    {
        return $this->state(fn (): array => [
            'tipe' => 'jasa',
            'stok' => null,
        ]);
    }

    public function fix(?float $price = 150000): static
    {
        return $this->state(fn (): array => [
            'mode_harga' => 'fix',
            'price' => $price,
        ]);
    }

    public function rfq(): static
    {
        return $this->state(fn (): array => [
            'mode_harga' => 'rfq',
            'price' => null,
        ]);
    }

    public function khusus(?float $price = 200000): static
    {
        return $this->state(fn (): array => [
            'mode_harga' => 'khusus',
            'price' => $price,
        ]);
    }

    public function aktif(): static
    {
        return $this->state(fn (): array => [
            'is_active' => true,
        ]);
    }

    public function nonaktif(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function habis(): static
    {
        return $this->state(fn (): array => [
            'tipe' => 'fisik',
            'stok' => 0,
        ]);
    }
}
