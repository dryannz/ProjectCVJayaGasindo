<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailQuotation extends Model
{
    protected $table = 'detail_quotation';

    protected $fillable = [
        'quotation_id',
        'idbarangjasa',
        'nama_barang',
        'tipe',
        'ukuran',
        'unit',
        'qty',
        'harga_satuan',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'decimal:0',
            'jumlah' => 'decimal:0',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function barangJasa(): BelongsTo
    {
        return $this->belongsTo(BarangJasa::class, 'idbarangjasa', 'idbarangjasa');
    }
}
