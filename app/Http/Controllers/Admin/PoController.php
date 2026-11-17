<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePoRequest;
use App\Http\Requests\UpdatePoRequest;
use App\Http\Requests\UpdatePoStatusRequest;
use App\Models\Petugas;
use App\Models\Po;
use App\Models\Quotation;
use App\Support\PoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PoController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $pos = Po::query()
            ->with(['customer', 'quotation'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('pono', 'like', "%{$search}%")
                        ->orWhere('no_po_customer', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama_pt', 'like', "%{$search}%"));
                });
            })
            ->when(array_key_exists($status, Po::STATUSES), fn (Builder $query) => $query->where('status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.po.index', [
            'pos' => $pos,
            'statuses' => Po::STATUSES,
            'statusCounts' => Po::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function create(Request $request): View
    {
        $quotation = Quotation::query()
            ->with(['customer', 'details'])
            ->findOrFail($request->integer('quotation_id'));

        abort_unless($quotation->status === Quotation::STATUS_DISETUJUI, 404);
        abort_if($quotation->po()->exists(), 404);

        return view('admin.po.create', [
            'quotation' => $quotation,
        ]);
    }

    public function store(StorePoRequest $request, PoService $poService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();
        $quotation = Quotation::query()->findOrFail($request->integer('quotation_id'));
        $po = $poService->createFromQuotation($quotation, $request->validated(), $petugas->idpetugas);

        return to_route('admin.po.show', $po)
            ->with('success', "PO {$po->pono} berhasil dibuat sebagai draft.");
    }

    public function show(Po $po): View
    {
        $po->load([
            'customer',
            'quotation',
            'petugas',
            'details.barangJasa',
            'stockMutations.petugas',
            'suratJalans.petugas',
            'workOrders.petugas',
        ]);

        return view('admin.po.show', [
            'po' => $po,
        ]);
    }

    public function edit(Po $po): View
    {
        abort_unless($po->isEditable(), 404);
        $po->load(['customer', 'quotation.details']);

        return view('admin.po.edit', [
            'po' => $po,
        ]);
    }

    public function update(UpdatePoRequest $request, Po $po, PoService $poService): RedirectResponse
    {
        $poService->updateDraft($po, $request->validated());

        return to_route('admin.po.show', $po)->with('success', "PO {$po->pono} berhasil diperbarui.");
    }

    public function updateStatus(UpdatePoStatusRequest $request, Po $po, PoService $poService): RedirectResponse
    {
        /** @var Petugas $petugas */
        $petugas = Auth::user();

        try {
            $poService->changeStatus($po, $request->validated('status'), $petugas->idpetugas);
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'status' => 'Stok tidak mencukupi: '.implode('; ', $exception->shortageMessages()),
            ]);
        }

        return to_route('admin.po.show', $po)->with('success', 'Status PO berhasil diperbarui.');
    }
}
