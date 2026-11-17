<?php

namespace App\Models;

use Database\Factories\SuratJalanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratJalan extends Model
{
    /** @use HasFactory<SuratJalanFactory> */
    use HasFactory;

    public const JENIS_PENGIRIMAN = 'pengiriman';

    public const JENIS_PENJEMPUTAN = 'penjemputan';

    public const JENIS_PENGEMBALIAN_TANGKI = 'pengembalian_tangki';

    public const JENIS = [
        self::JENIS_PENGIRIMAN => 'Pengiriman',
        self::JENIS_PENJEMPUTAN => 'Penjemputan',
        self::JENIS_PENGEMBALIAN_TANGKI => 'Pengembalian tangki',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DITERBITKAN = 'diterbitkan';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_DITERBITKAN => 'Diterbitkan',
        self::STATUS_DITERIMA => 'Diterima',
        self::STATUS_DIBATALKAN => 'Dibatalkan',
    ];

    protected $table = 'surat_jalan';

    protected $primaryKey = 'nosuratjalan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nosuratjalan',
        'tgl',
        'idpetugas',
        'pono',
        'idcust',
        'attn',
        'rev',
        'mobil',
        'jenis',
        'status',
        'catatan',
        'nama_penerima',
        'tanggal_diterima',
        'alasan_batal',
    ];

    protected function casts(): array
    {
        return [
            'tgl' => 'date',
            'tanggal_diterima' => 'date',
        ];
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'pono', 'pono');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'idcust', 'idcust');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'idpetugas', 'idpetugas');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailSuratJalan::class, 'nosuratjalan', 'nosuratjalan');
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_DITERBITKAN, self::STATUS_DITERIMA], true);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst(str_replace('_', ' ', $this->jenis));
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-slate-100 text-slate-700',
            self::STATUS_DITERBITKAN => 'bg-yellow-100 text-yellow-900',
            self::STATUS_DITERIMA => 'bg-emerald-100 text-emerald-800',
            self::STATUS_DIBATALKAN => 'bg-red-100 text-red-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'nosuratjalan';
    }
}
