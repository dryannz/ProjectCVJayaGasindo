<?php

namespace App\Support;

use App\Models\NomorDokumen;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class DocumentNumberService
{
    /**
     * Buat nomor dokumen berurutan per jenis per periode (bulan berjalan).
     * Transaksi + lockForUpdate agar aman terhadap permintaan bersamaan.
     * Format dari config/dokumen.php, misal "QT/{tahun}/{bulan}/{urut4}".
     */
    public static function generate(string $jenis, ?string $kodeSingkat = null): string
    {
        self::assertCustomerCodeIfRequired($jenis, $kodeSingkat);

        return DB::transaction(function () use ($jenis, $kodeSingkat): string {
            $periode = self::resolvePeriod();

            DB::table('nomor_dokumen')->insertOrIgnore([
                'jenis' => $jenis,
                'periode' => $periode,
                'terakhir' => 0,
            ]);

            /** @var NomorDokumen|null $counter */
            $counter = NomorDokumen::query()
                ->where('jenis', $jenis)
                ->where('periode', $periode)
                ->lockForUpdate()
                ->first();

            if ($counter === null) {
                throw new RuntimeException('Counter nomor dokumen tidak dapat dikunci.');
            }

            $counter->terakhir += 1;
            $counter->save();

            return self::render($jenis, $counter->terakhir, $kodeSingkat);
        });
    }

    public static function render(string $jenis, int $urut, ?string $kodeSingkat = null): string
    {
        $format = (string) config("dokumen.jenis.{$jenis}.format");

        if ($format === '') {
            throw new RuntimeException("Format nomor dokumen untuk jenis [{$jenis}] belum dikonfigurasi.");
        }

        self::assertCustomerCodeIfRequired($jenis, $kodeSingkat);

        return str_replace(
            ['{tahun}', '{bulan}', '{romawi}', '{urut4}', '{urut3}', '{kode}', '{singkatan}'],
            [
                now()->format('Y'),
                now()->format('m'),
                self::romanMonth((int) now()->format('n')),
                str_pad((string) $urut, 4, '0', STR_PAD_LEFT),
                str_pad((string) $urut, 3, '0', STR_PAD_LEFT),
                strtoupper((string) $kodeSingkat),
                (string) config('perusahaan.singkatan', 'MJGE'),
            ],
            $format,
        );
    }

    public static function romanMonth(int $month): string
    {
        $months = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        if (! array_key_exists($month, $months)) {
            throw new InvalidArgumentException('Bulan harus berada di antara 1 dan 12.');
        }

        return $months[$month];
    }

    private static function resolvePeriod(): string
    {
        return config('dokumen.periode', 'bulanan') === 'tahunan'
            ? now()->format('Y')
            : now()->format('Y-m');
    }

    private static function assertCustomerCodeIfRequired(string $jenis, ?string $kodeSingkat): void
    {
        if (! in_array($jenis, [NomorDokumen::JENIS_WORK_ORDER, NomorDokumen::JENIS_SURAT_JALAN], true)) {
            return;
        }

        if (! is_string($kodeSingkat) || ! preg_match('/^[A-Z0-9]{2,10}$/', $kodeSingkat)) {
            throw new InvalidArgumentException('Kode singkat customer wajib berupa 2 sampai 10 huruf besar atau angka.');
        }
    }
}
