<?php

namespace Tests\Feature;

use App\Models\Petugas;
use App\Models\Po;
use App\Models\SuratJalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratJalanAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_every_surat_jalan_entry_point(): void
    {
        [$po, $suratJalan] = $this->documents();

        $this->get(route('admin.surat-jalan.index'))->assertRedirect(route('login'));
        $this->get(route('admin.surat-jalan.create', $po))->assertRedirect(route('login'));
        $this->post(route('admin.surat-jalan.store', $po))->assertRedirect(route('login'));
        $this->get(route('admin.surat-jalan.show', $suratJalan))->assertRedirect(route('login'));
        $this->patch(route('admin.surat-jalan.publish', $suratJalan))->assertRedirect(route('login'));
        $this->patch(route('admin.surat-jalan.receive', $suratJalan))->assertRedirect(route('login'));
        $this->patch(route('admin.surat-jalan.cancel', $suratJalan))->assertRedirect(route('login'));
        $this->patch(route('admin.surat-jalan.notes', $suratJalan))->assertRedirect(route('login'));
    }

    public function test_admin_sales_can_access_surat_jalan_pages_but_other_roles_are_denied(): void
    {
        [$po, $suratJalan] = $this->documents();
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($admin)->get(route('admin.surat-jalan.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.surat-jalan.create', $po))->assertOk();
        $this->actingAs($admin)->get(route('admin.surat-jalan.show', $suratJalan))->assertOk();

        foreach (['teknisi', 'pimpinan'] as $role) {
            $petugas = Petugas::factory()->create(['role' => $role]);

            $this->actingAs($petugas)->get(route('admin.surat-jalan.index'))->assertForbidden();
            $this->actingAs($petugas)->get(route('admin.surat-jalan.create', $po))->assertForbidden();
            $this->actingAs($petugas)->post(route('admin.surat-jalan.store', $po))->assertForbidden();
            $this->actingAs($petugas)->get(route('admin.surat-jalan.show', $suratJalan))->assertForbidden();
            $this->actingAs($petugas)->patch(route('admin.surat-jalan.publish', $suratJalan))->assertForbidden();
            $this->actingAs($petugas)->patch(route('admin.surat-jalan.receive', $suratJalan))->assertForbidden();
            $this->actingAs($petugas)->patch(route('admin.surat-jalan.cancel', $suratJalan))->assertForbidden();
            $this->actingAs($petugas)->patch(route('admin.surat-jalan.notes', $suratJalan))->assertForbidden();
        }
    }

    /** @return array{0: Po, 1: SuratJalan} */
    private function documents(): array
    {
        $po = Po::factory()->diproses()->create();
        $suratJalan = SuratJalan::factory()->forPo($po)->create();

        return [$po, $suratJalan];
    }
}
