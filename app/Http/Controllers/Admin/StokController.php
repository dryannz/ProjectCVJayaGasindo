<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\BarangJasa;
use App\Models\Petugas;
use App\Models\StokMutasi;
use App\Support\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StokController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $selectedProduct = (string) $request->input('produk', '');
        $lowOnly = $request->boolean('menipis');
        $threshold = (int) config('inventory.low_stock_threshold', 5);

        $products = BarangJasa::query()
            ->where('tipe', 'fisik')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('idbarangjasa', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%");
                });
            })
            ->when($lowOnly, fn (Builder $query) => $query->where('stok', '<=', $threshold))
            ->orderBy('nama_barang')
            ->get();

        $mutations = StokMutasi::query()
            ->with(['barangJasa', 'petugas', 'po'])
            ->when($selectedProduct !== '', fn (Builder $query) => $query->where('idbarangjasa', $selectedProduct))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stok.index', [
            'products' => $products,
            'mutations' => $mutations,
            'selectedProduct' => $selectedProduct,
            'threshold' => $threshold,
            'totalPhysicalProducts' => BarangJasa::query()->where('tipe', 'fisik')->count(),
            'lowStockCount' => BarangJasa::query()->where('tipe', 'fisik')->where('stok', '<=', $threshold)->count(),
        ]);
    }

    public function adjust(StockAdjustmentRequest $request, StockService $stockService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $data = $request->validated();

        if ($data['action'] === 'masuk') {
            $stockService->masuk(
                $data['idbarangjasa'],
                (int) $data['qty'],
                'manual',
                null,
                $data['keterangan'],
                $petugas->idpetugas,
            );
        } else {
            $stockService->koreksi(
                $data['idbarangjasa'],
                (int) $data['stok_baru'],
                $data['keterangan'],
                $petugas->idpetugas,
            );
        }

        return to_route('admin.stok.index', ['produk' => $data['idbarangjasa']])
            ->with('success', 'Perubahan stok berhasil dicatat.');
    }
}
