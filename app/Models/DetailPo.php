<?php

namespace App\Models;

use Database\Factories\DetailPoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPo extends Model
{
    /** @use HasFactory<DetailPoFactory> */
    use HasFactory;

    protected $table = 'detail_po';

    protected $fillable = [
        'idbarangjasa',
        'pono',
        'qty',
        'amount',
        'nama_barang',
        'unit',
        'harga_satuan',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'amount' => 'decimal:0',
            'harga_satuan' => 'decimal:0',
        ];
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'pono', 'pono');
    }

    public function barangJasa(): BelongsTo
    {
        return $this->belongsTo(BarangJasa::class, 'idbarangjasa', 'idbarangjasa');
    }
}
