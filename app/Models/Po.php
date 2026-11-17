<?php

namespace App\Models;

use Database\Factories\PoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Po extends Model
{
    /** @use HasFactory<PoFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DIPROSES = 'diproses';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_BATAL = 'batal';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_DIPROSES => 'Diproses',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_BATAL => 'Batal',
    ];

    protected $table = 'po';

    protected $primaryKey = 'pono';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'pono',
        'idcust',
        'tgl_order',
        'schedule_delivery',
        'term_of_payment',
        'project_name',
        'project_location',
        'quotation_id',
        'idpetugas',
        'no_po_customer',
        'status',
        'subtotal',
        'diskon',
        'ppn',
        'total',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tgl_order' => 'date',
            'schedule_delivery' => 'date',
            'subtotal' => 'decimal:0',
            'diskon' => 'decimal:0',
            'ppn' => 'decimal:0',
            'total' => 'decimal:0',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'idcust', 'idcust');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'idpetugas', 'idpetugas');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPo::class, 'pono', 'pono');
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'referensi_id', 'pono')
            ->where('referensi_tipe', 'po');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'pono', 'pono');
    }

    public function suratJalans(): HasMany
    {
        return $this->hasMany(SuratJalan::class, 'pono', 'pono');
    }

    public function activeSuratJalans(): HasMany
    {
        return $this->suratJalans()->whereIn('status', [
            SuratJalan::STATUS_DITERBITKAN,
            SuratJalan::STATUS_DITERIMA,
        ]);
    }

    public function hasActiveSuratJalan(): bool
    {
        return $this->activeSuratJalans()->exists();
    }

    public function canCreateSuratJalan(): bool
    {
        return $this->status === self::STATUS_DIPROSES;
    }

    public function canCreateWorkOrder(): bool
    {
        return in_array($this->status, [self::STATUS_DIPROSES, self::STATUS_SELESAI], true);
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isStockConsumed(): bool
    {
        return $this->stockMutations()->where('jenis', 'keluar')->exists();
    }

    public function canTransitionTo(string $status): bool
    {
        if ($status === self::STATUS_BATAL) {
            return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_DIPROSES], true);
        }

        return match ($status) {
            self::STATUS_DIPROSES => $this->status === self::STATUS_DRAFT,
            self::STATUS_SELESAI => $this->status === self::STATUS_DIPROSES,
            default => false,
        };
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'bg-slate-100 text-slate-700',
            self::STATUS_DIPROSES => 'bg-yellow-100 text-yellow-900',
            self::STATUS_SELESAI => 'bg-emerald-100 text-emerald-800',
            self::STATUS_BATAL => 'bg-red-100 text-red-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }
}
