<?php

namespace App\Support;

use App\Models\BarangJasa;
use Illuminate\Support\Collection;

/**
 * Keranjang RFQ berbasis session, sehingga pengunjung tidak perlu login.
 * Yang disimpan hanya kode produk dan jumlah; data produk selalu dibaca ulang
 * dari database agar nama, status aktif, dan label harga tidak basi.
 */
class RfqCart
{
    public const SESSION_KEY = 'rfq.cart';

    public const MAX_JENIS = 30;

    public const MAX_QTY = 9999;

    /** @return array<string, int> */
    public function raw(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function add(string $idbarangjasa, int $qty = 1): void
    {
        $cart = $this->raw();

        if (! isset($cart[$idbarangjasa]) && count($cart) >= self::MAX_JENIS) {
            return;
        }

        $cart[$idbarangjasa] = $this->batasi(($cart[$idbarangjasa] ?? 0) + $qty);
        session([self::SESSION_KEY => $cart]);
    }

    public function set(string $idbarangjasa, int $qty): void
    {
        $cart = $this->raw();

        if (! isset($cart[$idbarangjasa])) {
            return;
        }

        $cart[$idbarangjasa] = $this->batasi($qty);
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(string $idbarangjasa): void
    {
        $cart = $this->raw();
        unset($cart[$idbarangjasa]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Item keranjang beserta modelnya. Produk yang sudah dinonaktifkan atau
     * dihapus setelah masuk keranjang dibuang diam-diam.
     *
     * @return Collection<int, array{produk: BarangJasa, qty: int}>
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if ($cart === []) {
            return collect();
        }

        $produk = BarangJasa::query()
            ->aktif()
            ->with('kategori')
            ->whereIn('idbarangjasa', array_keys($cart))
            ->get()
            ->keyBy('idbarangjasa');

        $items = collect();

        foreach ($cart as $id => $qty) {
            if ($produk->has((string) $id)) {
                $items->push(['produk' => $produk[(string) $id], 'qty' => (int) $qty]);
            }
        }

        // Sinkronkan session bila ada produk yang gugur.
        if ($items->count() !== count($cart)) {
            session([self::SESSION_KEY => $items->mapWithKeys(
                fn (array $item): array => [$item['produk']->idbarangjasa => $item['qty']],
            )->all()]);
        }

        return $items;
    }

    public function count(): int
    {
        return count($this->raw());
    }

    private function batasi(int $qty): int
    {
        return max(1, min(self::MAX_QTY, $qty));
    }
}
