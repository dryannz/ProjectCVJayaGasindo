<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\DetailPo;
use App\Models\NomorDokumen;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\WorkOrder;
use App\Support\DocumentNumberService;
use App\Support\WorkOrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    private WorkOrderService $workOrderService;

    private Petugas $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workOrderService = app(WorkOrderService::class);
        $this->admin = Petugas::factory()->create(['role' => 'admin_sales']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_work_order_number_uses_customer_code_and_roman_month(): void
    {
        Carbon::setTestNow('2026-11-16');
        NomorDokumen::create([
            'jenis' => NomorDokumen::JENIS_WORK_ORDER,
            'periode' => '2026-11',
            'terakhir' => 8,
        ]);

        $this->assertSame('XI', DocumentNumberService::romanMonth(11));
        $this->assertSame(
            '11-009/ MJGE-PPKS/ WO/ XI/ 2026',
            DocumentNumberService::generate(NomorDokumen::JENIS_WORK_ORDER, 'PPKS'),
        );
    }

    public function test_work_order_requires_valid_customer_code_and_processed_po(): void
    {
        $customer = Customer::factory()->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES);

        try {
            $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail']));
            $this->fail('Expected a validation exception for a missing customer code.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('kode_singkat', $exception->errors());
        }

        $codedCustomer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $draftPoData = $this->poWithService($codedCustomer, Po::STATUS_DRAFT);

        $this->expectException(ValidationException::class);
        $this->workOrderService->createFromPo($draftPoData['po'], $this->workOrderPayload($draftPoData['detail']));
    }

    public function test_work_order_accepts_only_service_items_and_enforces_remaining_quantity(): void
    {
        $customer = Customer::factory()->withKodeSingkat('PPKS')->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES, 3);
        $physical = BarangJasa::factory()->fisik()->create(['nama_barang' => 'Tabung fisik']);
        $physicalDetail = DetailPo::create([
            'idbarangjasa' => $physical->idbarangjasa,
            'pono' => $poData['po']->pono,
            'qty' => 1,
            'amount' => 100000,
            'nama_barang' => $physical->nama_barang,
            'unit' => $physical->unit,
            'harga_satuan' => 100000,
        ]);

        try {
            $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($physicalDetail));
            $this->fail('Expected a validation exception for a physical item.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items.0.detail_po_id', $exception->errors());
        }

        $workOrder = $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail'], 2));

        $this->assertSame(WorkOrder::STATUS_MENUNGGU, $workOrder->status);
        $this->assertSame($poData['detail']->nama_barang, $workOrder->details->sole()->nama_barang);
        $this->assertSame(2, $workOrder->details->sole()->qty);
        $this->assertArrayNotHasKey('harga_satuan', $workOrder->details->sole()->getAttributes());

        $this->expectException(ValidationException::class);
        $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail'], 2));
    }

    public function test_work_order_status_flow_requires_active_technician_and_locks_completed_order(): void
    {
        $customer = Customer::factory()->withKodeSingkat('PPKS')->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES);
        $workOrder = $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail']));
        $inactiveTechnician = Petugas::factory()->teknisi()->nonaktif()->create();

        $this->expectException(ValidationException::class);
        $this->workOrderService->assignTechnician($workOrder, $inactiveTechnician->idpetugas);
    }

    public function test_assigned_technician_can_start_complete_and_update_notes(): void
    {
        $customer = Customer::factory()->withKodeSingkat('PPKS')->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES);
        $workOrder = $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail']));
        $technician = Petugas::factory()->teknisi()->create();

        $assigned = $this->workOrderService->assignTechnician($workOrder, $technician->idpetugas);
        $this->assertSame(WorkOrder::STATUS_DITUGASKAN, $assigned->status);

        $started = $this->workOrderService->start($assigned, $technician->idpetugas, '2026-11-17', 'Tiba di lokasi.');
        $this->assertSame(WorkOrder::STATUS_DIKERJAKAN, $started->status);
        $this->assertSame('Tiba di lokasi.', $started->catatan_teknisi);

        $updatedNotes = $this->workOrderService->updateTechnicianNotes($started, $technician->idpetugas, 'Pekerjaan berjalan.');
        $this->assertSame('Pekerjaan berjalan.', $updatedNotes->catatan_teknisi);

        $completed = $this->workOrderService->complete($updatedNotes, $technician->idpetugas, '2026-11-18', '2026-11-18', 'Pekerjaan selesai.');
        $this->assertSame(WorkOrder::STATUS_SELESAI, $completed->status);
        $this->assertSame('2026-11-18', $completed->tanggal_selesai->toDateString());
        $this->assertSame('2026-11-18', $completed->tanggal_bast->toDateString());

        $this->expectException(ValidationException::class);
        $this->workOrderService->update($completed, ['lokasi_pekerjaan' => 'Lokasi baru']);
    }

    public function test_wrong_technician_and_invalid_start_transition_are_rejected(): void
    {
        $customer = Customer::factory()->withKodeSingkat('PPKS')->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES);
        $workOrder = $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail']));
        $assignedTechnician = Petugas::factory()->teknisi()->create();
        $otherTechnician = Petugas::factory()->teknisi()->create();
        $assigned = $this->workOrderService->assignTechnician($workOrder, $assignedTechnician->idpetugas);

        try {
            $this->workOrderService->start($assigned, $otherTechnician->idpetugas);
            $this->fail('Expected a validation exception for the wrong technician.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('work_order', $exception->errors());
        }

        $started = $this->workOrderService->start($assigned, $assignedTechnician->idpetugas);

        $this->expectException(ValidationException::class);
        $this->workOrderService->start($started, $assignedTechnician->idpetugas);
    }

    public function test_cancel_requires_reason_and_admin_notes_remain_editable(): void
    {
        $customer = Customer::factory()->withKodeSingkat('PPKS')->create();
        $poData = $this->poWithService($customer, Po::STATUS_DIPROSES);
        $workOrder = $this->workOrderService->createFromPo($poData['po'], $this->workOrderPayload($poData['detail']));

        try {
            $this->workOrderService->cancel($workOrder, ' ');
            $this->fail('Expected a validation exception for an empty cancellation reason.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('alasan_batal', $exception->errors());
        }

        $cancelled = $this->workOrderService->cancel($workOrder, 'Jadwal customer berubah.');
        $this->assertSame(WorkOrder::STATUS_DIBATALKAN, $cancelled->status);

        $updated = $this->workOrderService->updateAdminNotes($cancelled, 'Dicatat untuk tindak lanjut.');
        $this->assertSame('Dicatat untuk tindak lanjut.', $updated->catatan_admin);
    }

    /** @return array{po: Po, detail: DetailPo} */
    private function poWithService(Customer $customer, string $status, int $qty = 1): array
    {
        $po = Po::factory()->create([
            'idcust' => $customer->idcust,
            'idpetugas' => $this->admin->idpetugas,
            'status' => $status,
        ]);
        $service = BarangJasa::factory()->jasa()->create(['nama_barang' => 'Instalasi pipa gas']);
        $detail = DetailPo::create([
            'idbarangjasa' => $service->idbarangjasa,
            'pono' => $po->pono,
            'qty' => $qty,
            'amount' => 500000,
            'nama_barang' => $service->nama_barang,
            'unit' => $service->unit,
            'harga_satuan' => 500000,
        ]);

        return ['po' => $po, 'detail' => $detail];
    }

    /** @return array{items: array<int, array{detail_po_id: int, qty: int}>, lokasi_pekerjaan: string, uraian_pekerjaan: string, keluhan_tangki: string} */
    private function workOrderPayload(DetailPo $detail, int $qty = 1): array
    {
        return [
            'items' => [[
                'detail_po_id' => $detail->id,
                'qty' => $qty,
            ]],
            'lokasi_pekerjaan' => 'Bekasi',
            'uraian_pekerjaan' => 'Instalasi jalur gas.',
            'keluhan_tangki' => 'Tidak ada keluhan.',
        ];
    }
}
