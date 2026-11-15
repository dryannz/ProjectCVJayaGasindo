<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadItem extends Model
{
    protected $table = 'lead_items';

    protected $fillable = [
        'lead_id',
        'idbarangjasa',
        'nama_barang',
        'tipe',
        'ukuran',
        'unit',
        'label_harga',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
