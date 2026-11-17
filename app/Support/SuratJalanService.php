<?php

namespace App\Support;

use App\Exceptions\InsufficientStockException;
use App\Models\DetailPo;
use App\Models\DetailSuratJalan;
use App\Models\NomorDokumen;
use App\Models\Po;
use App\Models\SuratJalan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SuratJalanService
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromPo(Po $po, array $data, string $idpetugas): SuratJalan
    {
        $items = $this->normalizeItems($data['items'] ?? []);
        $jenis = (string) ($data['jenis'] ?? '');
        $this->assertJenis($jenis);

        return DB::transaction(function () use ($po, $data, $idpetugas, $items, $jenis): SuratJalan {
            $lockedPo = Po::query()
                ->with(['customer', 'details.barangJasa'])
                ->lockForUpdate()
                ->findOrFail($po->getKey());

            if (! $lockedPo->canCreateSuratJalan()) {
                throw ValidationException::withMessages([
                    'pono' => 'Surat Jalan hanya dapat dibuat dari PO yang berstatus Diproses.',
                ]);
            }

            $this->assertItemsWithinRemainingQuantity($lockedPo, $items);

            try {
                $number = DocumentNumberService::generate(
                    NomorDokumen::JENIS_SURAT_JALAN,
                    $lockedPo->customer?->kode_singkat,
                );
            } catch (InvalidArgumentException $exception) {
                throw ValidationException::withMessages([
                    'idcust' => $exception->getMessage(),
                ]);
            }

            $suratJalan = SuratJalan::create([
                'nosuratjalan' => $number,
                'tgl' => $data['tgl'],
                'idpetugas' => $idpetugas,
                'pono' => $lockedPo->pono,
                'idcust' => $lockedPo->idcust,
                'attn' => $this->resolveAttn($data, $lockedPo),
                'rev' => trim((string) ($data['rev'] ?? 'Rev - 00')) ?: 'Rev - 00',
                'mobil' => $this->nullableString($data['mobil'] ?? null),
                'jenis' => $jenis,
                'status' => SuratJalan::STATUS_DRAFT,
                'catatan' => $this->nullableString($data['catatan'] ?? null),
            ]);

            $details = $lockedPo->details->keyBy('id');

            foreach ($items as $item) {
                /** @var DetailPo $detailPo */
                $detailPo = $details->get($item['detail_po_id']);

                $suratJalan->details()->create([
                    'detail_po_id' => $detailPo->id,
                    'nama_barang' => $detailPo->nama_barang,
                    'qty' => $item['qty'],
                    'unit' => $detailPo->unit,
                ]);
            }

            return $suratJalan->load(['po', 'customer', 'petugas', 'details.detailPo']);
        });
    }

    /**
     * @return array<int, array{detail: DetailPo, used: int, remaining: int}>
     */
    public function remainingItems(Po $po): array
    {
        $po->loadMissing('details.barangJasa');

        $activeQuantities = DetailSuratJalan::query()
            ->whereHas('suratJalan', function (Builder $query) use ($po): void {
                $query->where('pono', $po->pono)
                    ->whereIn('status', [SuratJalan::STATUS_DITERBITKAN, SuratJalan::STATUS_DITERIMA]);
            })
            ->selectRaw('detail_po_id, SUM(qty) as total_qty')
            ->groupBy('detail_po_id')
            ->pluck('total_qty', 'detail_po_id');

        return $po->details->map(function (DetailPo $detail) use ($activeQuantities): array {
            $used = (int) ($activeQuantities->get($detail->id) ?? 0);

            return [
                'detail' => $detail,
                'used' => $used,
                'remaining' => max(0, (int) $detail->qty - $used),
            ];
        })->all();
    }

    public function publish(SuratJalan $suratJalan, string $idpetugas): SuratJalan
    {
        return DB::transaction(function () use ($suratJalan, $idpetugas): SuratJalan {
            $lockedSuratJalan = SuratJalan::query()
                ->with(['po.details', 'details.detailPo.barangJasa'])
                ->lockForUpdate()
                ->findOrFail($suratJalan->getKey());

            if (! $lockedSuratJalan->isEditable()) {
                throw ValidationException::withMessages([
                    'status' => 'Surat Jalan yang sudah diterbitkan tidak dapat diterbitkan ulang.',
                ]);
            }

            if ($lockedSuratJalan->po === null || ! $lockedSuratJalan->po->canCreateSuratJalan()) {
                throw ValidationException::withMessages([
                    'status' => 'Surat Jalan hanya dapat diterbitkan selama PO berstatus Diproses.',
                ]);
            }

            $items = $lockedSuratJalan->details
                ->map(fn (DetailSuratJalan $detail): array => [
                    'detail_po_id' => $detail->detail_po_id,
                    'qty' => $detail->qty,
                ])
                ->all();
            $this->assertItemsWithinRemainingQuantity($lockedSuratJalan->po, $items, $lockedSuratJalan->nosuratjalan);

            $requirements = $this->stockRequirements($lockedSuratJalan);
            $shortages = $this->stockService->findShortages($requirements);

            if ($shortages !== []) {
                throw InsufficientStockException::forShortages($shortages);
            }

            foreach ($requirements as $idbarangjasa => $qty) {
                $this->stockService->keluar(
                    $idbarangjasa,
                    $qty,
                    'sj',
                    $lockedSuratJalan->nosuratjalan,
                    "Surat Jalan {$lockedSuratJalan->nosuratjalan} diterbitkan",
                    $idpetugas,
                );
            }

            $lockedSuratJalan->update([
                'status' => SuratJalan::STATUS_DITERBITKAN,
                'idpetugas' => $idpetugas,
            ]);

            return $lockedSuratJalan->fresh(['po', 'customer', 'petugas', 'details.detailPo']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function receive(SuratJalan $suratJalan, array $data, string $idpetugas): SuratJalan
    {
        return DB::transaction(function () use ($suratJalan, $data, $idpetugas): SuratJalan {
            $lockedSuratJalan = SuratJalan::query()->lockForUpdate()->findOrFail($suratJalan->getKey());

            if ($lockedSuratJalan->status !== SuratJalan::STATUS_DITERBITKAN) {
                throw ValidationException::withMessages([
                    'status' => 'Surat Jalan hanya dapat ditandai diterima setelah diterbitkan.',
                ]);
            }

            $namaPenerima = trim((string) ($data['nama_penerima'] ?? ''));

            if ($namaPenerima === '') {
                throw ValidationException::withMessages([
                    'nama_penerima' => 'Nama penerima wajib diisi.',
                ]);
            }

            if (trim((string) ($data['tanggal_diterima'] ?? '')) === '') {
                throw ValidationException::withMessages([
                    'tanggal_diterima' => 'Tanggal diterima wajib diisi.',
                ]);
            }

            $lockedSuratJalan->update([
                'status' => SuratJalan::STATUS_DITERIMA,
                'nama_penerima' => $namaPenerima,
                'tanggal_diterima' => $data['tanggal_diterima'],
                'catatan' => array_key_exists('catatan', $data)
                    ? $this->nullableString($data['catatan'])
                    : $lockedSuratJalan->catatan,
                'idpetugas' => $idpetugas,
            ]);

            return $lockedSuratJalan->fresh(['po', 'customer', 'petugas', 'details.detailPo']);
        });
    }

    public function updateNotes(SuratJalan $suratJalan, ?string $catatan, string $idpetugas): SuratJalan
    {
        return DB::transaction(function () use ($suratJalan, $catatan, $idpetugas): SuratJalan {
            $lockedSuratJalan = SuratJalan::query()->lockForUpdate()->findOrFail($suratJalan->getKey());

            if ($lockedSuratJalan->status === SuratJalan::STATUS_DIBATALKAN) {
                throw ValidationException::withMessages([
                    'catatan' => 'Catatan Surat Jalan yang dibatalkan tidak dapat diubah.',
                ]);
            }

            $lockedSuratJalan->update([
                'catatan' => $this->nullableString($catatan),
                'idpetugas' => $idpetugas,
            ]);

            return $lockedSuratJalan->fresh(['po', 'customer', 'petugas', 'details.detailPo']);
        });
    }

    public function cancel(SuratJalan $suratJalan, string $alasanBatal, string $idpetugas): SuratJalan
    {
        if (trim($alasanBatal) === '') {
            throw ValidationException::withMessages([
                'alasan_batal' => 'Alasan pembatalan wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($suratJalan, $alasanBatal, $idpetugas): SuratJalan {
            $lockedSuratJalan = SuratJalan::query()
                ->with(['details.detailPo.barangJasa'])
                ->lockForUpdate()
                ->findOrFail($suratJalan->getKey());

            if ($lockedSuratJalan->status === SuratJalan::STATUS_DIBATALKAN) {
                throw ValidationException::withMessages([
                    'status' => 'Surat Jalan sudah dibatalkan.',
                ]);
            }

            if (! in_array($lockedSuratJalan->status, [
                SuratJalan::STATUS_DRAFT,
                SuratJalan::STATUS_DITERBITKAN,
                SuratJalan::STATUS_DITERIMA,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Status Surat Jalan tidak dapat dibatalkan.',
                ]);
            }

            if ($lockedSuratJalan->isActive()) {
                foreach ($this->stockRequirements($lockedSuratJalan) as $idbarangjasa => $qty) {
                    $this->stockService->masuk(
                        $idbarangjasa,
                        $qty,
                        'sj',
                        $lockedSuratJalan->nosuratjalan,
                        "Pembatalan Surat Jalan {$lockedSuratJalan->nosuratjalan}",
                        $idpetugas,
                    );
                }
            }

            $lockedSuratJalan->update([
                'status' => SuratJalan::STATUS_DIBATALKAN,
                'alasan_batal' => trim($alasanBatal),
                'idpetugas' => $idpetugas,
            ]);

            return $lockedSuratJalan->fresh(['po', 'customer', 'petugas', 'details.detailPo']);
        });
    }

    /**
     * @param  array<int, mixed>  $items
     * @return array<int, array{detail_po_id: int, qty: int}>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu item wajib dipilih.',
            ]);
        }

        $normalized = [];
        $seen = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages([
                    "items.{$index}" => 'Item Surat Jalan tidak valid.',
                ]);
            }

            $detailPoId = (int) ($item['detail_po_id'] ?? 0);
            $qty = (int) ($item['qty'] ?? 0);

            if ($detailPoId < 1 || $qty < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty" => 'Qty item minimal 1 dan detail PO wajib dipilih.',
                ]);
            }

            if (isset($seen[$detailPoId])) {
                throw ValidationException::withMessages([
                    "items.{$index}.detail_po_id" => 'Detail PO tidak boleh dipilih lebih dari satu kali.',
                ]);
            }

            $seen[$detailPoId] = true;
            $normalized[] = [
                'detail_po_id' => $detailPoId,
                'qty' => $qty,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<int, array{detail_po_id: int, qty: int}>  $items
     */
    private function assertItemsWithinRemainingQuantity(Po $po, array $items, ?string $excludeNosuratjalan = null): void
    {
        $activeQuantities = $this->activeQuantities($po->pono, $excludeNosuratjalan);
        $details = $po->details->keyBy('id');

        foreach ($items as $index => $item) {
            /** @var DetailPo|null $detailPo */
            $detailPo = $details->get($item['detail_po_id']);

            if ($detailPo === null) {
                throw ValidationException::withMessages([
                    "items.{$index}.detail_po_id" => 'Detail item tidak berasal dari PO yang dipilih.',
                ]);
            }

            $alreadyUsed = $activeQuantities[$detailPo->id] ?? 0;
            $remaining = max(0, (int) $detailPo->qty - $alreadyUsed);

            if ($item['qty'] > $remaining) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty" => "Qty {$detailPo->nama_barang} melebihi sisa PO. Sisa tersedia: {$remaining}.",
                ]);
            }
        }
    }

    /** @return array<int, int> */
    private function activeQuantities(string $pono, ?string $excludeNosuratjalan = null): array
    {
        $details = DetailSuratJalan::query()
            ->whereHas('suratJalan', function ($query) use ($pono, $excludeNosuratjalan): void {
                $query->where('pono', $pono)
                    ->whereIn('status', [SuratJalan::STATUS_DITERBITKAN, SuratJalan::STATUS_DITERIMA]);

                if ($excludeNosuratjalan !== null) {
                    $query->where('nosuratjalan', '!=', $excludeNosuratjalan);
                }
            })
            ->lockForUpdate()
            ->get(['detail_po_id', 'qty']);
        $quantities = [];

        foreach ($details as $detail) {
            $quantities[$detail->detail_po_id] = ($quantities[$detail->detail_po_id] ?? 0) + (int) $detail->qty;
        }

        return $quantities;
    }

    /** @return array<string, int> */
    private function stockRequirements(SuratJalan $suratJalan): array
    {
        if ($suratJalan->jenis !== SuratJalan::JENIS_PENGIRIMAN) {
            return [];
        }

        $requirements = [];

        foreach ($suratJalan->details as $detail) {
            $barangJasa = $detail->detailPo?->barangJasa;

            if ($barangJasa?->tipe !== 'fisik') {
                continue;
            }

            $idbarangjasa = $detail->detailPo?->idbarangjasa;

            if (! is_string($idbarangjasa) || $idbarangjasa === '') {
                continue;
            }

            $requirements[$idbarangjasa] = ($requirements[$idbarangjasa] ?? 0) + (int) $detail->qty;
        }

        return $requirements;
    }

    private function assertJenis(string $jenis): void
    {
        if (! array_key_exists($jenis, SuratJalan::JENIS)) {
            throw ValidationException::withMessages([
                'jenis' => 'Jenis Surat Jalan tidak valid.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function resolveAttn(array $data, Po $po): ?string
    {
        if (array_key_exists('attn', $data)) {
            return $this->nullableString($data['attn']);
        }

        return $this->nullableString($po->customer?->pic);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
