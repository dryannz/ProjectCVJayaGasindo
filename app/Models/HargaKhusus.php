<?php

namespace App\Models;

use Database\Factories\HargaKhususFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HargaKhusus extends Model
{
    /** @use HasFactory<HargaKhususFactory> */
    use HasFactory;

    protected $table = 'harga_khusus';

    protected $fillable = [
        'idcust',
        'idbarangjasa',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function barangJasa(): BelongsTo
    {
        return $this->belongsTo(BarangJasa::class, 'idbarangjasa', 'idbarangjasa');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'idcust', 'idcust');
    }
}
