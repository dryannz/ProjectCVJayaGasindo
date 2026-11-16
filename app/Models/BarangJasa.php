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

    public function detailPo(): HasMany
    {
        return $this->hasMany(DetailPo::class, 'idbarangjasa', 'idbarangjasa');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'idbarangjasa', 'idbarangjasa');
    }

    public function scopeAktif(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'idbarangjasa';
    }

    public function getHargaTampilAttribute(): ?string
    {
        if ($this->mode_harga !== 'fix' || $this->price === null) {
            return null;
        }

        $formattedPrice = 'Rp '.number_format((float) $this->price, 0, ',', '.');

        return $this->unit && $this->unit !== '-' ? "{$formattedPrice} / {$this->unit}" : $formattedPrice;
    }

    public function getLabelHargaAttribute(): string
    {
        return match ($this->mode_harga) {
            'fix' => $this->harga_tampil ?? 'Hubungi Kami',
            'rfq' => 'Harga sesuai permintaan',
            'khusus' => 'Harga khusus untuk customer terdaftar',
            default => 'Hubungi Kami',
        };
    }

    public function getLabelKetersediaanAttribute(): ?string
    {
        if ($this->tipe === 'jasa') {
            return null;
        }

        return ($this->stok ?? 0) > 0 ? 'Tersedia' : 'Stok habis';
    }

    public function isTersedia(): bool
    {
        if ($this->tipe === 'jasa') {
            return true;
        }

        return ($this->stok ?? 0) > 0;
    }

    public function getTautanPenawaranAttribute(): string
    {
        $whatsapp = preg_replace('/[^0-9]/', '', (string) config('perusahaan.whatsapp'));

        if ($whatsapp === '') {
            return route('contact');
        }

        if (str_starts_with($whatsapp, '0')) {
            $whatsapp = '62'.substr($whatsapp, 1);
        }

        $message = urlencode(sprintf(
            'Halo %s, saya ingin meminta penawaran untuk %s (Kode: %s).',
            config('perusahaan.nama'),
            $this->nama_barang,
            $this->idbarangjasa,
        ));

        return "https://wa.me/{$whatsapp}?text={$message}";
    }
}
