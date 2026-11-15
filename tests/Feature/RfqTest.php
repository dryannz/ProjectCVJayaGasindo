<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfqTest extends TestCase
{
    use RefreshDatabase;

    private function isiDataPemohon(array $override = []): array
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'perusahaan' => 'PT Maju Bersama',
            'telepon' => '081234567890',
            'email' => 'budi@example.com',
            'alamat' => 'Kawasan Industri Jababeka, Cikarang',
            'catatan' => 'Butuh minggu depan',
        ], $override);
    }

    public function test_guest_can_add_active_product_to_rfq_cart(): void
    {
        $produk = BarangJasa::factory()->create(['nama_barang' => 'Tabung Oksigen 6m3']);

        $this->post(route('rfq.add', $produk), ['qty' => 3])->assertRedirect();

        $this->get(route('rfq.index'))
            ->assertOk()
            ->assertSee('Tabung Oksigen 6m3')
            ->assertSee('value="3"', false);
    }

    public function test_adding_same_product_twice_accumulates_quantity(): void
    {
        $produk = BarangJasa::factory()->create();

        $this->post(route('rfq.add', $produk), ['qty' => 2]);
        $this->post(route('rfq.add', $produk), ['qty' => 4]);

        $this->assertSame([$produk->idbarangjasa => 6], session('rfq.cart'));
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $produk = BarangJasa::factory()->nonaktif()->create();

        $this->post(route('rfq.add', $produk))->assertNotFound();
    }

    public function test_product_deactivated_after_being_added_is_dropped_from_cart(): void
    {
        $produk = BarangJasa::factory()->create(['nama_barang' => 'Regulator Lama']);
        $this->post(route('rfq.add', $produk));

        $produk->update(['is_active' => false]);

        $this->get(route('rfq.index'))
            ->assertOk()
            ->assertDontSee(route('katalog.produk', $produk), false)
            ->assertSee('Keranjang kosong');
    }

    public function test_quantity_can_be_updated_and_item_removed(): void
    {
        $produk = BarangJasa::factory()->create();
        $this->post(route('rfq.add', $produk));

        $this->patch(route('rfq.update', $produk), ['qty' => 12])->assertRedirect(route('rfq.index'));
        $this->assertSame(12, session('rfq.cart')[$produk->idbarangjasa]);

        $this->delete(route('rfq.remove', $produk))->assertRedirect(route('rfq.index'));
        $this->assertSame([], session('rfq.cart'));
    }

    public function test_quantity_must_be_a_positive_integer(): void
    {
        $produk = BarangJasa::factory()->create();

        $this->post(route('rfq.add', $produk), ['qty' => 0])->assertSessionHasErrors('qty');
        $this->post(route('rfq.add', $produk), ['qty' => 'banyak'])->assertSessionHasErrors('qty');
    }

    public function test_cart_does_not_expose_private_price_values(): void
    {
        $produk = BarangJasa::factory()->rfq()->create(['price' => 765000]);
        $this->post(route('rfq.add', $produk));

        $this->get(route('rfq.index'))
            ->assertOk()
            ->assertSee('Harga sesuai permintaan')
            ->assertDontSee('765.000');
    }

    public function test_submitting_rfq_stores_lead_with_item_snapshot_and_redirects_to_whatsapp(): void
    {
        config(['perusahaan.whatsapp' => '0812-3456-7890']);

        $fisik = BarangJasa::factory()->create(['nama_barang' => 'Selang Gas', 'unit' => 'meter']);
        $jasa = BarangJasa::factory()->jasa()->rfq()->create(['nama_barang' => 'Instalasi Pipa Gas']);

        $this->post(route('rfq.add', $fisik), ['qty' => 5]);
        $this->post(route('rfq.add', $jasa));

        $response = $this->post(route('rfq.store'), $this->isiDataPemohon());

        $lead = Lead::query()->with('items')->firstOrFail();

        $this->assertSame('campuran', $lead->jenis);
        $this->assertSame('baru', $lead->status);
        $this->assertMatchesRegularExpression('/^RFQ-\d{8}-0001$/', $lead->kode);
        $this->assertCount(2, $lead->items);
        $this->assertSame(5, $lead->items->firstWhere('idbarangjasa', $fisik->idbarangjasa)->qty);
        $this->assertStringContainsString('Selang Gas', $lead->pesan_whatsapp);
        $this->assertStringContainsString($lead->kode, $lead->pesan_whatsapp);

        $response->assertRedirectContains('https://wa.me/6281234567890?text=');
        $this->assertNull(session('rfq.cart'));
    }

    public function test_lead_is_still_saved_when_company_whatsapp_is_not_configured(): void
    {
        config(['perusahaan.whatsapp' => 'ISI DENGAN DATA IKM']);

        $produk = BarangJasa::factory()->create();
        $this->post(route('rfq.add', $produk));

        $this->post(route('rfq.store'), $this->isiDataPemohon())->assertRedirect(route('rfq.terkirim'));

        $this->assertDatabaseCount('leads', 1);
        $this->get(route('rfq.terkirim'))->assertOk()->assertSee(Lead::query()->value('kode'));
    }

    public function test_empty_cart_cannot_be_submitted(): void
    {
        $this->post(route('rfq.store'), $this->isiDataPemohon())
            ->assertRedirect(route('rfq.index'))
            ->assertSessionHasErrors('keranjang');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post(route('rfq.add', BarangJasa::factory()->create()));

        $this->post(route('rfq.store'), ['nama' => '', 'telepon' => 'abc'])
            ->assertSessionHasErrors(['nama', 'telepon']);

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_service_request_requires_work_location(): void
    {
        $this->post(route('rfq.add', BarangJasa::factory()->jasa()->create()));

        $this->post(route('rfq.store'), $this->isiDataPemohon(['alamat' => '']))
            ->assertSessionHasErrors('alamat');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_honeypot_blocks_bot_submissions(): void
    {
        $this->post(route('rfq.add', BarangJasa::factory()->create()));

        $this->post(route('rfq.store'), $this->isiDataPemohon(['website' => 'http://spam.example']))
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('leads', 0);
    }

    public function test_lead_codes_increment_within_the_same_day(): void
    {
        config(['perusahaan.whatsapp' => '081234567890']);

        foreach (range(1, 2) as $ignored) {
            $this->post(route('rfq.add', BarangJasa::factory()->create()));
            $this->post(route('rfq.store'), $this->isiDataPemohon());
        }

        $kode = Lead::query()->orderBy('id')->pluck('kode');

        $this->assertStringEndsWith('-0001', $kode[0]);
        $this->assertStringEndsWith('-0002', $kode[1]);
    }

    public function test_catalog_pages_offer_add_to_rfq_action(): void
    {
        $produk = BarangJasa::factory()->create();

        $this->get(route('katalog.index'))->assertSee(route('rfq.add', $produk), false);
        $this->get(route('katalog.produk', $produk))
            ->assertOk()
            ->assertSee('Tambah ke keranjang RFQ');
    }
}
