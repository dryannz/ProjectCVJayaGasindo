<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\DetailPo;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\SuratJalan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratJalanHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_publish_receive_note_and_cancel_surat_jalan(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $product = BarangJasa::factory()->create(['stok' => 5]);
        $po = Po::factory()->diproses()->create(['idcust' => $customer->idcust]);
        $detail = DetailPo::factory()->create([
            'pono' => $po->pono,
            'idbarangjasa' => $product->idbarangjasa,
            'qty' => 2,
            'nama_barang' => $product->nama_barang,
            'unit' => $product->unit,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.surat-jalan.create', $po))
            ->assertOk()
            ->assertSee('Buat Surat Jalan')
            ->assertSee($detail->nama_barang);

        $this->actingAs($admin)
            ->post(route('admin.surat-jalan.store', $po), [
                'tgl' => now()->toDateString(),
                'attn' => $customer->pic,
                'rev' => 'Rev - 00',
                'mobil' => 'B 1234 CD',
                'jenis' => SuratJalan::JENIS_PENGIRIMAN,
                'items' => [
                    $detail->id => [
                        'selected' => '1',
                        'detail_po_id' => $detail->id,
                        'qty' => 2,
                    ],
                ],
            ])
            ->assertRedirect();

        $suratJalan = SuratJalan::query()->sole();

        $this->actingAs($admin)
            ->get(route('admin.surat-jalan.show', $suratJalan))
            ->assertOk()
            ->assertSee($suratJalan->nosuratjalan)
            ->assertSee($detail->nama_barang);

        $this->actingAs($admin)
            ->patch(route('admin.surat-jalan.publish', $suratJalan))
            ->assertRedirect(route('admin.surat-jalan.show', $suratJalan));

        $this->assertSame(SuratJalan::STATUS_DITERBITKAN, $suratJalan->fresh()->status);
        $this->assertSame(3, (int) $product->fresh()->stok);

        $this->actingAs($admin)
            ->patch(route('admin.surat-jalan.receive', $suratJalan), [
                'nama_penerima' => 'Budi Penerima',
                'tanggal_diterima' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.surat-jalan.show', $suratJalan));

        $this->actingAs($admin)
            ->patch(route('admin.surat-jalan.notes', $suratJalan), ['catatan' => 'Diterima tanpa catatan tambahan.'])
            ->assertRedirect(route('admin.surat-jalan.show', $suratJalan));

        $this->assertSame('Diterima tanpa catatan tambahan.', $suratJalan->fresh()->catatan);

        $this->actingAs($admin)
            ->patch(route('admin.surat-jalan.cancel', $suratJalan), ['alasan_batal' => 'Pembatalan koreksi dokumen.'])
            ->assertRedirect(route('admin.surat-jalan.show', $suratJalan));

        $this->assertSame(SuratJalan::STATUS_DIBATALKAN, $suratJalan->fresh()->status);
        $this->assertSame(5, (int) $product->fresh()->stok);
    }

    public function test_store_rejects_an_unselected_item_payload_without_items(): void
    {
        $admin = Petugas::factory()->create(['role' => 'admin_sales']);
        $po = Po::factory()->diproses()->create();

        $this->actingAs($admin)
            ->from(route('admin.surat-jalan.create', $po))
            ->post(route('admin.surat-jalan.store', $po), [
                'tgl' => now()->toDateString(),
                'rev' => 'Rev - 00',
                'jenis' => SuratJalan::JENIS_PENGIRIMAN,
                'items' => [],
            ])
            ->assertRedirect(route('admin.surat-jalan.create', $po))
            ->assertSessionHasErrors('items');
    }
}
