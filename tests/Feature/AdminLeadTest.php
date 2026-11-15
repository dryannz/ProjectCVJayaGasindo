<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\LeadItem;
use App\Models\Petugas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_lead_log(): void
    {
        $lead = Lead::factory()->create();

        $this->get(route('admin.leads.index'))->assertRedirect(route('login'));
        $this->get(route('admin.leads.show', $lead))->assertRedirect(route('login'));
        $this->patch(route('admin.leads.update', $lead), ['status' => 'dihubungi'])->assertRedirect(route('login'));
    }

    public function test_non_admin_staff_cannot_open_lead_log(): void
    {
        $lead = Lead::factory()->create();
        $teknisi = Petugas::factory()->create(['role' => 'teknisi']);

        $this->actingAs($teknisi)->get(route('admin.leads.index'))->assertForbidden();
        $this->actingAs($teknisi)->get(route('admin.leads.show', $lead))->assertForbidden();
        $this->actingAs($teknisi)->patch(route('admin.leads.update', $lead), ['status' => 'batal'])->assertForbidden();
    }

    public function test_admin_sees_leads_and_can_filter_by_status_and_keyword(): void
    {
        $admin = Petugas::factory()->create();
        Lead::factory()->create(['nama' => 'Pelanggan Baru Satu']);
        Lead::factory()->status('dihubungi')->create(['nama' => 'Pelanggan Sudah Dihubungi']);

        $this->actingAs($admin)->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('Pelanggan Baru Satu')
            ->assertSee('Pelanggan Sudah Dihubungi');

        $this->actingAs($admin)->get(route('admin.leads.index', ['status' => 'baru']))
            ->assertSee('Pelanggan Baru Satu')
            ->assertDontSee('Pelanggan Sudah Dihubungi');

        $this->actingAs($admin)->get(route('admin.leads.index', ['q' => 'Sudah Dihubungi']))
            ->assertSee('Pelanggan Sudah Dihubungi')
            ->assertDontSee('Pelanggan Baru Satu');
    }

    public function test_admin_can_view_lead_detail_with_items(): void
    {
        $admin = Petugas::factory()->create();
        $lead = Lead::factory()->create(['nama' => 'Siti Aminah']);
        LeadItem::create([
            'lead_id' => $lead->id,
            'idbarangjasa' => 'BRG-0001',
            'nama_barang' => 'Tabung Argon',
            'tipe' => 'fisik',
            'unit' => 'tabung',
            'label_harga' => 'Harga sesuai permintaan',
            'qty' => 4,
        ]);

        $this->actingAs($admin)->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('Tabung Argon')
            ->assertSee($lead->kode);
    }

    public function test_admin_can_update_status_and_internal_note(): void
    {
        $admin = Petugas::factory()->create();
        $lead = Lead::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.leads.update', $lead), [
                'status' => 'dihubungi',
                'catatan_internal' => 'Sudah ditelepon, minta survei Kamis.',
            ])
            ->assertRedirect(route('admin.leads.show', $lead));

        $lead->refresh();

        $this->assertSame('dihubungi', $lead->status);
        $this->assertSame('Sudah ditelepon, minta survei Kamis.', $lead->catatan_internal);
        $this->assertSame($admin->idpetugas, $lead->ditangani_oleh);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $admin = Petugas::factory()->create();
        $lead = Lead::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.leads.update', $lead), ['status' => 'selesai-banget'])
            ->assertSessionHasErrors('status');

        $this->assertSame('baru', $lead->refresh()->status);
    }

    public function test_dashboard_shows_new_lead_count(): void
    {
        $admin = Petugas::factory()->create();
        Lead::factory()->count(3)->sequence(
            ['kode' => 'RFQ-20261116-0001'],
            ['kode' => 'RFQ-20261116-0002'],
            ['kode' => 'RFQ-20261116-0003'],
        )->create();
        Lead::factory()->status('batal')->create(['kode' => 'RFQ-20261116-0004']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('3 <span', false)
            ->assertSee('baru dari 4 lead');
    }
}
