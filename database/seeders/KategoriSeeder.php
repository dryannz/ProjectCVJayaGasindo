<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Kategori 1', 'Kategori 2'] as $urutan => $nama) {
            Kategori::updateOrCreate(
                ['slug' => str($nama)->slug()],
                [
                    'nama' => $nama,
                    'urutan' => $urutan,
                ],
            );
        }
    }
}
