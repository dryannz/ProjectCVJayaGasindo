<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelSuratJalanRequest;
use App\Http\Requests\ReceiveSuratJalanRequest;
use App\Http\Requests\StoreSuratJalanRequest;
use App\Http\Requests\UpdateSuratJalanNotesRequest;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\SuratJalan;
use App\Support\SuratJalanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SuratJalanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $jenis = (string) $request->input('jenis', '');

        $suratJalans = SuratJalan::query()
            ->with(['customer', 'po', 'petugas'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nosuratjalan', 'like', "%{$search}%")
                        ->orWhere('pono', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_pt', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, SuratJalan::STATUSES), fn (Builder $query) => $query->where('status', $status))
            ->when(array_key_exists($jenis, SuratJalan::JENIS), fn (Builder $query) => $query->where('jenis', $jenis))
            ->latest('tgl')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.surat-jalan.index', [
            'suratJalans' => $suratJalans,
            'statuses' => SuratJalan::STATUSES,
            'jenis' => SuratJalan::JENIS,
        ]);
    }

    public function create(Po $po, SuratJalanService $suratJalanService): View
    {
        abort_unless($po->canCreateSuratJalan(), 404);
        $po->load(['customer', 'details.barangJasa']);

        return view('admin.surat-jalan.create', [
            'po' => $po,
            'items' => $suratJalanService->remainingItems($po),
        ]);
    }

    public function store(StoreSuratJalanRequest $request, Po $po, SuratJalanService $suratJalanService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $suratJalan = $suratJalanService->createFromPo($po, $request->validated(), $petugas->idpetugas);

        return to_route('admin.surat-jalan.show', $suratJalan)
            ->with('success', "Surat Jalan {$suratJalan->nosuratjalan} berhasil dibuat sebagai draft.");
    }

    public function show(SuratJalan $suratJalan): View
    {
        $suratJalan->load([
            'customer',
            'po.customer',
            'petugas',
            'details.detailPo.barangJasa',
        ]);

        return view('admin.surat-jalan.show', [
            'suratJalan' => $suratJalan,
        ]);
    }

    public function publish(SuratJalan $suratJalan, SuratJalanService $suratJalanService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();

        try {
            $suratJalanService->publish($suratJalan, $petugas->idpetugas);
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'status' => 'Stok tidak mencukupi: '.implode('; ', $exception->shortageMessages()),
            ]);
        }

        return to_route('admin.surat-jalan.show', $suratJalan)
            ->with('success', 'Surat Jalan berhasil diterbitkan.');
    }

    public function receive(ReceiveSuratJalanRequest $request, SuratJalan $suratJalan, SuratJalanService $suratJalanService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $suratJalanService->receive($suratJalan, $request->validated(), $petugas->idpetugas);

        return to_route('admin.surat-jalan.show', $suratJalan)
            ->with('success', 'Surat Jalan ditandai sudah diterima.');
    }

    public function cancel(CancelSuratJalanRequest $request, SuratJalan $suratJalan, SuratJalanService $suratJalanService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $suratJalanService->cancel($suratJalan, (string) $request->validated('alasan_batal'), $petugas->idpetugas);

        return to_route('admin.surat-jalan.show', $suratJalan)
            ->with('success', 'Surat Jalan dibatalkan.');
    }

    public function updateNotes(UpdateSuratJalanNotesRequest $request, SuratJalan $suratJalan, SuratJalanService $suratJalanService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $suratJalanService->updateNotes($suratJalan, $request->validated('catatan'), $petugas->idpetugas);

        return to_route('admin.surat-jalan.show', $suratJalan)
            ->with('success', 'Catatan Surat Jalan berhasil disimpan.');
    }
}
