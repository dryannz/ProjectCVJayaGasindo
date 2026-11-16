<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Petugas extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'petugas';

    protected $primaryKey = 'idpetugas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'idpetugas',
        'namapetugas',
        'notelp',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function po(): HasMany
    {
        return $this->hasMany(Po::class, 'idpetugas', 'idpetugas');
    }

    public function stokMutasi(): HasMany
    {
        return $this->hasMany(StokMutasi::class, 'idpetugas', 'idpetugas');
    }
}
