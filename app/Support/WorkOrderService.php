<?php

namespace App\Support;

use App\Models\DetailPo;
use App\Models\DetailWorkOrder;
use App\Models\NomorDokumen;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createFromPo(Po $po, array $data): WorkOrder
    {
        return DB::transaction(function () use ($po, $data): WorkOrder {
            $lockedPo = Po::query()
                ->with('customer')
                ->lockForUpdate()
                ->findOrFail($po->getKey());

            $this->assertPoCanCreateWorkOrder($lockedPo);

            $customer = $lockedPo->customer;

            if ($customer === null || ! is_string($customer->kode_singkat) || ! preg_match('/^[A-Z0-9]{2,10}$/', $customer->kode_singkat)) {
                throw ValidationException::withMessages([
                    'kode_singkat' => 'Customer belum memiliki kode singkat yang valid. Isi kode singkat customer sebelum membuat Work Order.',
                ]);
            }

            $items = $this->validateItems($lockedPo, $data['items'] ?? []);
            $workOrder = WorkOrder::create([
                'nomor_wo' => DocumentNumberService::generate(NomorDokumen::JENIS_WORK_ORDER, $customer->kode_singkat),
                'pono' => $lockedPo->pono,
                'idcust' => $lockedPo->idcust,
                'tanggal_terbit' => $data['tanggal_terbit'] ?? now()->toDateString(),
                'lokasi_pekerjaan' => $data['lokasi_pekerjaan'],
                'uraian_pekerjaan' => $data['uraian_pekerjaan'],
                'keluhan_tangki' => $data['keluhan_tangki'] ?? null,
                'status' => WorkOrder::STATUS_MENUNGGU,
            ]);

            $this->storeItems($workOrder, $items);

            return $workOrder->load(['customer', 'po', 'details']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WorkOrder $workOrder, array $data): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()
                ->lockForUpdate()
                ->findOrFail($workOrder->getKey());

            if ($lockedWorkOrder->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Work Order selesai atau dibatalkan tidak dapat diubah. Gunakan catatan admin bila diperlukan.',
                ]);
            }

            $updates = [];

            foreach (['tanggal_terbit', 'lokasi_pekerjaan', 'uraian_pekerjaan', 'keluhan_tangki'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updates[$field] = $data[$field];
                }
            }

            if ($updates !== []) {
                $lockedWorkOrder->update($updates);
            }

            if (array_key_exists('items', $data)) {
                $lockedPo = $lockedWorkOrder->po()->lockForUpdate()->firstOrFail();
                $items = $this->validateItems($lockedPo, $data['items'], $lockedWorkOrder->nomor_wo);
                $lockedWorkOrder->details()->delete();
                $this->storeItems($lockedWorkOrder, $items);
            }

            return $lockedWorkOrder->fresh(['customer', 'po', 'details']);
        });
    }

    public function assignTechnician(WorkOrder $workOrder, string $idpetugas): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $idpetugas): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $technician = Petugas::query()
                ->where('idpetugas', $idpetugas)
                ->where('role', 'teknisi')
                ->where('is_active', true)
                ->first();

            if ($technician === null) {
                throw ValidationException::withMessages([
                    'idpetugas' => 'Teknisi harus memiliki role teknisi dan berstatus aktif.',
                ]);
            }

            if (! in_array($lockedWorkOrder->status, [WorkOrder::STATUS_MENUNGGU, WorkOrder::STATUS_DITUGASKAN], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Teknisi hanya dapat ditugaskan saat Work Order menunggu atau sudah ditugaskan.',
                ]);
            }

            $lockedWorkOrder->update([
                'idpetugas' => $technician->idpetugas,
                'status' => WorkOrder::STATUS_DITUGASKAN,
            ]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    public function start(WorkOrder $workOrder, string $idpetugas, ?string $tanggalMulai = null, ?string $catatanTeknisi = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $idpetugas, $tanggalMulai, $catatanTeknisi): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $this->assertAssignedTechnician($lockedWorkOrder, $idpetugas);

            if ($lockedWorkOrder->status !== WorkOrder::STATUS_DITUGASKAN) {
                throw ValidationException::withMessages([
                    'status' => 'Work Order hanya dapat dimulai dari status ditugaskan.',
                ]);
            }

            $lockedWorkOrder->update([
                'status' => WorkOrder::STATUS_DIKERJAKAN,
                'tanggal_mulai' => $tanggalMulai ?? now()->toDateString(),
                'catatan_teknisi' => $catatanTeknisi ?? $lockedWorkOrder->catatan_teknisi,
            ]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    public function updateTechnicianNotes(WorkOrder $workOrder, string $idpetugas, string $catatanTeknisi): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $idpetugas, $catatanTeknisi): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $this->assertAssignedTechnician($lockedWorkOrder, $idpetugas);

            if (! in_array($lockedWorkOrder->status, [WorkOrder::STATUS_DITUGASKAN, WorkOrder::STATUS_DIKERJAKAN], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Catatan teknisi hanya dapat diubah saat pekerjaan belum selesai atau dibatalkan.',
                ]);
            }

            $lockedWorkOrder->update(['catatan_teknisi' => $catatanTeknisi]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    public function complete(WorkOrder $workOrder, string $idpetugas, string $tanggalSelesai, string $tanggalBast, ?string $catatanTeknisi = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $idpetugas, $tanggalSelesai, $tanggalBast, $catatanTeknisi): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $this->assertAssignedTechnician($lockedWorkOrder, $idpetugas);

            if ($lockedWorkOrder->status !== WorkOrder::STATUS_DIKERJAKAN) {
                throw ValidationException::withMessages([
                    'status' => 'Work Order hanya dapat diselesaikan saat sedang dikerjakan.',
                ]);
            }

            $completedAt = $this->parseDate($tanggalSelesai, 'tanggal_selesai');
            $bastAt = $this->parseDate($tanggalBast, 'tanggal_bast');

            if ($bastAt->isBefore($completedAt)) {
                throw ValidationException::withMessages([
                    'tanggal_bast' => 'Tanggal BAST tidak boleh sebelum tanggal selesai.',
                ]);
            }

            $lockedWorkOrder->update([
                'status' => WorkOrder::STATUS_SELESAI,
                'tanggal_selesai' => $completedAt->toDateString(),
                'tanggal_bast' => $bastAt->toDateString(),
                'catatan_teknisi' => $catatanTeknisi ?? $lockedWorkOrder->catatan_teknisi,
            ]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    public function cancel(WorkOrder $workOrder, string $alasanBatal): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $alasanBatal): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());

            if (! in_array($lockedWorkOrder->status, [WorkOrder::STATUS_MENUNGGU, WorkOrder::STATUS_DITUGASKAN, WorkOrder::STATUS_DIKERJAKAN], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Work Order selesai atau sudah dibatalkan tidak dapat dibatalkan lagi.',
                ]);
            }

            if (trim($alasanBatal) === '') {
                throw ValidationException::withMessages([
                    'alasan_batal' => 'Alasan pembatalan wajib diisi.',
                ]);
            }

            $lockedWorkOrder->update([
                'status' => WorkOrder::STATUS_DIBATALKAN,
                'alasan_batal' => trim($alasanBatal),
            ]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    public function updateAdminNotes(WorkOrder $workOrder, string $catatanAdmin): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $catatanAdmin): WorkOrder {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $lockedWorkOrder->update(['catatan_admin' => $catatanAdmin]);

            return $lockedWorkOrder->fresh(['customer', 'petugas', 'details']);
        });
    }

    private function assertPoCanCreateWorkOrder(Po $po): void
    {
        if (! $po->canCreateWorkOrder()) {
            throw ValidationException::withMessages([
                'pono' => 'Work Order hanya dapat dibuat dari PO berstatus diproses atau selesai.',
            ]);
        }
    }

    /**
     * @return array<int, array{detail_po_id: int, nama_barang: string, qty: int}>
     */
    private function validateItems(Po $po, mixed $submittedItems, ?string $excludeWorkOrder = null): array
    {
        if (! is_array($submittedItems) || $submittedItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu item jasa untuk Work Order.',
            ]);
        }

        $sourceDetails = DetailPo::query()
            ->with('barangJasa')
            ->where('pono', $po->pono)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $items = [];

        foreach (array_values($submittedItems) as $index => $submittedItem) {
            $detailPoId = (int) ($submittedItem['detail_po_id'] ?? 0);
            $qty = (int) ($submittedItem['qty'] ?? 0);
            $detail = $sourceDetails->get($detailPoId);

            if ($detail === null) {
                throw ValidationException::withMessages([
                    "items.{$index}.detail_po_id" => 'Item PO tidak ditemukan pada PO ini.',
                ]);
            }

            if (isset($items[$detailPoId])) {
                throw ValidationException::withMessages([
                    "items.{$index}.detail_po_id" => 'Item PO tidak boleh dipilih dua kali.',
                ]);
            }

            if ($qty < 1) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty" => 'Qty Work Order minimal 1.',
                ]);
            }

            if ($detail->barangJasa?->tipe !== 'jasa') {
                throw ValidationException::withMessages([
                    "items.{$index}.detail_po_id" => 'Work Order hanya dapat dibuat untuk item bertipe jasa.',
                ]);
            }

            $allocatedQty = $this->allocatedQuantity($po, $detailPoId, $excludeWorkOrder);

            if ($allocatedQty + $qty > (int) $detail->qty) {
                throw ValidationException::withMessages([
                    "items.{$index}.qty" => 'Qty melebihi sisa item jasa pada PO. Sisa saat ini: '.max(0, (int) $detail->qty - $allocatedQty).'.',
                ]);
            }

            $items[$detailPoId] = [
                'detail_po_id' => $detailPoId,
                'nama_barang' => $detail->nama_barang,
                'qty' => $qty,
            ];
        }

        return array_values($items);
    }

    private function allocatedQuantity(Po $po, int $detailPoId, ?string $excludeWorkOrder): int
    {
        return (int) DetailWorkOrder::query()
            ->where('detail_po_id', $detailPoId)
            ->whereHas('workOrder', function (Builder $query) use ($po, $excludeWorkOrder): void {
                $query
                    ->where('pono', $po->pono)
                    ->where('status', '!=', WorkOrder::STATUS_DIBATALKAN)
                    ->when($excludeWorkOrder !== null, fn (Builder $query): Builder => $query->where('nomor_wo', '!=', $excludeWorkOrder));
            })
            ->sum('qty');
    }

    /** @param array<int, array{detail_po_id: int, nama_barang: string, qty: int}> $items */
    private function storeItems(WorkOrder $workOrder, array $items): void
    {
        foreach ($items as $item) {
            $workOrder->details()->create($item);
        }
    }

    private function assertAssignedTechnician(WorkOrder $workOrder, string $idpetugas): void
    {
        if ($workOrder->idpetugas !== $idpetugas) {
            throw ValidationException::withMessages([
                'work_order' => 'Work Order ini tidak ditugaskan kepada Anda.',
            ]);
        }
    }

    private function parseDate(string $date, string $field): Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }
}
