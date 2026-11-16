<?php

namespace App\Support;

use App\Models\DetailQuotation;
use App\Models\Lead;
use App\Models\NomorDokumen;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class QuotationStatusService
{
    /**
     * Simpan quotation + detail dalam satu transaksi, lalu tandai lead menjadi quotation.
     *
     * @param  array<int, array{nama_barang: string, tipe: string, ukuran: ?string, unit: ?string, qty: int, harga_satuan: int, idbarangjasa: ?string}>  $items
     */
    public static function simpan(array $data, array $items, ?Lead $lead): Quotation
    {
        $totals = QuotationPersistService::hitungTotal(
            $items,
            (int) ($data['diskon'] ?? 0),
            (float) ($data['ppn_persen'] ?? 0),
        );

        return DB::transaction(function () use ($data, $items, $totals, $lead): Quotation {
            $quotation = Quotation::create([
                'nomor_quotation' => DocumentNumberService::generate(NomorDokumen::JENIS_QUOTATION),
                'lead_id' => $lead?->id,
                'idcust' => $data['idcust'],
                'idpetugas' => $data['idpetugas'],
                'tanggal' => $data['tanggal'],
                'berlaku_sampai' => $data['berlaku_sampai'] ?? null,
                'status' => Quotation::STATUS_DRAFT,
                'catatan' => $data['catatan'] ?? null,
                'syarat' => $data['syarat'] ?? null,
                ...$totals,
            ]);

            foreach ($items as $item) {
                $quotation->details()->create([
                    ...$item,
                    'jumlah' => $item['qty'] * $item['harga_satuan'],
                ]);
            }

            if ($lead !== null && $lead->status !== Lead::STATUS_QUOTATION) {
                $lead->update(['status' => Lead::STATUS_QUOTATION]);
            }

            return $quotation;
        });
    }

    /** Ganti seluruh detail quotation pada saat masih draft. */
    public static function perbaruiItems(Quotation $quotation, array $items, int $diskon, float $ppnPersen): void
    {
        $totals = QuotationPersistService::hitungTotal($items, $diskon, $ppnPersen);

        DB::transaction(function () use ($quotation, $items, $totals): void {
            DetailQuotation::query()->where('quotation_id', $quotation->id)->delete();

            foreach ($items as $item) {
                $quotation->details()->create([
                    ...$item,
                    'jumlah' => $item['qty'] * $item['harga_satuan'],
                ]);
            }

            $quotation->update($totals);
        });
    }
}
