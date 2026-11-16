<?php

namespace Database\Factories;

use App\Models\BarangJasa;
use App\Models\DetailPo;
use App\Models\Po;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DetailPo> */
class DetailPoFactory extends Factory
{
    protected $model = DetailPo::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $hargaSatuan = fake()->numberBetween(50, 500) * 1000;

        return [
            'idbarangjasa' => BarangJasa::factory(),
            'pono' => Po::factory(),
            'qty' => $qty,
            'amount' => $qty * $hargaSatuan,
            'nama_barang' => fake()->words(3, true),
            'unit' => 'pcs',
            'harga_satuan' => $hargaSatuan,
        ];
    }
}
