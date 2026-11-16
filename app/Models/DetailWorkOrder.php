<?php

namespace App\Models;

use Database\Factories\DetailWorkOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailWorkOrder extends Model
{
    /** @use HasFactory<DetailWorkOrderFactory> */
    use HasFactory;

    protected $table = 'detail_work_order';

    protected $fillable = [
        'nomor_wo',
        'detail_po_id',
        'nama_barang',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'detail_po_id' => 'integer',
            'qty' => 'integer',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'nomor_wo', 'nomor_wo');
    }

    public function detailPo(): BelongsTo
    {
        return $this->belongsTo(DetailPo::class, 'detail_po_id');
    }
}
