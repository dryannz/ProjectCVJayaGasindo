<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangJasaRequest;
use App\Http\Requests\UpdateBarangJasaRequest;
use App\Models\BarangJasa;
use App\Models\Kategori;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BarangJasaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        $products = BarangJasa::query()
            ->with('kategori')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('idbarangjasa', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('tipe'), fn ($query) => $query->where('tipe', $request->string('tipe')->toString()))
            ->when($request->input('status') === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($request->input('status') === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('nama_barang')
            ->paginate(12)
            ->withQueryString();

        return view('admin.produk.index', [
            'barangJasa' => $products,
            'kategoris' => Kategori::query()->orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.produk.create', [
            'kategoris' => Kategori::query()->orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    public function store(StoreBarangJasaRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;
        $storedImage = $this->storeImage($data);

        try {
            $this->createWithGeneratedId($data);
        } catch (Throwable $exception) {
            if ($storedImage !== null) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }

        return to_route('admin.produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(BarangJasa $barangJasa): View
    {
        return view('admin.produk.edit', [
            'barangJasa' => $barangJasa,
            'kategoris' => Kategori::query()->orderBy('urutan')->orderBy('nama')->get(),
        ]);
    }

    public function update(UpdateBarangJasaRequest $request, BarangJasa $barangJasa): RedirectResponse
    {
        $data = $request->validated();
        $oldImage = $barangJasa->gambar;
        $storedImage = null;
        $shouldDeleteOldImage = false;

        if (($data['gambar'] ?? null) instanceof UploadedFile) {
            $storedImage = $this->storeImage($data);
            $shouldDeleteOldImage = $oldImage !== null;
        } elseif (($data['hapus_gambar'] ?? false) && $oldImage !== null) {
            $data['gambar'] = null;
            $shouldDeleteOldImage = true;
        }

        unset($data['hapus_gambar']);

        try {
            DB::transaction(function () use ($barangJasa, $data): void {
                $barangJasa->update($data);
            });
        } catch (Throwable $exception) {
            if ($storedImage !== null) {
                Storage::disk('public')->delete($storedImage);
            }

            throw $exception;
        }

        if ($shouldDeleteOldImage && $oldImage !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        return to_route('admin.produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function toggleStatus(BarangJasa $barangJasa): RedirectResponse
    {
        $barangJasa->update(['is_active' => ! $barangJasa->is_active]);

        return back()->with('success', 'Status produk berhasil diperbarui.');
    }

    public function destroy(BarangJasa $barangJasa): RedirectResponse
    {
        if (Schema::hasTable('detail_po') && DB::table('detail_po')->where('idbarangjasa', $barangJasa->getKey())->exists()) {
            return back()->withErrors([
                'produk' => 'Produk sudah digunakan pada PO dan tidak dapat dihapus. Nonaktifkan produk jika sudah tidak dijual.',
            ]);
        }

        $image = $barangJasa->gambar;
        $barangJasa->delete();

        if ($image !== null) {
            Storage::disk('public')->delete($image);
        }

        return to_route('admin.produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function storeImage(array &$data): ?string
    {
        if (! (($data['gambar'] ?? null) instanceof UploadedFile)) {
            unset($data['gambar'], $data['hapus_gambar']);

            return null;
        }

        $path = $data['gambar']->store('produk', 'public');
        $data['gambar'] = $path;
        unset($data['hapus_gambar']);

        return $path;
    }

    private function createWithGeneratedId(array $data): BarangJasa
    {
        $prefix = $data['tipe'] === 'jasa' ? 'JSA' : 'BRG';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($data, $prefix): BarangJasa {
                    $lastId = BarangJasa::query()
                        ->where('idbarangjasa', 'like', "{$prefix}-%")
                        ->lockForUpdate()
                        ->orderByDesc('idbarangjasa')
                        ->value('idbarangjasa');

                    $sequence = 1;

                    if (is_string($lastId) && preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', $lastId, $matches) === 1) {
                        $sequence = ((int) $matches[1]) + 1;
                    }

                    $payload = $data;
                    $payload['idbarangjasa'] = sprintf('%s-%04d', $prefix, $sequence);

                    return BarangJasa::create($payload);
                });
            } catch (QueryException $exception) {
                if (! in_array((int) $exception->getCode(), [19, 23000], true)) {
                    throw $exception;
                }
            }
        }

        throw ValidationException::withMessages([
            'nama_barang' => 'ID produk tidak dapat dibuat sekarang. Silakan coba lagi.',
        ]);
    }
}
