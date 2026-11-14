<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        return view('admin.kategori.index', [
            'kategoris' => Kategori::query()
                ->withCount('barangJasa')
                ->orderBy('urutan')
                ->orderBy('nama')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.kategori.create');
    }

    public function store(StoreKategoriRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['nama']);
        $data['urutan'] ??= 0;

        Kategori::create($data);

        return to_route('admin.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Kategori $kategori): View
    {
        return view('admin.kategori.edit', compact('kategori'));
    }

    public function update(UpdateKategoriRequest $request, Kategori $kategori): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->uniqueSlug($data['nama'], $kategori->getKey());
        $data['urutan'] ??= 0;
        $kategori->update($data);

        return to_route('admin.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori): RedirectResponse
    {
        $productCount = $kategori->barangJasa()->count();

        DB::transaction(function () use ($kategori): void {
            $kategori->barangJasa()->update(['kategori_id' => null]);
            $kategori->delete();
        });

        $message = 'Kategori berhasil dihapus.';

        if ($productCount > 0) {
            $message .= " {$productCount} produk menjadi tanpa kategori.";
        }

        return to_route('admin.kategori.index')->with('success', $message);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'kategori';
        $slug = $baseSlug;
        $suffix = 2;

        while (Kategori::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
