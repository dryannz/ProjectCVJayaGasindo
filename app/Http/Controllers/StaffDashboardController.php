<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffDashboardController extends Controller
{
    public function admin(): View
    {
        return $this->render('Dashboard Admin / Sales', 'katalog, quotation, PO, dan invoice');
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
