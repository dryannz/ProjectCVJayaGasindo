<?php

namespace Tests\Feature;

use App\Models\Petugas;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_every_work_order_entry_point(): void
    {
        $workOrder = WorkOrder::factory()->ditugaskan()->create();
        $po = $workOrder->po;

        $this->get(route('admin.work-order.index'))->assertRedirect(route('login'));
        $this->get(route('admin.work-order.create', $po))->assertRedirect(route('login'));
        $this->post(route('admin.work-order.store', $po))->assertRedirect(route('login'));
        $this->get(route('admin.work-order.show', $workOrder))->assertRedirect(route('login'));
        $this->get(route('admin.work-order.edit', $workOrder))->assertRedirect(route('login'));
        $this->put(route('admin.work-order.update', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('admin.work-order.assign', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('admin.work-order.cancel', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('admin.work-order.admin-notes', $workOrder))->assertRedirect(route('login'));
        $this->get(route('teknisi.work-order'))->assertRedirect(route('login'));
        $this->get(route('teknisi.work-order.show', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('teknisi.work-order.start', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('teknisi.work-order.notes', $workOrder))->assertRedirect(route('login'));
        $this->patch(route('teknisi.work-order.complete', $workOrder))->assertRedirect(route('login'));
    }

    public function test_admin_sales_can_access_admin_work_order_pages_but_not_technician_pages(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);
        $workOrder = WorkOrder::factory()->create();
        $po = $workOrder->po;

        $this->actingAs($admin)->get(route('admin.work-order.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.work-order.create', $po))->assertOk();
        $this->actingAs($admin)->get(route('admin.work-order.show', $workOrder))->assertOk();
        $this->actingAs($admin)->get(route('admin.work-order.edit', $workOrder))->assertOk();

        $this->actingAs($admin)->get(route('teknisi.work-order'))->assertForbidden();
        $this->actingAs($admin)->get(route('teknisi.work-order.show', $workOrder))->assertForbidden();
        $this->actingAs($admin)->patch(route('teknisi.work-order.start', $workOrder))->assertForbidden();
        $this->actingAs($admin)->patch(route('teknisi.work-order.notes', $workOrder))->assertForbidden();
        $this->actingAs($admin)->patch(route('teknisi.work-order.complete', $workOrder))->assertForbidden();
    }

    public function test_technician_can_access_only_technician_pages_and_is_denied_admin_pages(): void
    {
        $technician = Petugas::factory()->teknisi()->create();
        $workOrder = WorkOrder::factory()->ditugaskan($technician)->create();
        $po = $workOrder->po;

        $this->actingAs($technician)->get(route('teknisi.work-order'))->assertOk();
        $this->actingAs($technician)->get(route('teknisi.work-order.show', $workOrder))->assertOk();
        $this->actingAs($technician)
            ->patch(route('teknisi.work-order.notes', $workOrder), ['catatan_teknisi' => 'Catatan akses teknisi.'])
            ->assertRedirect(route('teknisi.work-order.show', $workOrder));

        $this->actingAs($technician)->get(route('admin.work-order.index'))->assertForbidden();
        $this->actingAs($technician)->get(route('admin.work-order.create', $po))->assertForbidden();
        $this->actingAs($technician)->post(route('admin.work-order.store', $po))->assertForbidden();
        $this->actingAs($technician)->get(route('admin.work-order.show', $workOrder))->assertForbidden();
        $this->actingAs($technician)->get(route('admin.work-order.edit', $workOrder))->assertForbidden();
        $this->actingAs($technician)->put(route('admin.work-order.update', $workOrder))->assertForbidden();
        $this->actingAs($technician)->patch(route('admin.work-order.assign', $workOrder))->assertForbidden();
        $this->actingAs($technician)->patch(route('admin.work-order.cancel', $workOrder))->assertForbidden();
        $this->actingAs($technician)->patch(route('admin.work-order.admin-notes', $workOrder))->assertForbidden();
    }

    public function test_pimpinan_is_denied_from_all_work_order_pages_and_actions(): void
    {
        $pimpinan = Petugas::factory()->create(['role' => 'pimpinan']);
        $workOrder = WorkOrder::factory()->create();
        $po = $workOrder->po;

        $this->actingAs($pimpinan)->get(route('admin.work-order.index'))->assertForbidden();
        $this->actingAs($pimpinan)->get(route('admin.work-order.create', $po))->assertForbidden();
        $this->actingAs($pimpinan)->post(route('admin.work-order.store', $po))->assertForbidden();
        $this->actingAs($pimpinan)->get(route('admin.work-order.show', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->get(route('admin.work-order.edit', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->put(route('admin.work-order.update', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('admin.work-order.assign', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('admin.work-order.cancel', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('admin.work-order.admin-notes', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->get(route('teknisi.work-order'))->assertForbidden();
        $this->actingAs($pimpinan)->get(route('teknisi.work-order.show', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('teknisi.work-order.start', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('teknisi.work-order.notes', $workOrder))->assertForbidden();
        $this->actingAs($pimpinan)->patch(route('teknisi.work-order.complete', $workOrder))->assertForbidden();
    }
}
