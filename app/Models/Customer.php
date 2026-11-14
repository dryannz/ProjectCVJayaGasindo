<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $table = 'customer';

    protected $primaryKey = 'idcust';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'idcust',
        'notelp_pt',
        'nama_pt',
        'alamat_pt',
        'pic',
    ];

    public function hargaKhusus(): HasMany
    {
        return $this->hasMany(HargaKhusus::class, 'idcust', 'idcust');
    }
}
