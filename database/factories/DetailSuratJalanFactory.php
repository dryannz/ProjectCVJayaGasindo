<?php

namespace Database\Factories;

use App\Models\DetailPo;
use App\Models\DetailSuratJalan;
use App\Models\SuratJalan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailSuratJalan>
 */
class DetailSuratJalanFactory extends Factory
{
    protected $model = DetailSuratJalan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nosuratjalan' => SuratJalan::factory(),
            'detail_po_id' => DetailPo::factory(),
            'nama_barang' => fake()->words(3, true),
            'qty' => fake()->numberBetween(1, 5),
            'unit' => 'pcs',
        ];
    }

    public function forSuratJalan(SuratJalan $suratJalan): static
    {
        return $this->state(fn (): array => [
            'nosuratjalan' => $suratJalan->nosuratjalan,
        ]);
    }

    public function forDetailPo(DetailPo $detailPo): static
    {
        return $this->state(fn (): array => [
            'detail_po_id' => $detailPo->id,
            'nama_barang' => $detailPo->nama_barang,
            'unit' => $detailPo->unit,
        ]);
    }
}
