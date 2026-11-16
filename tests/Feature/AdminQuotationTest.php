<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadItem;
use App\Models\NomorDokumen;
use App\Models\Petugas;
use App\Models\Quotation;
use App\Support\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuotationTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Petugas::factory()->create();
    }

    /** @return array<string, mixed> */
    private function payloadQuotation(Customer $customer, array $overrides = []): array
    {
        return [
            'customer_mode' => 'existing',
            'idcust' => $customer->idcust,
            'tanggal' => '2026-11-16',
            'berlaku_sampai' => '2026-12-16',
            'diskon' => 0,
            'ppn_persen' => 0,
            'items' => [
                [
                    'idbarangjasa' => null,
                    'nama_barang' => 'Tabung Argon',
                    'tipe' => 'fisik',
                    'ukuran' => '1/2 inch',
                    'unit' => 'tabung',
                    'qty' => 2,
                    'harga_satuan' => 50000,
                ],
            ],
            ...$overrides,
        ];
    }

    public function test_guest_dan_non_admin_ditolak(): void
    {
        $customer = Customer::factory()->create();

        $this->get(route('admin.quotation.index'))->assertRedirect(route('login'));

        /** @var Petugas $teknisi */
        $teknisi = Petugas::factory()->create(['role' => 'teknisi']);
        $this->actingAs($teknisi)->get(route('admin.quotation.index'))->assertForbidden();
        $this->actingAs($teknisi)->post(route('admin.quotation.store'), $this->payloadQuotation($customer))->assertForbidden();
    }

    public function test_admin_membuat_quotation_dengan_nomor_otomatis_dan_total_dihitung_server(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.quotation.store'), $this->payloadQuotation($customer, [
                'diskon' => 20000,
                'ppn_persen' => 11,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $quotation = Quotation::query()->sole();

        $this->assertSame('QT/2026/11/0001', $quotation->nomor_quotation);
        $this->assertSame(100000, (int) $quotation->subtotal);
        $this->assertSame(20000, (int) $quotation->diskon);
        $this->assertSame(8800, (int) $quotation->ppn);
        $this->assertSame(88800, (int) $quotation->total);
        $this->assertSame(Quotation::STATUS_DRAFT, $quotation->status);
        $this->assertSame($this->admin->idpetugas, $quotation->idpetugas);
        $this->assertSame(1, $quotation->details()->count());
        $this->assertSame(100000, (int) $quotation->details()->first()->jumlah);
    }

    public function test_nomor_berurutan_per_bulan(): void
    {
        NomorDokumen::create([
            'jenis' => NomorDokumen::JENIS_QUOTATION,
            'periode' => now()->format('Y-m'),
            'terakhir' => 7,
        ]);

        $customer = Customer::factory()->create();
        $this->actingAs($this->admin)->post(route('admin.quotation.store'), $this->payloadQuotation($customer))->assertRedirect();

        $this->assertSame('QT/'.now()->format('Y/m').'/0008', Quotation::query()->sole()->nomor_quotation);
    }

    public function test_buat_quotation_dari_lead_membuat_customer_baru_dan_mengubah_status_lead(): void
    {
        $lead = Lead::factory()->create([
            'nama' => 'Budi Santoso',
            'perusahaan' => 'PT Teknik Sejahtera',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Industri No. 10',
            'status' => 'baru',
        ]);
        LeadItem::create([
            'lead_id' => $lead->id,
            'idbarangjasa' => null,
            'nama_barang' => 'Tabung Oksigen',
            'tipe' => 'fisik',
            'unit' => 'tabung',
            'qty' => 3,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.quotation.store'), [
                'customer_mode' => 'baru',
                'lead_id' => $lead->id,
                'customer_baru' => [
                    'nama_pt' => 'PT Teknik Sejahtera',
                    'notelp_pt' => '081234567890',
                    'alamat_pt' => 'Jl. Industri No. 10',
                    'pic' => 'Budi Santoso',
                ],
                'tanggal' => '2026-11-16',
                'ppn_persen' => 0,
                'diskon' => 0,
                'items' => [[
                    'idbarangjasa' => null,
                    'nama_barang' => 'Tabung Oksigen',
                    'tipe' => 'fisik',
                    'qty' => 3,
                    'harga_satuan' => 45000,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $customer = Customer::query()->sole();

        $this->assertSame('PT Teknik Sejahtera', $customer->nama_pt);
        $this->assertSame('081234567890', $customer->notelp_pt);
        $this->assertSame('Jl. Industri No. 10', $customer->alamat_pt);
        $this->assertSame('Budi Santoso', $customer->pic);

        $quotation = Quotation::query()->sole();
        $this->assertSame($lead->id, $quotation->lead_id);
        $this->assertSame($customer->idcust, $quotation->idcust);
        $this->assertSame('quotation', $lead->refresh()->status);
    }

    public function test_create_page_dari_lead_menampilkan_saran_customer_cocok(): void
    {
        $lead = Lead::factory()->create([
            'perusahaan' => 'PT Lama Jaya',
            'telepon' => '089876543210',
        ]);
        $cocok = Customer::factory()->create([
            'nama_pt' => 'PT Lama Jaya',
            'notelp_pt' => '089876543210',
        ]);

        $this->actingAs($this->admin)->get(route('admin.quotation.create', ['lead_id' => $lead->id]))
            ->assertOk()
            ->assertSee('Customer serupa ditemukan')
            ->assertSee($cocok->nama_pt);
    }

    public function test_validasi_item_tanpa_harga_ditolak(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.quotation.store'), $this->payloadQuotation($customer, [
                'items' => [[
                    'nama_barang' => 'Tabung',
                    'qty' => 1,
                    'harga_satuan' => null,
                ]],
            ]))
            ->assertSessionHasErrors('items.0.harga_satuan');

        $this->assertSame(0, Quotation::query()->count());
    }

    public function test_quotation_tanpa_item_ditolak(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.quotation.store'), $this->payloadQuotation($customer, ['items' => []]))
            ->assertSessionHasErrors('items');
    }

    public function test_draft_dapat_diedit_lalu_terkirim_dikunci(): void
    {
        $customer = Customer::factory()->create();
        $quotation = Quotation::factory()->for($customer, 'customer')->create();

        // 1. Edit saat masih draft
        $this->actingAs($this->admin)
            ->put(route('admin.quotation.update', $quotation), $this->payloadQuotation($customer, [
                'diskon' => 10000,
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.quotation.show', $quotation));

        $quotation->refresh();
        $this->assertSame(10000, (int) $quotation->diskon);
        $this->assertSame(100000, (int) $quotation->subtotal);

        // 2. Ubah status menjadi terkirim
        $this->actingAs($this->admin)
            ->patch(route('admin.quotation.status', $quotation), [
                'status' => Quotation::STATUS_TERKIRIM,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        // 3. Edit setelah terkirim: nominal/diskon tetap terkunci, hanya catatan yang diperbarui
        $this->actingAs($this->admin)
            ->put(route('admin.quotation.update', $quotation), [
                'catatan' => 'Sudah dikirim via WhatsApp.',
                'diskon' => 50000,
            ])
            ->assertRedirect(route('admin.quotation.show', $quotation));

        $quotation->refresh();
        $this->assertSame(Quotation::STATUS_TERKIRIM, $quotation->status);
        $this->assertSame(10000, (int) $quotation->diskon);
    }

    public function test_transisi_status_tidak_valid_ditolak(): void
    {
        $customer = Customer::factory()->create();
        $quotation = Quotation::factory()->terkirim()->for($customer, 'customer')->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.quotation.status', $quotation), ['status' => Quotation::STATUS_TERKIRIM])
            ->assertSessionHasErrors('status');
    }

    public function test_status_kadaluarsa_efektif(): void
    {
        $customer = Customer::factory()->create();
        $quotation = Quotation::factory()->terkirim()->for($customer, 'customer')->create([
            'berlaku_sampai' => now()->subDay()->toDateString(),
        ]);

        $this->assertSame(Quotation::STATUS_KADALUARSA, $quotation->status_efektif);

        $this->actingAs($this->admin)->get(route('admin.quotation.index', ['status' => 'kadaluarsa']))
            ->assertOk()
            ->assertSee($quotation->nomor_quotation);
    }

    public function test_harga_khusus_kosong_menghasilkan_harga_null(): void
    {
        $customer = Customer::factory()->create();
        $produk = BarangJasa::factory()->create(['mode_harga' => 'khusus', 'price' => 999999]);

        $harga = QuotationService::hargaAwal($produk, $customer->idcust);

        $this->assertNull($harga['harga_satuan']);
        $this->assertSame('khusus-kosong', $harga['sumber']);
    }

    public function test_customer_yang_dipakai_quotation_tidak_dapat_dihapus(): void
    {
        $customer = Customer::factory()->create();
        Quotation::factory()->for($customer, 'customer')->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.customer.destroy', $customer))
            ->assertSessionHasErrors('customer');

        $this->assertModelExists($customer);
    }

    public function test_customer_yang_belum_dipakai_dapat_dihapus(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.customer.destroy', $customer))
            ->assertRedirect(route('admin.customer.index'));

        $this->assertModelMissing($customer);
    }
}