<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Kategori;
use App\Models\Petugas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_catalog_routes(): void
    {
        $this->get(route('admin.kategori.index'))->assertRedirect(route('login'));
        $this->get(route('admin.produk.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_staff_cannot_access_catalog_routes(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'teknisi']);

        $this->actingAs($petugas)
            ->get(route('admin.kategori.index'))
            ->assertForbidden();

        $this->actingAs($petugas)
            ->get(route('admin.produk.index'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_uses_real_catalog_counts(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);
        Kategori::factory()->create();
        BarangJasa::factory()->create(['is_active' => true, 'tipe' => 'fisik', 'stok' => 3]);
        BarangJasa::factory()->create(['is_active' => true, 'tipe' => 'jasa', 'stok' => null]);
        BarangJasa::factory()->create(['is_active' => false, 'tipe' => 'fisik', 'stok' => 1]);

        $this->actingAs($petugas)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Produk aktif')
            ->assertSee('2')
            ->assertSee('Stok fisik menipis');
    }

    public function test_admin_can_open_catalog_pages(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);
        $kategori = Kategori::factory()->create(['nama' => 'Regulator']);
        $produk = BarangJasa::factory()->create([
            'kategori_id' => $kategori->id,
            'nama_barang' => 'Regulator Tekanan',
        ]);

        $this->actingAs($petugas)
            ->get(route('admin.kategori.index'))
            ->assertOk()
            ->assertSee('Regulator');

        $this->actingAs($petugas)
            ->get(route('admin.produk.index'))
            ->assertOk()
            ->assertSee('Regulator Tekanan')
            ->assertSee('BRG-');

        $this->actingAs($petugas)->get(route('admin.produk.create'))->assertOk();
        $this->actingAs($petugas)->get(route('admin.produk.edit', $produk))->assertOk();
    }

    public function test_admin_can_create_category_with_a_unique_slug(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($petugas)
            ->post(route('admin.kategori.store'), ['nama' => 'Regulator Gas', 'urutan' => 1])
            ->assertRedirect(route('admin.kategori.index'));

        $this->actingAs($petugas)
            ->post(route('admin.kategori.store'), ['nama' => 'Regulator Gas', 'urutan' => 2])
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseHas('kategori', ['slug' => 'regulator-gas']);
        $this->assertDatabaseHas('kategori', ['slug' => 'regulator-gas-2']);
    }

    public function test_deleting_category_uncategorizes_attached_products(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);
        $kategori = Kategori::factory()->create();
        $produk = BarangJasa::factory()->create(['kategori_id' => $kategori->id]);

        $this->actingAs($petugas)
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
        $this->assertDatabaseHas('barang_jasa', ['idbarangjasa' => $produk->idbarangjasa, 'kategori_id' => null]);
    }

    public function test_admin_can_create_physical_product_with_automatic_id(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($petugas)
            ->post(route('admin.produk.store'), [
                'tipe' => 'fisik',
                'nama_barang' => 'Selang Gas',
                'deskripsi' => 'Selang untuk instalasi gas.',
                'ukuran' => '1/2 inch',
                'unit' => 'pcs',
                'mode_harga' => 'fix',
                'price' => 125000,
                'stok' => 8,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseHas('barang_jasa', [
            'idbarangjasa' => 'BRG-0001',
            'nama_barang' => 'Selang Gas',
            'stok' => 8,
        ]);
    }

    public function test_service_product_forces_stock_and_rfq_forces_price_to_null(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($petugas)
            ->post(route('admin.produk.store'), [
                'tipe' => 'jasa',
                'nama_barang' => 'Servis Instalasi',
                'ukuran' => '-',
                'unit' => 'pekerjaan',
                'mode_harga' => 'rfq',
                'price' => 200000,
                'stok' => 99,
            ])
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseHas('barang_jasa', [
            'idbarangjasa' => 'JSA-0001',
            'stok' => null,
            'price' => null,
        ]);
    }

    public function test_physical_fixed_price_product_requires_stock_and_price(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($petugas)
            ->from(route('admin.produk.create'))
            ->post(route('admin.produk.store'), [
                'tipe' => 'fisik',
                'nama_barang' => 'Regulator',
                'ukuran' => '1/2 inch',
                'unit' => 'pcs',
                'mode_harga' => 'fix',
            ])
            ->assertRedirect(route('admin.produk.create'))
            ->assertSessionHasErrors(['stok', 'price']);
    }

    public function test_product_image_is_stored_on_public_disk(): void
    {
        Storage::fake('public');
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);

        $this->actingAs($petugas)->post(route('admin.produk.store'), [
            'tipe' => 'fisik',
            'nama_barang' => 'Tabung Gas',
            'ukuran' => '12 kg',
            'unit' => 'unit',
            'mode_harga' => 'fix',
            'price' => 500000,
            'stok' => 4,
            'gambar' => UploadedFile::fake()->image('tabung.png'),
        ])->assertRedirect(route('admin.produk.index'));

        $path = BarangJasa::query()->firstOrFail()->gambar;

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_product_update_uses_route_bound_model_instead_of_posted_id(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);
        $produk = BarangJasa::factory()->create(['idbarangjasa' => 'BRG-0012']);

        $this->actingAs($petugas)
            ->put(route('admin.produk.update', $produk), [
                'idbarangjasa' => 'JSA-9999',
                'tipe' => 'fisik',
                'nama_barang' => 'Nama diperbarui',
                'ukuran' => '1/2 inch',
                'unit' => 'pcs',
                'mode_harga' => 'fix',
                'price' => 150000,
                'stok' => 6,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.produk.index'));

        $this->assertDatabaseHas('barang_jasa', ['idbarangjasa' => 'BRG-0012', 'nama_barang' => 'Nama diperbarui']);
        $this->assertDatabaseMissing('barang_jasa', ['idbarangjasa' => 'JSA-9999']);
    }

    public function test_product_referenced_by_detail_po_cannot_be_deleted(): void
    {
        $petugas = Petugas::factory()->create(['role' => 'admin_sales']);
        $produk = BarangJasa::factory()->create();

        Schema::create('detail_po', function (Blueprint $table): void {
            $table->string('idbarangjasa', 20);
            $table->string('nopo', 20);
            $table->string('qty', 10);
            $table->decimal('amount', 10, 0);
        });

        DB::table('detail_po')->insert([
            'idbarangjasa' => $produk->idbarangjasa,
            'nopo' => 'PO-0001',
            'qty' => '1',
            'amount' => 100000,
        ]);

        $this->actingAs($petugas)
            ->delete(route('admin.produk.destroy', $produk))
            ->assertRedirect()
            ->assertSessionHasErrors('produk');

        $this->assertDatabaseHas('barang_jasa', ['idbarangjasa' => $produk->idbarangjasa]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('detail_po');

        parent::tearDown();
    }
}
