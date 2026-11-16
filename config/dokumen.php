<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Format penomoran dokumen
    |--------------------------------------------------------------------------
    | Placeholder: {tahun} = 4 digit, {bulan} = 2 digit, {urut4} = urutan
    | 4 digit per jenis per bulan. ASUMSI: format asli dari IKM belum ada.
    */
    'periode' => env('DOKUMEN_PERIODE', 'bulanan'),

    'jenis' => [
        'quotation' => [
            'format' => env('DOKUMEN_FORMAT_QUOTATION', 'QT/{tahun}/{bulan}/{urut4}'),
        ],
        'po' => [
            'format' => env('DOKUMEN_FORMAT_PO', 'PO/{tahun}/{bulan}/{urut4}'),
        ],
        'work_order' => [
            'format' => env('DOKUMEN_FORMAT_WORK_ORDER', '{bulan}-{urut3}/ {singkatan}-{kode}/ WO/ {romawi}/ {tahun}'),
        ],
        'surat_jalan' => [
            'format' => env('DOKUMEN_FORMAT_SURAT_JALAN', '{bulan}-{urut3}/ {singkatan}-{kode}/ SJ/ {romawi}/ {tahun}'),
        ],
    ],
];
