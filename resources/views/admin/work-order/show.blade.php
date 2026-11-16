@extends('layouts.admin')

@section('title', 'Detail '.$workOrder->nomor_wo)

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col justify-between gap-5 rounded-[1.75rem] bg-[#0b1f3a] px-6 py-8 text-white sm:flex-row sm:items-end sm:px-9">
            <div>
                <a href="{{ route('admin.work-order.index') }}" class="inline-flex min-h-11 items-center font-bold text-yellow-300 underline decoration-yellow-300 decoration-2 underline-offset-4 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Kembali ke daftar Work Order</a>
                <p class="mt-5 text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Detail Work Order</p>
                <h1 class="mt-3 break-words font-mono text-2xl font-black sm:text-4xl">{{ $workOrder->nomor_wo }}</h1>
                <p class="mt-3 text-sm text-slate-300">PO {{ $workOrder->pono }} · {{ $workOrder->customer?->nama_pt ?? '-' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-black {{ $workOrder->status_class }}">{{ $workOrder->label_status }}</span>
                @if (! $workOrder->isTerminal())
                    <a href="{{ route('admin.work-order.edit', $workOrder) }}" class="inline-flex min-h-11 items-center rounded-lg border border-white/30 px-4 text-sm font-bold text-white hover:border-yellow-300 hover:text-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Edit data</a>
                @endif
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Data ringkas Work Order">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Customer</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $workOrder->customer?->nama_pt ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $workOrder->idcust }}</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tanggal terbit</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $workOrder->tanggal_terbit?->translatedFormat('d F Y') }}</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Lokasi</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $workOrder->lokasi_pekerjaan }}</p></div>
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-yellow-900">Teknisi</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $workOrder->petugas?->namapetugas ?? 'Belum ditugaskan' }}</p></div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(20rem,0.8fr)]">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="wo-items-title">
                    <h2 id="wo-items-title" class="text-xl font-black text-[#0b1f3a]">Item pekerjaan</h2>
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[480px] text-left text-sm">
                            <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-3 py-3 font-bold">Item jasa</th><th class="px-3 py-3 text-right font-bold">Qty</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($workOrder->details as $detail)
                                    <tr><td class="px-3 py-4 font-semibold text-slate-800">{{ $detail->nama_barang }}</td><td class="px-3 py-4 text-right font-bold text-[#0b1f3a]">{{ $detail->qty }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="px-3 py-8 text-center text-slate-500">Belum ada item pekerjaan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                    <h2 class="text-xl font-black text-[#0b1f3a]">Uraian pekerjaan</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $workOrder->uraian_pekerjaan }}</p>
                    @if ($workOrder->keluhan_tangki)
                        <h3 class="mt-6 text-sm font-bold text-[#0b1f3a]">Keluhan tangki</h3>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $workOrder->keluhan_tangki }}</p>
                    @endif
                </section>
            </div>

            <div class="space-y-6">
                @if (in_array($workOrder->status, [\App\Models\WorkOrder::STATUS_MENUNGGU, \App\Models\WorkOrder::STATUS_DITUGASKAN], true))
                    <section class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 sm:p-7" aria-labelledby="assign-title">
                        <h2 id="assign-title" class="text-xl font-black text-[#0b1f3a]">Tugaskan teknisi</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-700">Hanya teknisi aktif yang dapat menerima Work Order.</p>
                        <form method="POST" action="{{ route('admin.work-order.assign', $workOrder) }}" class="mt-5 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div><label for="idpetugas" class="text-sm font-bold text-[#0b1f3a]">Teknisi</label><select id="idpetugas" name="idpetugas" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"><option value="">Pilih teknisi</option>@foreach ($technicians as $technician)<option value="{{ $technician->idpetugas }}" @selected($workOrder->idpetugas === $technician->idpetugas)>{{ $technician->namapetugas }}</option>@endforeach</select></div>
                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Simpan penugasan</button>
                        </form>
                    </section>
                @endif

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="admin-note-title">
                    <h2 id="admin-note-title" class="text-xl font-black text-[#0b1f3a]">Catatan admin</h2>
                    <form method="POST" action="{{ route('admin.work-order.admin-notes', $workOrder) }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')
                        <textarea id="catatan_admin" name="catatan_admin" rows="5" maxlength="5000" class="block w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Catatan internal untuk pelaksanaan pekerjaan">{{ old('catatan_admin', $workOrder->catatan_admin) }}</textarea>
                        <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Simpan catatan</button>
                    </form>
                </section>

                @if ($workOrder->catatan_teknisi)
                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-7"><h2 class="text-xl font-black text-[#0b1f3a]">Catatan teknisi</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $workOrder->catatan_teknisi }}</p></section>
                @endif

                @if ($workOrder->status === \App\Models\WorkOrder::STATUS_DIBATALKAN)
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-7"><h2 class="text-xl font-black text-red-900">Alasan pembatalan</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-red-800">{{ $workOrder->alasan_batal }}</p></section>
                @elseif (! $workOrder->isTerminal())
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-7"><h2 class="text-xl font-black text-red-900">Batalkan Work Order</h2><p class="mt-2 text-sm leading-6 text-red-800">Pembatalan mengembalikan qty jasa ke sisa alokasi PO.</p><form method="POST" action="{{ route('admin.work-order.cancel', $workOrder) }}" class="mt-5 space-y-4" onsubmit="return window.confirm('Batalkan Work Order ini?')">@csrf @method('PATCH')<textarea id="alasan_batal" name="alasan_batal" rows="3" required maxlength="2000" class="block w-full rounded-lg border border-red-300 bg-white px-3 py-3 text-sm focus:border-red-700 focus:ring-2 focus:ring-red-300" placeholder="Tulis alasan pembatalan"></textarea><button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-4 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Batalkan Work Order</button></form></section>
                @endif
            </div>
        </section>
    </div>
@endsection
