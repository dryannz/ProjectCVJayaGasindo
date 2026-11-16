<?php

namespace App\Http\Controllers;

use App\Models\BarangJasa;
use App\Models\Kategori;
use App\Models\Lead;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function admin(): View
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();

        return view('admin.dashboard', [
            'activeProductCount' => BarangJasa::query()->aktif()->count(),
            'categoryCount' => Kategori::query()->count(),
            'lowStockCount' => BarangJasa::query()
                ->where('tipe', 'fisik')
                ->whereNotNull('stok')
                ->where('stok', '<=', config('inventory.low_stock_threshold', 5))
                ->count(),
            'leadBaruCount' => Lead::query()->baru()->count(),
            'leadTotalCount' => Lead::query()->count(),
            'poStatusCounts' => Po::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'quotationStatusCounts' => Quotation::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'petugas' => $petugas,
        ]);
    }

    public function workOrder(): View
    {
        return $this->render('Work Order Teknisi', 'melihat Work Order');
    }

    public function pimpinan(): View
    {
        return $this->render('Dashboard Pimpinan', 'laporan penjualan dan stok');
    }

    private function render(string $title, string $accessSummary): View
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();

        return view('staff.dashboard', [
            'accessSummary' => $accessSummary,
            'pageTitle' => $title,
            'petugas' => $petugas,
        ]);
    }
}
