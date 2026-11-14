<?php

namespace App\Models;

use Database\Factories\BarangJasaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BarangJasa extends Model
{
    /** @use HasFactory<BarangJasaFactory> */
    use HasFactory;

    protected $table = 'barang_jasa';

    protected $primaryKey = 'idbarangjasa';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'idbarangjasa',
        'kategori_id',
        'tipe',
        'nama_barang',
        'deskripsi',
        'ukuran',
        'unit',
        'mode_harga',
        'price',
        'stok',
        'gambar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stok' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function hargaKhusus(): HasMany
    {
        return $this->hasMany(HargaKhusus::class, 'idbarangjasa', 'idbarangjasa');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'idbarangjasa';
    }
}
