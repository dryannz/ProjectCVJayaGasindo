<?php

namespace App\Support;

use App\Exceptions\InsufficientStockException;
use App\Models\BarangJasa;
use App\Models\StokMutasi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function masuk(
        BarangJasa|string $produk,
        int $qty,
        ?string $referensiTipe = null,
        ?string $referensiId = null,
        ?string $keterangan = null,
        ?string $idpetugas = null,
    ): ?StokMutasi {
        $this->assertPositiveQuantity($qty);

        return DB::transaction(function () use ($produk, $qty, $referensiTipe, $referensiId, $keterangan, $idpetugas): ?StokMutasi {
            $barangJasa = $this->lockProduct($produk);

            if ($barangJasa->tipe !== 'fisik') {
                return null;
            }

            $stokSebelum = (int) ($barangJasa->stok ?? 0);
            $stokSesudah = $stokSebelum + $qty;

            $barangJasa->update(['stok' => $stokSesudah]);

            return $this->recordMutation(
                $barangJasa,
                StokMutasi::JENIS_MASUK,
                $qty,
                $stokSebelum,
                $stokSesudah,
                $referensiTipe,
                $referensiId,
                $keterangan,
                $idpetugas,
            );
        });
    }

    public function keluar(
        BarangJasa|string $produk,
        int $qty,
        ?string $referensiTipe = null,
        ?string $referensiId = null,
        ?string $keterangan = null,
        ?string $idpetugas = null,
    ): ?StokMutasi {
        $this->assertPositiveQuantity($qty);

        return DB::transaction(function () use ($produk, $qty, $referensiTipe, $referensiId, $keterangan, $idpetugas): ?StokMutasi {
            $barangJasa = $this->lockProduct($produk);

            if ($barangJasa->tipe !== 'fisik') {
                return null;
            }

            $stokSebelum = (int) ($barangJasa->stok ?? 0);

            if ($stokSebelum < $qty) {
                throw new InsufficientStockException(
                    $barangJasa->idbarangjasa,
                    $barangJasa->nama_barang,
                    $qty,
                    $stokSebelum,
                );
            }

            $stokSesudah = $stokSebelum - $qty;
            $barangJasa->update(['stok' => $stokSesudah]);

            return $this->recordMutation(
                $barangJasa,
                StokMutasi::JENIS_KELUAR,
                $qty,
                $stokSebelum,
                $stokSesudah,
                $referensiTipe,
                $referensiId,
                $keterangan,
                $idpetugas,
            );
        });
    }

    /**
     * @param  array<string, int>  $requirements
     * @return array<int, array{idbarangjasa: string, nama_barang: string, diminta: int, tersedia: int}>
     */
    public function findShortages(array $requirements): array
    {
        if ($requirements === []) {
            return [];
        }

        $products = BarangJasa::query()
            ->whereIn('idbarangjasa', array_keys($requirements))
            ->lockForUpdate()
            ->get()
            ->keyBy('idbarangjasa');
        $shortages = [];

        foreach ($requirements as $idbarangjasa => $qty) {
            $product = $products->get($idbarangjasa);
            $available = (int) ($product?->stok ?? 0);

            if ($product === null || $available < $qty) {
                $shortages[] = [
                    'idbarangjasa' => $idbarangjasa,
                    'nama_barang' => $product?->nama_barang ?? $idbarangjasa,
                    'diminta' => $qty,
                    'tersedia' => $available,
                ];
            }
        }

        return $shortages;
    }

    public function koreksi(
        BarangJasa|string $produk,
        int $stokBaru,
        string $keterangan,
        ?string $idpetugas = null,
    ): ?StokMutasi {
        if ($stokBaru < 0) {
            throw new InvalidArgumentException('Stok baru tidak boleh kurang dari 0.');
        }

        if (trim($keterangan) === '') {
            throw new InvalidArgumentException('Keterangan koreksi stok wajib diisi.');
        }

        return DB::transaction(function () use ($produk, $stokBaru, $keterangan, $idpetugas): ?StokMutasi {
            $barangJasa = $this->lockProduct($produk);

            if ($barangJasa->tipe !== 'fisik') {
                return null;
            }

            $stokSebelum = (int) ($barangJasa->stok ?? 0);

            if ($stokSebelum === $stokBaru) {
                return null;
            }

            $barangJasa->update(['stok' => $stokBaru]);

            return $this->recordMutation(
                $barangJasa,
                StokMutasi::JENIS_KOREKSI,
                abs($stokBaru - $stokSebelum),
                $stokSebelum,
                $stokBaru,
                'manual',
                null,
                $keterangan,
                $idpetugas,
            );
        });
    }

    private function lockProduct(BarangJasa|string $produk): BarangJasa
    {
        $idbarangjasa = $produk instanceof BarangJasa ? $produk->getKey() : $produk;

        if (! is_string($idbarangjasa) || $idbarangjasa === '') {
            throw new ModelNotFoundException;
        }

        return BarangJasa::query()
            ->where('idbarangjasa', $idbarangjasa)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function recordMutation(
        BarangJasa $barangJasa,
        string $jenis,
        int $qty,
        int $stokSebelum,
        int $stokSesudah,
        ?string $referensiTipe,
        ?string $referensiId,
        ?string $keterangan,
        ?string $idpetugas,
    ): StokMutasi {
        return StokMutasi::create([
            'idbarangjasa' => $barangJasa->idbarangjasa,
            'jenis' => $jenis,
            'qty' => $qty,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'referensi_tipe' => $referensiTipe,
            'referensi_id' => $referensiId,
            'keterangan' => $keterangan,
            'idpetugas' => $idpetugas,
        ]);
    }

    private function assertPositiveQuantity(int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Jumlah stok harus lebih besar dari 0.');
        }
    }
}
