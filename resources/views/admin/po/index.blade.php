@extends('layouts.admin')

@section('title', 'Purchase Order')

@section('content')
    <div class="space-y-7">
        <section class="flex flex-col justify-between gap-5 rounded-[1.75rem] bg-[#0b1f3a] px-6 py-8 text-white shadow-xl shadow-[#0b1f3a]/15 sm:flex-row sm:items-end sm:px-9">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Dokumen transaksi</p>
                <h1 class="mt-3 text-3xl font-black leading-tight sm:text-5xl">Purchase Order</h1>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">Kelola PO yang berasal dari Quotation disetujui, lalu kendalikan pengurangan stok dari satu alur yang tercatat.</p>
            </div>
            <a href="{{ route('admin.quotation.index', ['status' => 'disetujui']) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-yellow-400 px-5 py-3 text-sm font-black text-[#0b1f3a] transition hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Pilih Quotation disetujui</a>
        </section>

        <section class="grid gap-3 sm:grid-cols-4" aria-label="Ringkasan status PO">
            @foreach ($statuses as $status => $label)
                <a href="{{ route('admin.po.index', ['status' => $status]) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $label }}</p>
                    <p class="mt-3 text-3xl font-black text-[#0b1f3a]">{{ number_format((int) ($statusCounts[$status] ?? 0)) }}</p>
                </a>
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="po-list-title">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Daftar dokumen</p>
                    <h2 id="po-list-title" class="mt-2 text-2xl font-black text-[#0b1f3a]">PO yang tersimpan</h2>
                </div>
                <form method="GET" action="{{ route('admin.po.index') }}" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_12rem_auto]">
                    <label class="sr-only" for="po-search">Cari PO</label>
                    <input id="po-search" name="q" value="{{ request('q') }}" placeholder="Nomor PO atau customer" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <label class="sr-only" for="po-status">Filter status</label>
                    <select id="po-status" name="status" class="min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="min-h-11 rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white transition hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
                </form>
            </div>

            @if ($pos->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                    <p class="text-base font-bold text-[#0b1f3a]">Belum ada PO yang cocok.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Buat PO dari Quotation dengan status Disetujui untuk memulai.</p>
                </div>
            @else
                <div class="mt-6 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-3 py-4 font-bold">Nomor PO</th>
                                <th class="px-3 py-4 font-bold">Customer</th>
                                <th class="px-3 py-4 font-bold">Tanggal</th>
                                <th class="px-3 py-4 font-bold">Jadwal</th>
                                <th class="px-3 py-4 text-right font-bold">Total</th>
                                <th class="px-3 py-4 font-bold">Status</th>
                                <th class="px-3 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($pos as $po)
                                <tr class="align-top">
                                    <td class="px-3 py-4"><a href="{{ route('admin.po.show', $po) }}" class="font-black text-[#0b1f3a] underline decoration-yellow-400 decoration-2 underline-offset-4">{{ $po->pono }}</a><p class="mt-1 text-xs text-slate-500">{{ $po->quotation?->nomor_quotation ?? 'Tanpa nomor quotation' }}</p></td>
                                    <td class="px-3 py-4 font-semibold text-slate-800">{{ $po->customer?->nama_pt ?? '-' }}</td>
                                    <td class="px-3 py-4 text-slate-700">{{ $po->tgl_order?->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-4 text-slate-700">{{ $po->schedule_delivery?->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-4 text-right font-bold text-[#0b1f3a]">Rp {{ number_format((float) $po->total, 0, ',', '.') }}</td>
                                    <td class="px-3 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $po->status_class }}">{{ $po->label_status }}</span></td>
                                    <td class="px-3 py-4 text-right"><a href="{{ route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center rounded-lg px-3 font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Buka detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">{{ $pos->links() }}</div>
            @endif
        </section>
    </div>
@endsection