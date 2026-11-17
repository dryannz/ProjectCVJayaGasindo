<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\BarangJasa;
use App\Models\DetailQuotation;
use App\Models\NomorDokumen;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\Quotation;
use App\Models\StokMutasi;
use App\Support\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPoTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Petugas::factory()->create(['role' => 'admin_sales']);
    }

    public function test_guest_and_non_admin_cannot_access_po_routes(): void
    {
        $quotation = $this->quotationWithProducts([
            ['product' => BarangJasa::factory()->create(), 'qty' => 1, 'price' => 100000],
        ]);

        $this->get(route('admin.po.index'))->assertRedirect(route('login'));

        $technician = Petugas::factory()->create(['role' => 'teknisi']);
        $this->actingAs($technician)->get(route('admin.po.index'))->assertForbidden();
        $this->actingAs($technician)->post(route('admin.po.store'), $this->poPayload($quotation))->assertForbidden();
    }

    public function test_admin_can_create_po_from_approved_quotation_with_copied_items(): void
    {
        $product = BarangJasa::factory()->create(['nama_barang' => 'Tabung Argon', 'stok' => 8]);
        $quotation = $this->quotationWithProducts([
            ['product' => $product, 'qty' => 2, 'price' => 125000],
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.po.store'), $this->poPayload($quotation))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $po = Po::query()->sole();
        $detail = $po->details()->sole();

        $this->assertSame($quotation->id, $po->quotation_id);
        $this->assertSame($quotation->idcust, $po->idcust);
        $this->assertSame($product->idbarangjasa, $detail->idbarangjasa);
        $this->assertSame('Tabung Argon', $detail->nama_barang);
        $this->assertSame(250000, (int) $po->subtotal);
        $this->assertSame(Po::STATUS_DRAFT, $po->status);
    }

    public function test_one_quotation_can_only_have_one_po_and_unapproved_quotation_is_rejected(): void
    {
        $quotation = $this->quotationWithProducts([
            ['product' => BarangJasa::factory()->create(), 'qty' => 1, 'price' => 50000],
        ]);
        $payload = $this->poPayload($quotation);

        $this->actingAs($this->admin)->post(route('admin.po.store'), $payload)->assertRedirect();
        $this->actingAs($this->admin)
            ->from(route('admin.po.index'))
            ->post(route('admin.po.store'), $payload)
            ->assertRedirect(route('admin.po.index'))
            ->assertSessionHasErrors('quotation_id');

        $draftQuotation = $this->quotationWithProducts([
            ['product' => BarangJasa::factory()->create(), 'qty' => 1, 'price' => 50000],
        ], Quotation::STATUS_DRAFT);

        $this->actingAs($this->admin)
            ->from(route('admin.po.index'))
            ->post(route('admin.po.store'), $this->poPayload($draftQuotation))
            ->assertRedirect(route('admin.po.index'))
            ->assertSessionHasErrors('quotation_id');

        $this->assertSame(1, Po::query()->count());
    }

    public function test_po_number_is_sequential_and_server_total_ignores_client_total(): void
    {
        NomorDokumen::create([
            'jenis' => NomorDokumen::JENIS_PO,
            'periode' => now()->format('Y-m'),
            'terakhir' => 4,
        ]);

        $firstQuotation = $this->quotationWithProducts([
            ['product' => BarangJasa::factory()->create(), 'qty' => 2, 'price' => 100000],
        ]);
        $secondQuotation = $this->quotationWithProducts([
            ['product' => BarangJasa::factory()->create(), 'qty' => 1, 'price' => 75000],
        ]);

        $firstPayload = $this->poPayload($firstQuotation, [
            'diskon' => 10000,
            'ppn_persen' => 10,
            'total' => 1,
        ]);
        $firstPayload['items'][0]['qty'] = 3;
        $firstPayload['items'][0]['harga_satuan'] = 110000;

        $this->actingAs($this->admin)->post(route('admin.po.store'), $firstPayload)->assertRedirect();
        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($secondQuotation))->assertRedirect();

        $pos = Po::query()->orderBy('pono')->get();
        $this->assertSame('PO/'.now()->format('Y/m').'/0005', $pos[0]->pono);
        $this->assertSame('PO/'.now()->format('Y/m').'/0006', $pos[1]->pono);
        $this->assertSame(330000, (int) $pos[0]->subtotal);
        $this->assertSame(10000, (int) $pos[0]->diskon);
        $this->assertSame(32000, (int) $pos[0]->ppn);
        $this->assertSame(352000, (int) $pos[0]->total);
    }

    public function test_processing_does_not_consume_stock_for_physical_or_service_items(): void
    {
        $physical = BarangJasa::factory()->create(['stok' => 10]);
        $service = BarangJasa::factory()->jasa()->create(['nama_barang' => 'Instalasi pipa']);
        $quotation = $this->quotationWithProducts([
            ['product' => $physical, 'qty' => 3, 'price' => 100000],
            ['product' => $service, 'qty' => 1, 'price' => 500000],
        ]);

        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($quotation))->assertRedirect();
        $po = Po::query()->sole();

        $this->actingAs($this->admin)
            ->patch(route('admin.po.status', $po), ['status' => Po::STATUS_DIPROSES])
            ->assertRedirect(route('admin.po.show', $po));

        $this->assertSame(10, (int) $physical->fresh()->stok);
        $this->assertNull($service->fresh()->stok);
        $this->assertDatabaseCount('stok_mutasi', 0);
    }

    public function test_processing_does_not_reject_insufficient_stock(): void
    {
        $available = BarangJasa::factory()->create(['stok' => 10]);
        $short = BarangJasa::factory()->create(['stok' => 1, 'nama_barang' => 'Stok Terbatas']);
        $quotation = $this->quotationWithProducts([
            ['product' => $available, 'qty' => 2, 'price' => 100000],
            ['product' => $short, 'qty' => 3, 'price' => 100000],
        ]);

        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($quotation))->assertRedirect();
        $po = Po::query()->sole();

        $this->actingAs($this->admin)
            ->from(route('admin.po.show', $po))
            ->patch(route('admin.po.status', $po), ['status' => Po::STATUS_DIPROSES])
            ->assertRedirect(route('admin.po.show', $po))
            ->assertSessionHasNoErrors();

        $this->assertSame(10, (int) $available->fresh()->stok);
        $this->assertSame(1, (int) $short->fresh()->stok);
        $this->assertSame(Po::STATUS_DIPROSES, $po->fresh()->status);
        $this->assertDatabaseCount('stok_mutasi', 0);
    }

    public function test_valid_status_transitions_and_invalid_transition_are_enforced(): void
    {
        $product = BarangJasa::factory()->create(['stok' => 5]);
        $quotation = $this->quotationWithProducts([
            ['product' => $product, 'qty' => 1, 'price' => 100000],
        ]);
        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($quotation))->assertRedirect();
        $po = Po::query()->sole();

        $this->actingAs($this->admin)->patch(route('admin.po.status', $po), ['status' => Po::STATUS_DIPROSES])->assertRedirect();
        $this->actingAs($this->admin)->patch(route('admin.po.status', $po), ['status' => Po::STATUS_SELESAI])->assertRedirect();
        $this->assertSame(Po::STATUS_SELESAI, $po->fresh()->status);

        $this->actingAs($this->admin)
            ->from(route('admin.po.show', $po))
            ->patch(route('admin.po.status', $po), ['status' => Po::STATUS_DIPROSES])
            ->assertRedirect(route('admin.po.show', $po))
            ->assertSessionHasErrors('status');
    }

    public function test_cancel_does_not_change_stock_after_processing_or_from_draft(): void
    {
        $processedProduct = BarangJasa::factory()->create(['stok' => 5]);
        $processedQuotation = $this->quotationWithProducts([
            ['product' => $processedProduct, 'qty' => 2, 'price' => 100000],
        ]);
        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($processedQuotation))->assertRedirect();
        $processedPo = Po::query()->sole();
        $this->actingAs($this->admin)->patch(route('admin.po.status', $processedPo), ['status' => Po::STATUS_DIPROSES])->assertRedirect();
        $this->assertSame(5, (int) $processedProduct->fresh()->stok);

        $this->actingAs($this->admin)->patch(route('admin.po.status', $processedPo), ['status' => Po::STATUS_BATAL])->assertRedirect();
        $this->assertSame(5, (int) $processedProduct->fresh()->stok);
        $this->assertSame(0, StokMutasi::query()->where('referensi_id', $processedPo->pono)->count());

        $this->actingAs($this->admin)
            ->from(route('admin.po.show', $processedPo))
            ->patch(route('admin.po.status', $processedPo), ['status' => Po::STATUS_BATAL])
            ->assertRedirect(route('admin.po.show', $processedPo))
            ->assertSessionHasErrors('status');

        $draftProduct = BarangJasa::factory()->create(['stok' => 4]);
        $draftQuotation = $this->quotationWithProducts([
            ['product' => $draftProduct, 'qty' => 1, 'price' => 100000],
        ]);
        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($draftQuotation))->assertRedirect();
        $draftPo = Po::query()->where('quotation_id', $draftQuotation->id)->sole();
        $this->actingAs($this->admin)->patch(route('admin.po.status', $draftPo), ['status' => Po::STATUS_BATAL])->assertRedirect();
        $this->assertSame(4, (int) $draftProduct->fresh()->stok);
    }

    public function test_processed_po_items_are_locked(): void
    {
        $product = BarangJasa::factory()->create(['stok' => 5]);
        $quotation = $this->quotationWithProducts([
            ['product' => $product, 'qty' => 1, 'price' => 100000],
        ]);
        $this->actingAs($this->admin)->post(route('admin.po.store'), $this->poPayload($quotation))->assertRedirect();
        $po = Po::query()->sole();
        $this->actingAs($this->admin)->patch(route('admin.po.status', $po), ['status' => Po::STATUS_DIPROSES])->assertRedirect();

        $this->actingAs($this->admin)
            ->from(route('admin.po.show', $po))
            ->put(route('admin.po.update', $po), $this->poPayload($quotation, ['items' => [[
                'idbarangjasa' => $product->idbarangjasa,
                'nama_barang' => $product->nama_barang,
                'qty' => 9,
                'harga_satuan' => 999999,
            ]]]))
            ->assertRedirect(route('admin.po.show', $po))
            ->assertSessionHasErrors('status');

        $this->assertSame(1, (int) $po->fresh()->details()->sole()->qty);
    }

    public function test_stock_service_records_snapshots_and_never_goes_negative(): void
    {
        $product = BarangJasa::factory()->create(['stok' => 5]);
        $service = app(StockService::class);

        $service->keluar($product, 2, 'manual', null, 'Pengeluaran test', $this->admin->idpetugas);
        $service->masuk($product, 1, 'manual', null, 'Penerimaan test', $this->admin->idpetugas);

        try {
            $service->keluar($product, 10, 'manual', null, 'Tidak boleh', $this->admin->idpetugas);
            $this->fail('Expected an insufficient stock exception.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame(10, $exception->diminta);
            $this->assertSame(4, $exception->tersedia);
        }

        $mutations = StokMutasi::query()->orderBy('id')->get();
        $this->assertSame([5, 3], $mutations->pluck('stok_sebelum')->all());
        $this->assertSame([3, 4], $mutations->pluck('stok_sesudah')->all());
        $this->assertSame(4, (int) $product->fresh()->stok);
    }

    public function test_product_form_stock_change_is_recorded_as_correction_and_services_have_no_mutation(): void
    {
        $product = BarangJasa::factory()->create(['stok' => 2]);
        $this->actingAs($this->admin)
            ->put(route('admin.produk.update', $product), [
                'tipe' => 'fisik',
                'nama_barang' => $product->nama_barang,
                'ukuran' => $product->ukuran,
                'unit' => $product->unit,
                'mode_harga' => 'fix',
                'price' => 100000,
                'stok' => 7,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.produk.index'));

        $this->assertSame(7, (int) $product->fresh()->stok);
        $this->assertSame(StokMutasi::JENIS_KOREKSI, StokMutasi::query()->sole()->jenis);
        $this->assertSame(2, StokMutasi::query()->sole()->stok_sebelum);
        $this->assertSame(7, StokMutasi::query()->sole()->stok_sesudah);

        $serviceProduct = BarangJasa::factory()->jasa()->create();
        $stockService = app(StockService::class);
        $stockService->masuk($serviceProduct, 3, 'manual', null, 'Tidak dicatat', $this->admin->idpetugas);
        $stockService->koreksi($serviceProduct, 4, 'Tidak dicatat', $this->admin->idpetugas);

        $this->assertNull($serviceProduct->fresh()->stok);
        $this->assertSame(1, StokMutasi::query()->count());
    }

    /** @param array<int, array{product: BarangJasa, qty: int, price: int}> $items */
    private function quotationWithProducts(array $items, string $status = Quotation::STATUS_DISETUJUI): Quotation
    {
        $quotation = Quotation::factory()->status($status)->create();
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $item['product'];
            $qty = $item['qty'];
            $price = $item['price'];
            $subtotal += $qty * $price;

            DetailQuotation::create([
                'quotation_id' => $quotation->id,
                'idbarangjasa' => $product->idbarangjasa,
                'nama_barang' => $product->nama_barang,
                'tipe' => $product->tipe,
                'ukuran' => $product->ukuran,
                'unit' => $product->unit,
                'qty' => $qty,
                'harga_satuan' => $price,
                'jumlah' => $qty * $price,
            ]);
        }

        $quotation->update([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        return $quotation->fresh('details');
    }

    /** @return array<string, mixed> */
    private function poPayload(Quotation $quotation, array $overrides = []): array
    {
        $quotation->loadMissing('details');

        $items = $quotation->details->map(fn (DetailQuotation $detail): array => [
            'idbarangjasa' => $detail->idbarangjasa,
            'nama_barang' => $detail->nama_barang,
            'qty' => $detail->qty,
            'harga_satuan' => (int) $detail->harga_satuan,
        ])->all();

        return [
            'quotation_id' => $quotation->id,
            'idcust' => $quotation->idcust,
            'tgl_order' => '2026-11-16',
            'schedule_delivery' => '2026-11-23',
            'term_of_payment' => '30 hari',
            'project_name' => 'Proyek uji',
            'project_location' => 'Jakarta',
            'no_po_customer' => null,
            'diskon' => 0,
            'ppn_persen' => 0,
            'catatan' => null,
            'items' => $items,
            ...$overrides,
        ];
    }
}
