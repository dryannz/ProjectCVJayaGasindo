<?php

namespace Database\Factories;

use App\Models\BarangJasa;
use App\Models\Petugas;
use App\Models\StokMutasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StokMutasi> */
class StokMutasiFactory extends Factory
{
    protected $model = StokMutasi::class;

    public function definition(): array
    {
        return [
            'idbarangjasa' => BarangJasa::factory(),
            'jenis' => StokMutasi::JENIS_MASUK,
            'qty' => 1,
            'stok_sebelum' => 0,
            'stok_sesudah' => 1,
            'referensi_tipe' => 'manual',
            'referensi_id' => null,
            'keterangan' => 'Penyesuaian awal',
            'idpetugas' => Petugas::factory(),
        ];
    }
}
