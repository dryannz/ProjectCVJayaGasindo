<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorDokumen extends Model
{
    public const JENIS_QUOTATION = 'quotation';

    public const JENIS_PO = 'po';

    public $timestamps = false;

    protected $table = 'nomor_dokumen';

    protected $fillable = [
        'jenis',
        'periode',
        'terakhir',
    ];

    protected function casts(): array
    {
        return [
            'terakhir' => 'integer',
        ];
    }
}
