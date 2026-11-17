<?php

namespace App\Models;

use Database\Factories\DetailSuratJalanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailSuratJalan extends Model
{
    /** @use HasFactory<DetailSuratJalanFactory> */
    use HasFactory;

    protected $table = 'detail_surat_jalan';

    protected $fillable = [
        'nosuratjalan',
        'detail_po_id',
        'nama_barang',
        'qty',
        'unit',
    ];

    protected function casts(): array
    {
        return [
            'detail_po_id' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function suratJalan(): BelongsTo
    {
        return $this->belongsTo(SuratJalan::class, 'nosuratjalan', 'nosuratjalan');
    }

    public function detailPo(): BelongsTo
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }
}
