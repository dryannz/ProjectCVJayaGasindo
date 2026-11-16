<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Quotation;
use App\Support\QuotationPersistService;
use App\Support\QuotationService;
use App\Support\QuotationStatusService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');

        $quotations = Quotation::query()
            ->with(['customer', 'petugas'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nomor_quotation', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn (Builder $q) => $q->where('nama_pt', 'like', "%{$search}%"));
                });
            })
            // kadaluarsa adalah status efektif, bukan nilai kolom: filter khusus.
            ->when($status === Quotation::STATUS_KADALUARSA, function (Builder $query): void {
                $query->where('status', Quotation::STATUS_TERKIRIM)
                    ->whereNotNull('berlaku_sampai')
                    ->whereDate('berlaku_sampai', '<', now()->toDateString());
            })
            ->when(array_key_exists($status, Quotation::STATUSES) && $status !== Quotation::STATUS_KADALUARSA, fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.quotation.index', [
            'quotations' => $quotations,
            'ringkasan' => $this->ringkasanStatus(),
        ]);
    }

    public function create(Request $request): View
    {
        $lead = $this->leadDariRequest($request);

        if ($lead !== null) {
            $lead->load('items');
        }

        return view('admin.quotation.create', [
            'lead' => $lead,
            'customers' => Customer::query()->orderBy('nama_pt')->get(),
            'customerSaran' => $lead !== null ? QuotationService::cariCustomerDariLead($lead) : null,
            'customerDariLead' => $lead !== null ? QuotationService::dataCustomerDariLead($lead) : null,
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = QuotationPersistService::normalisasiItems($data['items']);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu item dengan nama barang dan qty wajib diisi.',
            ]);
        }

        $lead = isset($data['lead_id']) ? Lead::find($data['lead_id']) : null;

        // Mode "baru": buat customer langsung dari data lead / form dalam proses yang sama.
        if (($data['customer_mode'] ?? '') === 'baru') {
            $data['idcust'] = $this->buatCustomerBaru($data['customer_baru'], $lead)->idcust;
        }

        $quotation = QuotationStatusService::simpan([
            ...$data,
            'idpetugas' => Auth::user()->idpetugas,
        ], $items, $lead);

        return to_route('admin.quotation.show', $quotation)
            ->with('success', "Quotation {$quotation->nomor_quotation} berhasil dibuat.");
    }

    /** @param array<string, mixed> $dataCustomer */
    private function buatCustomerBaru(array $dataCustomer, ?Lead $lead): Customer
    {
        // Prioritaskan data yang sudah terisi admin; sisanya isi dari lead.
        if ($lead !== null) {
            $dataCustomer = [
                ...QuotationService::dataCustomerDariLead($lead),
                ...array_filter($dataCustomer, fn ($value): bool => $value !== null && $value !== ''),
            ];
        }

        return Customer::create([
            'idcust' => app(CustomerController::class)->generateIdcust(),
            'nama_pt' => $dataCustomer['nama_pt'],
            'notelp_pt' => $dataCustomer['notelp_pt'],
            'alamat_pt' => $dataCustomer['alamat_pt'],
            'pic' => $dataCustomer['pic'] ?? null,
        ]);
    }

    private function leadDariRequest(Request $request): ?Lead
    {
        $leadId = $request->integer('lead_id');

        if ($leadId <= 0) {
            return null;
        }

        return Lead::query()->findOrFail($leadId);
    }

    /** @return Collection<string, int> */
    private function ringkasanStatus(): Collection
    {
        return Quotation::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer', 'petugas', 'details', 'lead', 'po']);

        return view('admin.quotation.show', ['quotation' => $quotation]);
    }

    public function edit(Quotation $quotation): View
    {
        $quotation->load(['details', 'customer']);

        return view('admin.quotation.edit', [
            'quotation' => $quotation,
            'customers' => Customer::query()->orderBy('nama_pt')->get(),
        ]);
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validated();

        if ($quotation->status === Quotation::STATUS_DRAFT) {
            $items = QuotationPersistService::normalisasiItems($data['items'] ?? []);

            if ($items === []) {
                throw ValidationException::withMessages([
                    'items' => 'Minimal satu item dengan nama barang dan qty wajib diisi.',
                ]);
            }

            QuotationStatusService::perbaruiItems(
                $quotation,
                $items,
                (int) ($data['diskon'] ?? 0),
                (float) ($data['ppn_persen'] ?? 0),
            );

            $quotation->update([
                'idcust' => $data['idcust'],
                'tanggal' => $data['tanggal'],
                'berlaku_sampai' => $data['berlaku_sampai'] ?? null,
                'catatan' => $data['catatan'] ?? null,
                'syarat' => $data['syarat'] ?? null,
            ]);

            return to_route('admin.quotation.show', $quotation)
                ->with('success', "Quotation {$quotation->nomor_quotation} berhasil diperbarui.");
        }

        // Setelah terkirim: hanya transisi status yang valid dan catatan.
        $statusBaru = $data['status'] ?? null;

        if ($statusBaru !== null && $statusBaru !== $quotation->status) {
            if (! Quotation::transisiValid($quotation->status, $statusBaru)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak valid untuk quotation ini.',
                ]);
            }

            $quotation->update(['status' => $statusBaru]);
        }

        if (array_key_exists('catatan', $data)) {
            $quotation->update(['catatan' => $data['catatan']]);
        }

        return to_route('admin.quotation.show', $quotation)
            ->with('success', "Quotation {$quotation->nomor_quotation} diperbarui.");
    }

    /** Ubah status via tombol aksi cepat di halaman detail. */
    public function updateStatus(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.Quotation::STATUS_TERKIRIM.','.Quotation::STATUS_DISETUJUI.','.Quotation::STATUS_DITOLAK],
        ]);

        if (! Quotation::transisiValid($quotation->status, $data['status'])) {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status tidak valid untuk quotation ini.',
            ]);
        }

        $quotation->update(['status' => $data['status']]);

        return to_route('admin.quotation.show', $quotation)
            ->with('success', 'Status quotation diperbarui.');
    }
}
