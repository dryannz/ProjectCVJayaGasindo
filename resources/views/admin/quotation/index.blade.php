@extends('layouts.admin')

@section('title', 'Quotation')

@php
    $warnaStatus = \App\Models\Quotation::STATUSES;
@endphp

@section('content')
    <div class="space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
                <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Quotation</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Dokumen penawaran harga untuk customer, dibuat dari lead atau langsung.</p>
            </div>
            <a href="{{ route('admin.quotation.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Buat quotation</a>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach (\App\Models\Quotation::STATUSES as $kode => $label)
                <a href="{{ route('admin.quotation.index', ['status' => $kode]) }}" class="rounded-2xl border bg-white p-4 shadow-sm transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a] {{ request('status') === $kode ? 'border-[#0b1f3a]' : 'border-slate-200' }}">
                    <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-black text-[#0b1f3a]">{{ number_format($ringkasan[$kode] ?? 0) }}</p>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.quotation.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1.8fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari quotation</label>
                <input id="q" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nomor quotation atau nama PT">
            </div>
            <div>
                <label for="status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                <select id="status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Quotation::STATUSES as $kode => $label)
                        <option value="{{ $kode }}" @selected(request('status') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-[#0b1f3a] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#132c4d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Cari</button>
            </div>
        </form>


        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <caption class="sr-only">Daftar quotation</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th class="px-5 py-4 font-bold">Nomor</th>
                            <th class="px-5 py-4 font-bold">Tanggal</th>
                            <th class="px-5 py-4 font-bold">Customer</th>
                            <th class="px-5 py-4 text-right font-bold">Total</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                            <th class="px-5 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($quotations as $quotation)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-[#0b1f3a]">{{ $quotation->nomor_quotation }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $quotation->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-4 font-bold text-[#0b1f3a]">{{ $quotation->customer?->nama_pt ?? '-' }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-900">Rp {{ number_format((float) $quotation->total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $quotation->label_warna_status }}">{{ $quotation->label_status }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.quotation.show', $quotation) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Buka quotation {{ $quotation->nomor_quotation }}">Buka</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Belum ada quotation yang cocok dengan filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-200 md:hidden">
                @forelse ($quotations as $quotation)
                    <a href="{{ route('admin.quotation.show', $quotation) }}" class="block space-y-2 p-5 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#0b1f3a]">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-mono text-xs font-bold text-[#0b1f3a]">{{ $quotation->nomor_quotation }}</p>
                            <span class="shrink-0 rounded-lg px-2 py-1 text-xs font-bold {{ $quotation->label_warna_status }}">{{ $quotation->label_status }}</span>
                        </div>
                        <p class="font-bold text-[#0b1f3a]">{{ $quotation->customer?->nama_pt ?? '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $quotation->tanggal->translatedFormat('d M Y') }} &middot; Rp {{ number_format((float) $quotation->total, 0, ',', '.') }}</p>
                    </a>
                @empty
                    <p class="px-5 py-14 text-center text-sm text-slate-500">Belum ada quotation yang cocok dengan filter ini.</p>
                @endforelse
            </div>

            @if ($quotations->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $quotations->links() }}</div>
            @endif
        </div>
    </div>
@endsection
