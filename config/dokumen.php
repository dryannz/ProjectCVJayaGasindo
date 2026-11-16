<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Format penomoran dokumen
    |--------------------------------------------------------------------------
    | Placeholder: {tahun} = 4 digit, {bulan} = 2 digit, {urut4} = urutan
    | 4 digit per jenis per bulan. ASUMSI: format asli dari IKM belum ada.
    */
    'jenis' => [
        'quotation' => [
            'format' => env('DOKUMEN_FORMAT_QUOTATION', 'QT/{tahun}/{bulan}/{urut4}'),
        ],
    ],
];
