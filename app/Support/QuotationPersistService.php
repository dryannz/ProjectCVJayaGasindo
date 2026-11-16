<?php

namespace App\Support;

class QuotationPersistService
{
    /**
     * Hitung total di SERVER dari baris item. Angka total dari browser tidak dipercaya.
     *
     * @param  array<int, array{nama_barang: string, tipe: string, ukuran: ?string, unit: ?string, qty: int, harga_satuan: int, idbarangjasa: ?string}>  $items
     * @return array{subtotal: int, diskon: int, ppn: int, total: int}
     */
    public static function hitungTotal(array $items, int $diskon, float $ppnPersen): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += max(0, $item['qty']) * max(0, $item['harga_satuan']);
        }

        $diskon = min(max(0, $diskon), $subtotal);
        $dasarPajak = $subtotal - $diskon;
        $ppn = (int) round($dasarPajak * (max(0, $ppnPersen) / 100));

        return [
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'total' => $dasarPajak + $ppn,
        ];
    }

    /**
     * Baris item mentah dari form menjadi data siap simpan.
     *
     * @param  array<int, mixed>  $rawItems
     * @return array<int, array{nama_barang: string, tipe: string, ukuran: ?string, unit: ?string, qty: int, harga_satuan: int, idbarangjasa: ?string}>
     */
    public static function normalisasiItems(array $rawItems): array
    {
        $items = [];

        foreach ($rawItems as $raw) {
            $idbarangjasa = isset($raw['idbarangjasa']) && $raw['idbarangjasa'] !== ''
                ? (string) $raw['idbarangjasa']
                : null;
            $qty = (int) ($raw['qty'] ?? 0);
            $harga = (int) round((float) ($raw['harga_satuan'] ?? 0));
            $nama = trim((string) ($raw['nama_barang'] ?? ''));

            if ($nama === '' || $qty < 1) {
                continue;
            }

            $items[] = [
                'idbarangjasa' => $idbarangjasa,
                'nama_barang' => $nama,
                'tipe' => in_array(($raw['tipe'] ?? ''), ['fisik', 'jasa'], true) ? $raw['tipe'] : 'fisik',
                'ukuran' => isset($raw['ukuran']) && $raw['ukuran'] !== '' ? (string) $raw['ukuran'] : null,
                'unit' => isset($raw['unit']) && $raw['unit'] !== '' ? (string) $raw['unit'] : null,
                'qty' => $qty,
                'harga_satuan' => max(0, $harga),
            ];
        }

        return $items;
    }
}
