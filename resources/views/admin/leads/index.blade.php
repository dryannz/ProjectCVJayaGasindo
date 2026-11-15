@extends('layouts.admin')

@section('title', 'Lead Log')

@php
    $warnaStatus = [
        'baru' => 'bg-red-100 text-red-800',
        'dihubungi' => 'bg-yellow-100 text-yellow-900',
        'quotation' => 'bg-emerald-100 text-emerald-800',
        'batal' => 'bg-slate-100 text-slate-600',
    ];
@endphp

@section('content')
    <div class="space-y-7">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Lead Log</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Setiap permintaan penawaran dari keranjang RFQ publik tercatat di sini, termasuk yang dikirim ke WhatsApp.</p>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach (\App\Models\Lead::STATUSES as $kode => $label)
                <a href="{{ route('admin.leads.index', ['status' => $kode]) }}" class="rounded-2xl border bg-white p-4 shadow-sm transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] {{ request('status') === $kode ? 'border-[#0b1f3a]' : 'border-slate-200' }}">
                    <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-black text-[#0b1f3a]">{{ number_format($ringkasan[$kode] ?? 0) }}</p>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.leads.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari</label>
                <input id="q" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Kode, nama, perusahaan, no. HP">
            </div>
            <div>
                <label for="status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                <select id="status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Lead::STATUSES as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="jenis" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jenis</label>
                <select id="jenis" name="jenis" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\Lead::JENIS as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('jenis') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="dari" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Dari tanggal</label>
                <input id="dari" type="date" name="dari" value="{{ request('dari') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
            </div>
            <div>
                <label for="sampai" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Sampai tanggal</label>
                <input id="sampai" type="date" name="sampai" value="{{ request('sampai') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white hover:bg-[#12335d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
                <a href="{{ route('admin.leads.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Reset filter">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <caption class="sr-only">Daftar lead permintaan penawaran</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th scope="col" class="px-5 py-4 font-bold">Kode</th>
                            <th scope="col" class="px-5 py-4 font-bold">Pemohon</th>
                            <th scope="col" class="px-5 py-4 font-bold">Jenis</th>
                            <th scope="col" class="px-5 py-4 font-bold">Item</th>
                            <th scope="col" class="px-5 py-4 font-bold">Masuk</th>
                            <th scope="col" class="px-5 py-4 font-bold">Status</th>
                            <th scope="col" class="px-5 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($leads as $lead)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-[#0b1f3a]">{{ $lead->kode }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-bold text-[#0b1f3a]">{{ $lead->nama }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $lead->perusahaan ?: 'Perorangan' }} &middot; {{ $lead->telepon }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $lead->label_jenis }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $lead->items_count }} item</td>
                                <td class="px-5 py-4 text-slate-600"><time datetime="{{ $lead->created_at->toIso8601String() }}">{{ $lead->created_at->translatedFormat('d M Y, H:i') }}</time></td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $warnaStatus[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $lead->label_status }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Buka lead {{ $lead->kode }}">Buka</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500">Belum ada lead yang cocok dengan filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-200 md:hidden">
                @forelse ($leads as $lead)
                    <a href="{{ route('admin.leads.show', $lead) }}" class="block space-y-2 p-5 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#0b1f3a]">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-bold text-[#0b1f3a]">{{ $lead->nama }}</p>
                            <span class="shrink-0 rounded-lg px-2 py-1 text-xs font-bold {{ $warnaStatus[$lead->status] ?? 'bg-slate-100 text-slate-600' }}">{{ $lead->label_status }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $lead->perusahaan ?: 'Perorangan' }} &middot; {{ $lead->telepon }}</p>
                        <p class="text-xs font-semibold text-slate-500"><span class="font-mono">{{ $lead->kode }}</span> &middot; {{ $lead->items_count }} item &middot; {{ $lead->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </a>
                @empty
                    <p class="px-5 py-14 text-center text-sm text-slate-500">Belum ada lead yang cocok dengan filter ini.</p>
                @endforelse
            </div>

            @if ($leads->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $leads->links() }}</div>
            @endif
        </div>
    </div>
@endsection
