<?php

namespace App\Support;

use App\Models\NomorDokumen;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentNumberService
{
    /**
     * Buat nomor dokumen berurutan per jenis per periode (bulan berjalan).
     * Transaksi + lockForUpdate agar aman terhadap permintaan bersamaan.
     * Format dari config/dokumen.php, misal "QT/{tahun}/{bulan}/{urut4}".
     */
    public static function generate(string $jenis): string
    {
        return DB::transaction(function () use ($jenis): string {
            $periode = now()->format('Y-m');

            /** @var NomorDokumen|null $counter */
            $counter = NomorDokumen::query()
                ->where('jenis', $jenis)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->first();

            if ($counter === null) {
                $counter = NomorDokumen::query()->create([
                    'jenis' => $jenis,
                    'periode' => $periode,
                    'terakhir' => 0,
                ]);
            }

            $counter->terakhir += 1;
            $counter->save();

            return self::render($jenis, $counter->terakhir);
        });
    }

    public static function render(string $jenis, int $urut): string
    {
        $format = (string) config("dokumen.jenis.{$jenis}.format");

        if ($format === '') {
            throw new RuntimeException("Format nomor dokumen untuk jenis [{$jenis}] belum dikonfigurasi.");
        }

        return str_replace(
            ['{tahun}', '{bulan}', '{urut4}'],
            [now()->format('Y'), now()->format('m'), str_pad((string) $urut, 4, '0', STR_PAD_LEFT)],
            $format,
        );
    }
}
