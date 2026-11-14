<?php

namespace Database\Seeders;

use App\Models\Petugas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetugasSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Password sementara PTG001: AdminSales123!
        // Password sementara PTG002: Teknisi123!
        // Password sementara PTG003: Pimpinan123!
        $petugas = [
            [
                'idpetugas' => 'PTG001',
                'namapetugas' => 'Admin Sales MJG',
                'notelp' => '081200000001',
                'email' => 'admin.sales@jayagasindo.test',
                'password' => 'AdminSales123!',
                'role' => 'admin_sales',
            ],
            [
                'idpetugas' => 'PTG002',
                'namapetugas' => 'Teknisi MJG',
                'notelp' => '081200000002',
                'email' => 'teknisi@jayagasindo.test',
                'password' => 'Teknisi123!',
                'role' => 'teknisi',
            ],
            [
                'idpetugas' => 'PTG003',
                'namapetugas' => 'Pimpinan MJG',
                'notelp' => '081200000003',
                'email' => 'pimpinan@jayagasindo.test',
                'password' => 'Pimpinan123!',
                'role' => 'pimpinan',
            ],
        ];

        foreach ($petugas as $data) {
            Petugas::updateOrCreate(
                ['idpetugas' => $data['idpetugas']],
                $data,
            );
        }
    }
}
