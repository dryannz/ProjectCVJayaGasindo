<?php

namespace App\Support;

class WhatsApp
{
    /** Ubah nomor lokal (08xx) atau internasional menjadi format wa.me tanpa tanda baca. */
    public static function normalisasi(?string $nomor): string
    {
        $digit = preg_replace('/\D/', '', (string) $nomor);

        if ($digit === '') {
            return '';
        }

        if (str_starts_with($digit, '0')) {
            return '62'.substr($digit, 1);
        }

        return $digit;
    }

    /** Null bila nomor belum diisi atau tidak masuk akal, agar pemanggil bisa memakai cadangan. */
    public static function url(?string $nomor, string $pesan): ?string
    {
        $nomor = self::normalisasi($nomor);

        if (strlen($nomor) < 9) {
            return null;
        }

        return "https://wa.me/{$nomor}?text=".rawurlencode($pesan);
    }

    public static function urlPerusahaan(string $pesan): ?string
    {
        return self::url(config('perusahaan.whatsapp'), $pesan);
    }
}
