<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\BarangJasa;
use App\Models\Customer;
use App\Models\DetailPo;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\StokMutasi;
use App\Models\SuratJalan;
use App\Support\PoService;
use App\Support\SuratJalanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SuratJalanTest extends TestCase
{
    use RefreshDatabase;

    private Petugas $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Petugas::factory()->create(['role' => 'admin_sales']);
    }

    public function test_surat_jalan_requires_a_processed_po(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithProducts($customer, [
            ['product' => BarangJasa::factory()->create(), 'qty' => 1],
        ], Po::STATUS_DRAFT);

        $this->expectException(ValidationException::class);

        $this->service()->createFromPo($poData['po'], $this->payload($poData['details']), $this->admin->idpetugas);
    }

    public function test_numbering_is_sequential_and_uses_customer_code(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithProducts($customer, [
            ['product' => BarangJasa::factory()->create(), 'qty' => 3],
        ]);

        $first = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 1]]),
            $this->admin->idpetugas,
        );
        $second = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 1]]),
            $this->admin->idpetugas,
        );

        $this->assertStringContainsString('MJGE-ABCD', $first->nosuratjalan);
        $this->assertStringContainsString(now()->format('m').'-001/', $first->nosuratjalan);
        $this->assertStringContainsString(now()->format('m').'-002/', $second->nosuratjalan);
    }

    public function test_customer_without_short_code_is_rejected(): void
    {
        $customer = Customer::factory()->create();
        $poData = $this->poWithProducts($customer, [
            ['product' => BarangJasa::factory()->create(), 'qty' => 1],
        ]);

        try {
            $this->service()->createFromPo($poData['po'], $this->payload($poData['details']), $this->admin->idpetugas);
            $this->fail('Expected customer code validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('idcust', $exception->errors());
        }

        $this->assertDatabaseCount('surat_jalan', 0);
    }

    public function test_active_surat_jalan_qty_cannot_exceed_po_qty(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $product = BarangJasa::factory()->create(['stok' => 10]);
        $poData = $this->poWithProducts($customer, [
            ['product' => $product, 'qty' => 3],
        ]);

        $first = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 2]]),
            $this->admin->idpetugas,
        );
        $this->service()->publish($first, $this->admin->idpetugas);

        try {
            $this->service()->createFromPo(
                $poData['po'],
                $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 2]]),
                $this->admin->idpetugas,
            );
            $this->fail('Expected remaining quantity validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items.0.qty', $exception->errors());
        }

        $remaining = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 1]]),
            $this->admin->idpetugas,
        );

        $this->assertSame(SuratJalan::STATUS_DRAFT, $remaining->status);
    }

    public function test_publishing_delivery_consumes_physical_stock_once(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $physical = BarangJasa::factory()->create(['stok' => 10]);
        $service = BarangJasa::factory()->jasa()->create(['nama_barang' => 'Instalasi pipa']);
        $poData = $this->poWithProducts($customer, [
            ['product' => $physical, 'qty' => 3],
            ['product' => $service, 'qty' => 1],
        ]);

        $suratJalan = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details']),
            $this->admin->idpetugas,
        );
        $published = $this->service()->publish($suratJalan, $this->admin->idpetugas);

        $this->assertSame(SuratJalan::STATUS_DITERBITKAN, $published->status);
        $this->assertSame(7, (int) $physical->fresh()->stok);
        $this->assertNull($service->fresh()->stok);
        $this->assertDatabaseHas('stok_mutasi', [
            'referensi_tipe' => 'sj',
            'referensi_id' => $suratJalan->nosuratjalan,
            'jenis' => StokMutasi::JENIS_KELUAR,
            'qty' => 3,
            'stok_sebelum' => 10,
            'stok_sesudah' => 7,
        ]);

        try {
            $this->service()->publish($suratJalan, $this->admin->idpetugas);
            $this->fail('Expected duplicate publication to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertSame(1, StokMutasi::query()->where('referensi_id', $suratJalan->nosuratjalan)->count());
    }

    public function test_non_delivery_does_not_change_stock(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $physical = BarangJasa::factory()->create(['stok' => 10]);
        $poData = $this->poWithProducts($customer, [
            ['product' => $physical, 'qty' => 2],
        ]);

        $pickup = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details'], [['detail_po_id' => $poData['details'][0]->id, 'qty' => 2]], SuratJalan::JENIS_PENJEMPUTAN),
            $this->admin->idpetugas,
        );
        $this->service()->publish($pickup, $this->admin->idpetugas);

        $this->assertSame(10, (int) $physical->fresh()->stok);
        $this->assertDatabaseCount('stok_mutasi', 0);
    }

    public function test_insufficient_stock_rolls_back_every_item(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $available = BarangJasa::factory()->create(['stok' => 10]);
        $short = BarangJasa::factory()->create(['stok' => 1, 'nama_barang' => 'Stok Terbatas']);
        $poData = $this->poWithProducts($customer, [
            ['product' => $available, 'qty' => 2],
            ['product' => $short, 'qty' => 3],
        ]);
        $suratJalan = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details']),
            $this->admin->idpetugas,
        );

        try {
            $this->service()->publish($suratJalan, $this->admin->idpetugas);
            $this->fail('Expected insufficient stock exception.');
        } catch (InsufficientStockException $exception) {
            $this->assertCount(1, $exception->shortages);
            $this->assertSame(3, $exception->shortages[0]['diminta']);
            $this->assertSame(1, $exception->shortages[0]['tersedia']);
        }

        $this->assertSame(10, (int) $available->fresh()->stok);
        $this->assertSame(1, (int) $short->fresh()->stok);
        $this->assertSame(SuratJalan::STATUS_DRAFT, $suratJalan->fresh()->status);
        $this->assertDatabaseCount('stok_mutasi', 0);
    }

    public function test_cancelling_published_surat_jalan_returns_stock_once_and_unblocks_po(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $product = BarangJasa::factory()->create(['stok' => 5]);
        $poData = $this->poWithProducts($customer, [
            ['product' => $product, 'qty' => 2],
        ]);
        $suratJalan = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details']),
            $this->admin->idpetugas,
        );
        $this->service()->publish($suratJalan, $this->admin->idpetugas);

        try {
            app(PoService::class)->changeStatus($poData['po'], Po::STATUS_BATAL, $this->admin->idpetugas);
            $this->fail('Expected active Surat Jalan to block PO cancellation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $cancelled = $this->service()->cancel($suratJalan, 'Pengiriman dibatalkan customer.', $this->admin->idpetugas);

        $this->assertSame(SuratJalan::STATUS_DIBATALKAN, $cancelled->status);
        $this->assertSame(5, (int) $product->fresh()->stok);
        $this->assertSame(2, StokMutasi::query()->where('referensi_id', $suratJalan->nosuratjalan)->count());
        $this->assertSame(StokMutasi::JENIS_MASUK, StokMutasi::query()->where('referensi_id', $suratJalan->nosuratjalan)->latest('id')->value('jenis'));

        try {
            $this->service()->cancel($suratJalan, 'Pembatalan kedua.', $this->admin->idpetugas);
            $this->fail('Expected duplicate cancellation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $cancelledPo = app(PoService::class)->changeStatus($poData['po'], Po::STATUS_BATAL, $this->admin->idpetugas);

        $this->assertSame(Po::STATUS_BATAL, $cancelledPo->status);
    }

    public function test_received_surat_jalan_requires_publication_first(): void
    {
        $customer = Customer::factory()->withKodeSingkat('ABCD')->create();
        $poData = $this->poWithProducts($customer, [
            ['product' => BarangJasa::factory()->create(), 'qty' => 1],
        ]);
        $suratJalan = $this->service()->createFromPo(
            $poData['po'],
            $this->payload($poData['details']),
            $this->admin->idpetugas,
        );

        try {
            $this->service()->receive($suratJalan, [
                'nama_penerima' => 'Budi',
                'tanggal_diterima' => '2026-11-17',
            ], $this->admin->idpetugas);
            $this->fail('Expected receiving a draft Surat Jalan to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }
    }

    private function service(): SuratJalanService
    {
        return app(SuratJalanService::class);
    }

    /**
     * @param  array<int, array{product: BarangJasa, qty: int}>  $items
     * @return array{po: Po, details: array<int, DetailPo>}
     */
    private function poWithProducts(Customer $customer, array $items, string $status = Po::STATUS_DIPROSES): array
    {
        $po = Po::factory()->create([
            'idcust' => $customer->idcust,
            'idpetugas' => $this->admin->idpetugas,
            'status' => $status,
        ]);
        $details = [];

        foreach ($items as $item) {
            $details[] = DetailPo::create([
                'idbarangjasa' => $item['product']->idbarangjasa,
                'pono' => $po->pono,
                'qty' => $item['qty'],
                'amount' => 100000 * $item['qty'],
                'nama_barang' => $item['product']->nama_barang,
                'unit' => $item['product']->unit,
                'harga_satuan' => 100000,
            ]);
        }

        return ['po' => $po, 'details' => $details];
    }

    /**
     * @param  array<int, DetailPo>  $details
     * @param  array<int, array{detail_po_id: int, qty: int}>|null  $items
     * @return array{tgl: string, jenis: string, items: array<int, array{detail_po_id: int, qty: int}>}
     */
    private function payload(array $details, ?array $items = null, string $jenis = SuratJalan::JENIS_PENGIRIMAN): array
    {
        return [
            'tgl' => '2026-11-17',
            'jenis' => $jenis,
            'items' => $items ?? array_map(
                fn (DetailPo $detail): array => [
                    'detail_po_id' => $detail->id,
                    'qty' => $detail->qty,
                ],
                $details,
            ),
        ];
    }
}
