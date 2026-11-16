<?php

namespace App\Models;

use Database\Factories\StokMutasiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMutasi extends Model
{
    /** @use HasFactory<StokMutasiFactory> */
    use HasFactory;

    public const JENIS_MASUK = 'masuk';

    public const JENIS_KELUAR = 'keluar';

    public const JENIS_KOREKSI = 'koreksi';

    protected $table = 'stok_mutasi';

    protected $fillable = [
        'idbarangjasa',
        'jenis',
        'qty',
        'stok_sebelum',
        'stok_sesudah',
        'referensi_tipe',
        'referensi_id',
        'keterangan',
        'idpetugas',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'stok_sebelum' => 'integer',
            'stok_sesudah' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function barangJasa(): BelongsTo
    {
        return $this->belongsTo(BarangJasa::class, 'idbarangjasa', 'idbarangjasa');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(Petugas::class, 'idpetugas', 'idpetugas');
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(Po::class, 'referensi_id', 'pono')
            ->where('referensi_tipe', 'po');
    }
}
