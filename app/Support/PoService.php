<?php

namespace App\Support;

use App\Models\NomorDokumen;
use App\Models\Po;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PoService
{
    /** @param array<string, mixed> $data */
    public function createFromQuotation(Quotation $quotation, array $data, string $idpetugas): Po
    {
        return DB::transaction(function () use ($quotation, $data, $idpetugas): Po {
            $lockedQuotation = Quotation::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($quotation->getKey());

            $this->assertQuotationCanCreatePo($lockedQuotation);
            $items = $this->snapshotItems($lockedQuotation, $data['items'] ?? []);
            $totals = self::calculateTotals(
                $items,
                (int) ($data['diskon'] ?? 0),
                (float) ($data['ppn_persen'] ?? 0),
            );

            $po = Po::create([
                'pono' => DocumentNumberService::generate(NomorDokumen::JENIS_PO),
                'idcust' => $lockedQuotation->idcust,
                'tgl_order' => $data['tgl_order'],
                'schedule_delivery' => $data['schedule_delivery'],
                'term_of_payment' => $data['term_of_payment'],
                'project_name' => $data['project_name'] ?? null,
                'project_location' => $data['project_location'] ?? null,
                'quotation_id' => $lockedQuotation->id,
                'idpetugas' => $idpetugas,
                'no_po_customer' => $data['no_po_customer'] ?? null,
                'status' => Po::STATUS_DRAFT,
                'catatan' => $data['catatan'] ?? null,
                ...$totals,
            ]);

            $this->storeDetails($po, $items);

            return $po->load(['customer', 'quotation', 'details']);
        });
    }

    /** @param array<string, mixed> $data */
    public function updateDraft(Po $po, array $data): Po
    {
        return DB::transaction(function () use ($po, $data): Po {
            $lockedPo = Po::query()->with(['quotation.details'])->lockForUpdate()->findOrFail($po->getKey());

            if (! $lockedPo->isEditable()) {
                throw ValidationException::withMessages([
                    'status' => 'PO yang sudah diproses tidak dapat diubah.',
                ]);
            }

            if ($lockedPo->quotation === null) {
                throw ValidationException::withMessages([
                    'quotation_id' => 'PO ini tidak memiliki Quotation asal.',
                ]);
            }

            $items = $this->snapshotItems($lockedPo->quotation, $data['items'] ?? []);
            $totals = self::calculateTotals(
                $items,
                (int) ($data['diskon'] ?? 0),
                (float) ($data['ppn_persen'] ?? 0),
            );

            $lockedPo->update([
                'tgl_order' => $data['tgl_order'],
                'schedule_delivery' => $data['schedule_delivery'],
                'term_of_payment' => $data['term_of_payment'],
                'project_name' => $data['project_name'] ?? null,
                'project_location' => $data['project_location'] ?? null,
                'no_po_customer' => $data['no_po_customer'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                ...$totals,
            ]);
            $lockedPo->details()->delete();
            $this->storeDetails($lockedPo, $items);

            return $lockedPo->fresh(['customer', 'quotation', 'details']);
        });
    }

    public function changeStatus(Po $po, string $status, string $idpetugas): Po
    {
        return DB::transaction(function () use ($po, $status, $idpetugas): Po {
            $lockedPo = Po::query()->with(['details', 'quotation'])->lockForUpdate()->findOrFail($po->getKey());

            if (! $lockedPo->canTransitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => sprintf('Perubahan status dari %s ke %s tidak valid.', $lockedPo->label_status, Po::STATUSES[$status] ?? $status),
                ]);
            }

            if ($status === Po::STATUS_BATAL && $lockedPo->hasActiveSuratJalan()) {
                throw ValidationException::withMessages([
                    'status' => 'PO tidak dapat dibatalkan selama masih memiliki Surat Jalan aktif. Batalkan Surat Jalan terlebih dahulu.',
                ]);
            }

            $lockedPo->update([
                'status' => $status,
                'idpetugas' => $idpetugas,
            ]);

            return $lockedPo->fresh(['customer', 'quotation', 'details']);
        });
    }

    /** @param array<int, array<string, mixed>> $items */
    public static function calculateTotals(array $items, int $diskon, float $ppnPersen): array
    {
        $subtotal = array_sum(array_map(
            fn (array $item): int => (int) $item['qty'] * (int) $item['harga_satuan'],
            $items,
        ));
        $diskon = max(0, min($diskon, $subtotal));
        $dasarPajak = $subtotal - $diskon;
        $ppn = (int) round($dasarPajak * $ppnPersen / 100);

        return [
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'total' => $dasarPajak + $ppn,
        ];
    }

    private function assertQuotationCanCreatePo(Quotation $quotation): void
    {
        if ($quotation->status !== Quotation::STATUS_DISETUJUI) {
            throw ValidationException::withMessages([
                'quotation_id' => 'PO hanya dapat dibuat dari Quotation yang sudah disetujui.',
            ]);
        }

        if ($quotation->po()->exists()) {
            throw ValidationException::withMessages([
                'quotation_id' => 'Quotation ini sudah memiliki PO.',
            ]);
        }
    }

    /** @param array<int, array<string, mixed>> $submittedItems @return array<int, array<string, mixed>> */
    private function snapshotItems(Quotation $quotation, array $submittedItems): array
    {
        $sourceItems = $quotation->details->values();

        if ($sourceItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Quotation belum memiliki item.',
            ]);
        }

        $items = [];

        foreach ($sourceItems as $index => $sourceItem) {
            $submittedItem = $submittedItems[$index] ?? [];
            $qty = (int) ($submittedItem['qty'] ?? $sourceItem->qty);
            $hargaSatuan = (int) ($submittedItem['harga_satuan'] ?? $sourceItem->harga_satuan);

            if ($qty < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty" => 'Qty item minimal 1.',
                ]);
            }

            if ($hargaSatuan < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.harga_satuan" => 'Harga satuan tidak boleh kurang dari 0.',
                ]);
            }

            $items[] = [
                'idbarangjasa' => $sourceItem->idbarangjasa,
                'nama_barang' => $sourceItem->nama_barang,
                'tipe' => $sourceItem->tipe,
                'unit' => $sourceItem->unit,
                'qty' => $qty,
                'harga_satuan' => $hargaSatuan,
            ];
        }

        return $items;
    }

    /** @param array<int, array<string, mixed>> $items */
    private function storeDetails(Po $po, array $items): void
    {
        foreach ($items as $item) {
            $po->details()->create([
                'idbarangjasa' => $item['idbarangjasa'],
                'nama_barang' => $item['nama_barang'],
                'unit' => $item['unit'],
                'qty' => $item['qty'],
                'harga_satuan' => $item['harga_satuan'],
                'amount' => $item['qty'] * $item['harga_satuan'],
            ]);
        }
    }
}
