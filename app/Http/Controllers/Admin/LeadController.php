<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Petugas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
        ]);

        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $jenis = (string) $request->input('jenis', '');

        $leads = Lead::query()
            ->withCount('items')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('perusahaan', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists($status, Lead::STATUSES), fn (Builder $query) => $query->where('status', $status))
            ->when(array_key_exists($jenis, Lead::JENIS), fn (Builder $query) => $query->where('jenis', $jenis))
            ->when($request->filled('dari'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->date('dari')))
            ->when($request->filled('sampai'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->date('sampai')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'ringkasan' => Lead::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(Lead $lead): View
    {
        $lead->load(['items', 'penanganan']);

        return view('admin.leads.show', ['lead' => $lead]);
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'catatan_internal' => ['nullable', 'string', 'max:2000'],
        ]);

        /** @var Petugas $petugas */
        $petugas = Auth::user();

        $lead->update([
            ...$data,
            'ditangani_oleh' => $data['status'] === Lead::STATUS_BARU ? null : ($lead->ditangani_oleh ?? $petugas->idpetugas),
        ]);

        return to_route('admin.leads.show', $lead)->with('success', "Lead {$lead->kode} diperbarui.");
    }
}
