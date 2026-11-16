<?php

namespace App\Models;

use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_TERKIRIM = 'terkirim';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public const STATUS_KADALUARSA = 'kadaluarsa';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_TERKIRIM => 'Terkirim',
        self::STATUS_DISETUJUI => 'Disetujui',
        self::STATUS_DITOLAK => 'Ditolak',
        self::STATUS_KADALUARSA => 'Kadaluarsa',
    ];

    protected $table = 'quotation';

    protected $fillable = [
        'nomor_quotation',
        'lead_id',
        'idcust',
        'idpetugas',
        'tanggal',
        'berlaku_sampai',
        'status',
        'catatan',
        'syarat',
        'subtotal',
        'diskon',
        'ppn',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'berlaku_sampai' => 'date',
            'subtotal' => 'decimal:0',
            'diskon' => 'decimal:0',
            'ppn' => 'decimal:0',
            'total' => 'decimal:0',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
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
        return $this->hasMany(DetailQuotation::class);
    }

    /**
     * Status efektif: quotation terkirim yang melewati berlaku_sampai tampil sebagai kadaluarsa.
     * Nilai di database tetap "terkirim", tanpa job terjadwal.
     */
    public function getStatusEfektifAttribute(): string
    {
        if ($this->status === self::STATUS_TERKIRIM
            && $this->berlaku_sampai !== null
            && $this->berlaku_sampai->isPast()) {
            return self::STATUS_KADALUARSA;
        }

        return $this->status;
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUSES[$this->status_efektif] ?? ucfirst($this->status_efektif);
    }

    public function getLabelWarnaStatusAttribute(): string
    {
        return match ($this->status_efektif) {
            self::STATUS_DRAFT => 'bg-slate-100 text-slate-600',
            self::STATUS_TERKIRIM => 'bg-yellow-100 text-yellow-900',
            self::STATUS_DISETUJUI => 'bg-emerald-100 text-emerald-800',
            self::STATUS_DITOLAK => 'bg-red-100 text-red-800',
            self::STATUS_KADALUARSA => 'bg-orange-100 text-orange-800',
            default => 'bg-slate-100 text-slate-600',
        };
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public static function transisiValid(string $dari, string $ke): bool
    {
        return match ($ke) {
            self::STATUS_TERKIRIM => $dari === self::STATUS_DRAFT,
            self::STATUS_DISETUJUI, self::STATUS_DITOLAK => $dari === self::STATUS_TERKIRIM,
            default => false,
        };
    }
}
