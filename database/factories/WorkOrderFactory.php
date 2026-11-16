<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'nomor_wo' => 'WO/'.fake()->unique()->numerify('####'),
            'pono' => Po::factory()->diproses(),
            'idcust' => Customer::factory()->withKodeSingkat(),
            'tanggal_terbit' => now()->toDateString(),
            'lokasi_pekerjaan' => fake()->address(),
            'uraian_pekerjaan' => fake()->sentence(),
            'keluhan_tangki' => null,
            'idpetugas' => null,
            'status' => WorkOrder::STATUS_MENUNGGU,
            'tanggal_mulai' => null,
            'tanggal_selesai' => null,
            'tanggal_bast' => null,
            'catatan_teknisi' => null,
            'catatan_admin' => null,
            'alasan_batal' => null,
        ];
    }

    public function ditugaskan(?Petugas $petugas = null): static
    {
        return $this->state(fn (): array => [
            'status' => WorkOrder::STATUS_DITUGASKAN,
            'idpetugas' => $petugas?->idpetugas ?? Petugas::factory()->teknisi(),
        ]);
    }
}
