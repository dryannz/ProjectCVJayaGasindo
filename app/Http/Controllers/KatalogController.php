<?php

namespace App\Http\Controllers;

use App\Models\BarangJasa;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class KatalogController extends Controller
{
    public function index(Request $request): View
    {
        $kategoris = Kategori::query()
            ->withCount('barangJasaAktif')
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();

        $uncategorizedCount = BarangJasa::query()
            ->aktif()
            ->whereNull('kategori_id')
            ->count();

        $products = $this->filteredProducts($request)
            ->orderBy('nama_barang')
            ->paginate(12)
            ->withQueryString();

        return view('pages.katalog.index', [
            'kategoris' => $kategoris,
            'uncategorizedCount' => $uncategorizedCount,
            'produkList' => $products,
            'selectedKategori' => $request->string('kategori')->toString(),
            'selectedTipe' => $request->string('tipe')->toString(),
            'searchQuery' => trim($request->string('q')->toString()),
        ]);
    }

    public function kategori(Request $request, Kategori $kategori): View
    {
        $products = $this->filteredProducts($request)
            ->where('kategori_id', $kategori->getKey())
            ->orderBy('nama_barang')
            ->paginate(12)
            ->withQueryString();

        return view('pages.katalog.kategori', [
            'kategori' => $kategori,
            'produkList' => $products,
            'selectedTipe' => $request->string('tipe')->toString(),
            'searchQuery' => trim($request->string('q')->toString()),
        ]);
    }

    public function show(BarangJasa $barangJasa): View
    {
        abort_if(! $barangJasa->is_active, 404);

        $barangJasa->loadMissing('kategori');

        $relatedProducts = BarangJasa::query()
            ->aktif()
            ->where('idbarangjasa', '!=', $barangJasa->getKey())
            ->when(
                $barangJasa->kategori_id !== null,
                fn (Builder $query): Builder => $query->where('kategori_id', $barangJasa->kategori_id),
                fn (Builder $query): Builder => $query->whereNull('kategori_id'),
            )
            ->with('kategori')
            ->orderBy('nama_barang')
            ->limit(4)
            ->get();

        return view('pages.katalog.show', [
            'barangJasa' => $barangJasa,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    public function sitemap(): Response
    {
        return response()->view('pages.sitemap', [
            'kategoris' => Kategori::query()->orderBy('urutan')->orderBy('nama')->get(),
            'produks' => BarangJasa::query()->aktif()->select(['idbarangjasa', 'updated_at'])->get(),
        ])->header('Content-Type', 'application/xml; charset=utf-8');
    }

    private function filteredProducts(Request $request): Builder
    {
        $searchQuery = trim($request->string('q')->toString());
        $selectedKategori = $request->string('kategori')->toString();
        $selectedTipe = $request->string('tipe')->toString();

        return BarangJasa::query()
            ->aktif()
            ->with('kategori')
            ->when($searchQuery !== '', function (Builder $query) use ($searchQuery): void {
                $query->where('nama_barang', 'like', "%{$searchQuery}%");
            })
            ->when($selectedKategori !== '', function (Builder $query) use ($selectedKategori): void {
                if ($selectedKategori === 'lainnya') {
                    $query->whereNull('kategori_id');

                    return;
                }

                $query->whereHas('kategori', function (Builder $categoryQuery) use ($selectedKategori): void {
                    $categoryQuery->where('slug', $selectedKategori);
                });
            })
            ->when(in_array($selectedTipe, ['fisik', 'jasa'], true), function (Builder $query) use ($selectedTipe): void {
                $query->where('tipe', $selectedTipe);
            });
    }
}
