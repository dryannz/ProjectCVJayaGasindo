<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\SuratJalan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SuratJalan>
 */
class SuratJalanFactory extends Factory
{
    protected $model = SuratJalan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nosuratjalan' => '11-'.fake()->unique()->numerify('###').'/MJGE-ABCD/SJ/XI/2026',
            'tgl' => now()->toDateString(),
            'idpetugas' => Petugas::factory(),
            'pono' => Po::factory()->diproses(),
            'idcust' => Customer::factory()->withKodeSingkat(),
            'attn' => fake()->name(),
            'rev' => 'Rev - 00',
            'mobil' => null,
            'jenis' => SuratJalan::JENIS_PENGIRIMAN,
            'status' => SuratJalan::STATUS_DRAFT,
            'catatan' => null,
            'nama_penerima' => null,
            'tanggal_diterima' => null,
            'alasan_batal' => null,
        ];
    }

    public function forPo(Po $po): static
    {
        return $this->state(fn (): array => [
            'pono' => $po->pono,
            'idcust' => $po->idcust,
        ]);
    }

    public function diterbitkan(): static
    {
        return $this->state(fn (): array => [
            'status' => SuratJalan::STATUS_DITERBITKAN,
        ]);
    }

    public function diterima(): static
    {
        return $this->state(fn (): array => [
            'status' => SuratJalan::STATUS_DITERIMA,
            'nama_penerima' => fake()->name(),
            'tanggal_diterima' => now()->toDateString(),
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn (): array => [
            'status' => SuratJalan::STATUS_DIBATALKAN,
            'alasan_batal' => 'Dibatalkan untuk pengujian.',
        ]);
    }
}
