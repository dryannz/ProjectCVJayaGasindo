<?php

namespace Tests\Feature;

use App\Models\BarangJasa;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_lists_active_products_and_hides_inactive_products(): void
    {
        $activeProduct = BarangJasa::factory()->create(['nama_barang' => 'Regulator Aktif']);
        $inactiveProduct = BarangJasa::factory()->nonaktif()->create(['nama_barang' => 'Regulator Nonaktif']);

        $response = $this->get(route('katalog.index'));

        $response->assertOk()
            ->assertSee($activeProduct->nama_barang)
            ->assertDontSee($inactiveProduct->nama_barang);
    }

    public function test_public_catalog_filters_products_by_category_and_type(): void
    {
        $category = Kategori::factory()->create(['nama' => 'Gas Industri', 'slug' => 'gas-industri']);
        $matchingProduct = BarangJasa::factory()->jasa()->create([
            'kategori_id' => $category->id,
            'nama_barang' => 'Instalasi Gas Industri',
        ]);
        $otherProduct = BarangJasa::factory()->create(['nama_barang' => 'Selang Umum']);

        $response = $this->get(route('katalog.index', [
            'kategori' => $category->slug,
            'tipe' => 'jasa',
        ]));

        $response->assertOk()
            ->assertSee($matchingProduct->nama_barang)
            ->assertDontSee($otherProduct->nama_barang);
    }

    public function test_public_product_detail_hides_private_price_values(): void
    {
        $product = BarangJasa::factory()->rfq()->create([
            'nama_barang' => 'Jasa Pengisian Gas',
            'price' => 765000,
        ]);

        $response = $this->get(route('katalog.produk', $product));

        $response->assertOk()
            ->assertSee('Harga sesuai permintaan')
            ->assertDontSee('765.000')
            ->assertDontSee('765000');
    }

    public function test_public_product_detail_returns_not_found_for_inactive_product(): void
    {
        $product = BarangJasa::factory()->nonaktif()->create();

        $this->get(route('katalog.produk', $product))->assertNotFound();
    }

    public function test_sitemap_contains_public_catalog_urls_only(): void
    {
        $category = Kategori::factory()->create(['slug' => 'perlengkapan']);
        $activeProduct = BarangJasa::factory()->create(['kategori_id' => $category->id]);
        $inactiveProduct = BarangJasa::factory()->nonaktif()->create();

        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee(route('katalog.index'), false)
            ->assertSee(route('katalog.kategori', ['kategori' => $category->slug]), false)
            ->assertSee(route('katalog.produk', $activeProduct), false)
            ->assertDontSee(route('katalog.produk', $inactiveProduct), false);
    }
}
