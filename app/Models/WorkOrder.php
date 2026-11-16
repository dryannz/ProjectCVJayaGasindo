<?php

namespace App\Models;

use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory;

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DITUGASKAN = 'ditugaskan';

    public const STATUS_DIKERJAKAN = 'dikerjakan';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_MENUNGGU => 'Menunggu',
        self::STATUS_DITUGASKAN => 'Ditugaskan',
        self::STATUS_DIKERJAKAN => 'Dikerjakan',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_DIBATALKAN => 'Dibatalkan',
    ];

    protected $table = 'work_order';

    protected $primaryKey = 'nomor_wo';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nomor_wo',
        'pono',
        'idcust',
        'tanggal_terbit',
        'lokasi_pekerjaan',
        'uraian_pekerjaan',
        'keluhan_tangki',
        'idpetugas',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_bast',
        'catatan_teknisi',
        'catatan_admin',
        'alasan_batal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_bast' => 'date',
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
        return $this->hasMany(DetailWorkOrder::class, 'nomor_wo', 'nomor_wo');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIBATALKAN], true);
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'bg-slate-100 text-slate-700',
            self::STATUS_DITUGASKAN => 'bg-yellow-100 text-yellow-900',
            self::STATUS_DIKERJAKAN => 'bg-blue-100 text-blue-900',
            self::STATUS_SELESAI => 'bg-emerald-100 text-emerald-800',
            self::STATUS_DIBATALKAN => 'bg-red-100 text-red-800',
            default => 'bg-slate-100 text-slate-700',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'nomor_wo';
    }
}
