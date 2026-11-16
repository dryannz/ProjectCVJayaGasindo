<?php

namespace App\Support;

use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\HargaKhusus;
use App\Models\Lead;

class QuotationService
{
    /**
     * Harga awal per mode_harga:
     * fix -> price produk; rfq -> kosong (wajib diisi admin);
     * khusus -> harga_khusus customer bila ada, jika tidak kosong.
     *
     * @return array{harga_satuan: int|null, sumber: string}
     */
    public static function hargaAwal(BarangJasa $produk, ?string $idcust): array
    {
        if ($produk->mode_harga === 'rfq') {
            return ['harga_satuan' => null, 'sumber' => 'rfq'];
        }

        if ($produk->mode_harga === 'khusus' && $idcust !== null) {
            $hargaKhusus = HargaKhusus::query()
                ->where('idcust', $idcust)
                ->where('idbarangjasa', $produk->idbarangjasa)
                ->first();

            if ($hargaKhusus !== null) {
                return ['harga_satuan' => (int) $hargaKhusus->price, 'sumber' => 'khusus'];
            }

            return ['harga_satuan' => null, 'sumber' => 'khusus-kosong'];
        }

        return ['harga_satuan' => (int) round((float) $produk->price), 'sumber' => 'fix'];
    }

    /** Cari customer existing yang cocok dengan data lead (nama perusahaan atau nomor telepon). */
    public static function cariCustomerDariLead(Lead $lead): ?Customer
    {
        return Customer::query()
            ->where('nama_pt', $lead->perusahaan)
            ->orWhere('notelp_pt', $lead->telepon)
            ->first();
    }

    /** Data customer baru dari data lead. */
    public static function dataCustomerDariLead(Lead $lead): array
    {
        return [
            'nama_pt' => $lead->perusahaan !== null && $lead->perusahaan !== '' ? $lead->perusahaan : $lead->nama,
            'notelp_pt' => $lead->telepon,
            'alamat_pt' => $lead->alamat !== null && $lead->alamat !== '' ? $lead->alamat : '-',
            'pic' => $lead->nama,
        ];
    }
}
