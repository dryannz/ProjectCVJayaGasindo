<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\WhatsApp;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    public const STATUS_BARU = 'baru';

    public const STATUS_DIHUBUNGI = 'dihubungi';

    public const STATUS_QUOTATION = 'quotation';

    public const STATUS_BATAL = 'batal';

    public const STATUSES = [
        self::STATUS_BARU => 'Baru',
        self::STATUS_DIHUBUNGI => 'Sudah dihubungi',
        self::STATUS_QUOTATION => 'Jadi quotation',
        self::STATUS_BATAL => 'Batal',
    ];

    public const JENIS = [
        'produk' => 'Produk',
        'jasa' => 'Jasa',
        'campuran' => 'Produk & jasa',
    ];

    protected $table = 'leads';

    protected $fillable = [
        'kode',
        'nama',
        'perusahaan',
        'telepon',
        'email',
        'alamat',
        'catatan',
        'jenis',
        'status',
        'pesan_whatsapp',
        'ip_address',
        'ditangani_oleh',
        'catatan_internal',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(LeadItem::class);
    }

    public function penanganan(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'ditangani_oleh', 'idpetugas');
    }

    public function scopeBaru(Builder $query): void
    {
        $query->where('status', self::STATUS_BARU);
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst($this->jenis);
    }

    /** Tautan WhatsApp ke customer, untuk tindak lanjut oleh sales. */
    public function getTautanHubungiAttribute(): ?string
    {
        return WhatsApp::url(
            $this->telepon,
            sprintf('Halo %s, kami dari %s menindaklanjuti permintaan penawaran %s.', $this->nama, config('perusahaan.nama'), $this->kode),
        );
    }

    public static function buatKode(): string
    {
        $prefix = 'RFQ-'.now()->format('Ymd').'-';

        $terakhir = static::query()
            ->where('kode', 'like', $prefix.'%')
            ->orderByDesc('kode')
            ->value('kode');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }
}
