<?php

namespace Database\Factories;

use App\Models\DetailQuotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailQuotation>
 */
class DetailQuotationFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 10);
        $harga = fake()->numberBetween(50, 500) * 1000;

        return [
            'quotation_id' => null,
            'idbarangjasa' => null,
            'nama_barang' => fake()->words(3, true),
            'tipe' => 'fisik',
            'ukuran' => '1/2 inch',
            'unit' => 'pcs',
            'qty' => $qty,
            'harga_satuan' => $harga,
            'jumlah' => $qty * $harga,
        ];
    }
}
