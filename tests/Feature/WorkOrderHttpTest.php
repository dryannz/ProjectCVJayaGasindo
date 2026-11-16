<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\DetailPo;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\WorkOrder;
use App\Support\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_wrong_roles_cannot_open_admin_work_orders(): void
    {
        $this->get(route('admin.work-order.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(Petugas::factory()->teknisi()->create())
            ->get(route('admin.work-order.index'))
            ->assertForbidden();

        $this->actingAs(Petugas::factory()->create(['role' => 'pimpinan']))
            ->get(route('admin.work-order.index'))
            ->assertForbidden();

        $this->actingAs(Petugas::factory()->create(['role' => 'pimpinan']))
            ->get(route('teknisi.work-order'))
            ->assertForbidden();
    }

    public function test_admin_can_open_po_work_order_form_and_create_work_order(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithService($customer);

        $this->actingAs($admin)
            ->get(route('admin.work-order.create', $poData['po']))
            ->assertOk()
            ->assertSee($poData['detail']->nama_barang)
            ->assertSee('Buat Work Order');

        $this->actingAs($admin)
            ->post(route('admin.work-order.store', $poData['po']), [
                'tanggal_terbit' => '2026-11-16',
                'lokasi_pekerjaan' => 'Lokasi customer',
                'uraian_pekerjaan' => 'Pemeriksaan dan perbaikan tangki.',
                'keluhan_tangki' => 'Tekanan turun.',
                'items' => [
                    ['detail_po_id' => $poData['detail']->id, 'qty' => 1],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('work_order', [
            'pono' => $poData['po']->pono,
            'idcust' => $customer->idcust,
            'status' => WorkOrder::STATUS_MENUNGGU,
        ]);
    }

    public function test_technician_only_sees_assigned_work_orders_without_financial_fields(): void
    {
        $technician = Petugas::factory()->teknisi()->create();
        $otherTechnician = Petugas::factory()->teknisi()->create();
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithService($customer);
        $workOrder = app(WorkOrderService::class)->createFromPo($poData['po'], [
            'tanggal_terbit' => '2026-11-16',
            'lokasi_pekerjaan' => 'Lokasi customer',
            'uraian_pekerjaan' => 'Pemeriksaan dan perbaikan tangki.',
            'items' => [['detail_po_id' => $poData['detail']->id, 'qty' => 1]],
        ]);
        app(WorkOrderService::class)->assignTechnician($workOrder, $technician->idpetugas);

        $this->actingAs($technician)
            ->get(route('teknisi.work-order'))
            ->assertOk()
            ->assertSee($workOrder->nomor_wo)
            ->assertSee($customer->nama_pt)
            ->assertDontSee('harga_satuan')
            ->assertDontSee('subtotal')
            ->assertDontSee('ppn')
            ->assertDontSee('Rp');

        $this->actingAs($otherTechnician)
            ->get(route('teknisi.work-order.show', $workOrder))
            ->assertNotFound();
    }

    public function test_technician_can_start_and_complete_an_assigned_work_order(): void
    {
        $technician = Petugas::factory()->teknisi()->create();
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithService($customer);
        $workOrderService = app(WorkOrderService::class);
        $workOrder = $workOrderService->createFromPo($poData['po'], [
            'tanggal_terbit' => '2026-11-16',
            'lokasi_pekerjaan' => 'Lokasi customer',
            'uraian_pekerjaan' => 'Pemeriksaan dan perbaikan tangki.',
            'items' => [['detail_po_id' => $poData['detail']->id, 'qty' => 1]],
        ]);
        $workOrderService->assignTechnician($workOrder, $technician->idpetugas);

        $this->actingAs($technician)
            ->patch(route('teknisi.work-order.start', $workOrder), [
                'tanggal_mulai' => '2026-11-16',
                'catatan_teknisi' => 'Pekerjaan dimulai.',
            ])
            ->assertRedirect(route('teknisi.work-order.show', $workOrder));

        $this->actingAs($technician)
            ->patch(route('teknisi.work-order.complete', $workOrder), [
                'tanggal_selesai' => '2026-11-17',
                'tanggal_bast' => '2026-11-17',
                'catatan_teknisi' => 'Pekerjaan selesai.',
            ])
            ->assertRedirect(route('teknisi.work-order.show', $workOrder));

        $completedWorkOrder = WorkOrder::query()->findOrFail($workOrder->nomor_wo);

        $this->assertSame(WorkOrder::STATUS_SELESAI, $completedWorkOrder->status);
        $this->assertSame('2026-11-17', $completedWorkOrder->tanggal_selesai?->format('Y-m-d'));
        $this->assertSame('2026-11-17', $completedWorkOrder->tanggal_bast?->format('Y-m-d'));
    }

    public function test_admin_can_filter_assign_and_cancel_a_work_order(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);
        $technician = Petugas::factory()->teknisi()->create();
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithService($customer);
        $workOrder = app(WorkOrderService::class)->createFromPo($poData['po'], [
            'tanggal_terbit' => '2026-11-16',
            'lokasi_pekerjaan' => 'Lokasi customer',
            'uraian_pekerjaan' => 'Pemeriksaan dan perbaikan tangki.',
            'items' => [['detail_po_id' => $poData['detail']->id, 'qty' => 1]],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.work-order.index', ['q' => $workOrder->nomor_wo, 'status' => WorkOrder::STATUS_MENUNGGU]))
            ->assertOk()
            ->assertSee($workOrder->nomor_wo);

        $this->actingAs($admin)
            ->patch(route('admin.work-order.assign', $workOrder), ['idpetugas' => $technician->idpetugas])
            ->assertRedirect(route('admin.work-order.show', $workOrder));

        $this->assertDatabaseHas('work_order', [
            'nomor_wo' => $workOrder->nomor_wo,
            'idpetugas' => $technician->idpetugas,
            'status' => WorkOrder::STATUS_DITUGASKAN,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.work-order.cancel', $workOrder), ['alasan_batal' => 'Customer menunda pekerjaan.'])
            ->assertRedirect(route('admin.work-order.show', $workOrder));

        $this->assertDatabaseHas('work_order', [
            'nomor_wo' => $workOrder->nomor_wo,
            'status' => WorkOrder::STATUS_DIBATALKAN,
            'alasan_batal' => 'Customer menunda pekerjaan.',
        ]);
    }

    public function test_customer_code_is_normalized_and_must_be_unique(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($admin)
            ->post(route('admin.customer.store'), [
                'nama_pt' => 'PT Satu',
                'kode_singkat' => 's1',
                'notelp_pt' => '02112345678',
                'alamat_pt' => 'Jakarta',
                'pic' => 'PIC Satu',
            ])
            ->assertRedirect(route('admin.customer.index'));

        $this->assertDatabaseHas('customer', ['kode_singkat' => 'S1']);

        $secondCustomer = Customer::factory()->withKodeSingkat('S2')->create();

        $this->actingAs($admin)
            ->put(route('admin.customer.update', $secondCustomer), [
                'nama_pt' => $secondCustomer->nama_pt,
                'kode_singkat' => 'S1',
                'notelp_pt' => $secondCustomer->notelp_pt,
                'alamat_pt' => $secondCustomer->alamat_pt,
                'pic' => $secondCustomer->pic,
            ])
            ->assertSessionHasErrors('kode_singkat');

        $this->actingAs($admin)
            ->post(route('admin.customer.store'), [
                'nama_pt' => 'PT Dua',
                'kode_singkat' => 'S1',
                'notelp_pt' => '02112345679',
                'alamat_pt' => 'Jakarta',
            ])
            ->assertSessionHasErrors('kode_singkat');
    }

    /**
     * @return array{po: Po, detail: DetailPo}
     */
    private function poWithService(Customer $customer): array
    {
        $po = Po::factory()->diproses()->create([
            'idcust' => $customer->idcust,
        ]);
        $barangJasa = BarangJasa::factory()->jasa()->create([
            'nama_barang' => 'Pemeriksaan tangki',
            'unit' => 'pekerjaan',
        ]);
        $detail = DetailPo::create([
            'idbarangjasa' => $barangJasa->idbarangjasa,
            'pono' => $po->pono,
            'qty' => 2,
            'amount' => 200000,
            'nama_barang' => $barangJasa->nama_barang,
            'unit' => $barangJasa->unit,
            'harga_satuan' => 100000,
        ]);

        return ['po' => $po, 'detail' => $detail];
    }
}
