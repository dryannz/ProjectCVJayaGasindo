<?php

return [
    'nama' => env('PERUSAHAAN_NAMA', 'CV Mandiri Jaya Gasindo Engineering'),
    'singkatan' => env('PERUSAHAAN_SINGKATAN', 'MJGE'),
    'whatsapp' => env('PERUSAHAAN_WHATSAPP', '08978566701'),
    'telepon' => env('PERUSAHAAN_TELEPON', 'ISI DENGAN DATA IKM'),
    'email' => env('PERUSAHAAN_EMAIL', 'cvmjengineering@gmail.com'),
    'alamat' => env('PERUSAHAAN_ALAMAT', 'Graha Prima Blok ID No. 539 RT 01 RW 09, Satria Jaya, Tambun Utara, Kab. Bekasi, Jawa Barat 17510'),
    'jam_operasional' => env('PERUSAHAAN_JAM_OPERASIONAL', 'Senin - Sabtu, 08.00 - 17.00 WIB'),

    // Dokumen resmi (PDF)
    'logo' => env('PERUSAHAAN_LOGO', 'images/logo.png'),
    'npwp' => env('PERUSAHAAN_NPWP', '0433.9168.9743.5000'),

    'bank' => [
        'nama' => env('PERUSAHAAN_BANK_NAMA', 'Bank Mandiri KCP Bekasi Prima Orchad'),
        'nomor' => env('PERUSAHAAN_BANK_NOMOR', '156-00-1784248-7'),
        'atas_nama' => env('PERUSAHAAN_BANK_ATAS_NAMA', 'CV Mandiri Jaya Gasindo Engineering'),
    ],

    'pajak' => [
        'ppn_persen' => (float) env('PERUSAHAAN_PPN_PERSEN', 11),
        'pph_persen' => (float) env('PERUSAHAAN_PPH_PERSEN', 2),
    ],

    // Teks di bawah garis kop surat, per jenis dokumen
    'deskripsi_usaha' => [
        'invoice' => 'General Supplier & Instalation Gas Industri / Medis',
        'surat_jalan' => 'DISTRIBUTOR : O2,N2,CO2,CO,C2H2,H2 MIX GAS & INSTALASI GAS MEDIS/INDUSTRI',
        'quotation' => 'General Supplier & Instalation Gas Industri / Medis',
    ],

    'penandatangan' => [
        'quotation' => [
            'nama' => env('PERUSAHAAN_TTD_QUOTATION_NAMA', '[ISI: Nama penandatangan]'),
            'jabatan' => env('PERUSAHAAN_TTD_QUOTATION_JABATAN', '[ISI: Jabatan]'),
        ],
        'invoice' => [
            'nama' => env('PERUSAHAAN_TTD_INVOICE_NAMA', 'Nabiila Azzahra'),
            'jabatan' => env('PERUSAHAAN_TTD_INVOICE_JABATAN', '[ISI: Jabatan]'),
        ],
        'surat_jalan' => [
            'nama' => env('PERUSAHAAN_TTD_SJ_NAMA', 'Sugeng Riyanto'),
            'jabatan' => env('PERUSAHAAN_TTD_SJ_JABATAN', '[ISI: Jabatan]'),
        ],
    ],


    'cap' => env('PERUSAHAAN_CAP', null),
    'tanda_tangan' => env('PERUSAHAAN_TANDA_TANGAN', null),

    'quotation' => [
        'masa_berlaku_hari' => (int) env('PERUSAHAAN_QUOTATION_MASA_BERLAKU', 14),
        'syarat' => [
            '[ISI: Syarat dan ketentuan dari IKM, satu baris per poin]',
        ],
    ],
];
