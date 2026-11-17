@extends('layouts.admin')

@section('title', 'Surat Jalan')

@section('content')
    <div class="space-y-7">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Dokumen operasional</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Surat Jalan</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Buat, terbitkan, dan pantau penerimaan dokumen pengiriman dari PO yang sedang diproses.</p>
        </div>

        <form method="GET" action="{{ route('admin.surat-jalan.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-[minmax(0,1.6fr)_12rem_14rem_auto] lg:items-end">
            <div>
                <label for="surat-jalan-search" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari Surat Jalan</label>
                <input id="surat-jalan-search" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nomor SJ, PO, atau customer">
            </div>
            <div>
                <label for="surat-jalan-status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                <select id="surat-jalan-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="surat-jalan-jenis" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jenis</label>
                <select id="surat-jalan-jenis" name="jenis" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua jenis</option>
                    @foreach ($jenis as $value => $label)
                        <option value="{{ $value }}" @selected(request('jenis') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white transition hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="surat-jalan-list-title">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 p-5 sm:p-7">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Alur pengiriman</p>
                    <h2 id="surat-jalan-list-title" class="mt-2 text-xl font-black text-[#0b1f3a]">Daftar Surat Jalan</h2>
                </div>
                <a href="{{ route('admin.po.index', ['status' => 'diproses']) }}" class="inline-flex min-h-11 items-center rounded-lg border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Lihat PO diproses</a>
            </div>
            @if ($suratJalans->isEmpty())
                <div class="px-5 py-14 text-center">
                    <p class="font-bold text-[#0b1f3a]">Belum ada Surat Jalan yang cocok.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Surat Jalan dibuat dari detail PO yang berstatus Diproses.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-left text-sm">
                        <caption class="sr-only">Daftar Surat Jalan admin</caption>
                        <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                            <tr>
                                <th class="px-5 py-4 font-bold">Nomor SJ</th>
                                <th class="px-5 py-4 font-bold">Customer</th>
                                <th class="px-5 py-4 font-bold">PO</th>
                                <th class="px-5 py-4 font-bold">Tanggal</th>
                                <th class="px-5 py-4 font-bold">Jenis</th>
                                <th class="px-5 py-4 font-bold">Status</th>
                                <th class="px-5 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($suratJalans as $suratJalan)
                                <tr class="align-top hover:bg-slate-50">
                                    <td class="px-5 py-4"><a href="{{ route('admin.surat-jalan.show', $suratJalan) }}" class="font-mono font-bold text-[#0b1f3a] underline decoration-yellow-400 decoration-2 underline-offset-4 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">{{ $suratJalan->nosuratjalan }}</a></td>
                                    <td class="px-5 py-4"><p class="font-bold text-slate-800">{{ $suratJalan->customer?->nama_pt ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $suratJalan->idcust }}</p></td>
                                    <td class="px-5 py-4">@if ($suratJalan->po)<a href="{{ route('admin.po.show', $suratJalan->po) }}" class="font-mono text-xs font-bold text-slate-700 hover:text-red-700">{{ $suratJalan->pono }}</a>@else<span class="font-mono text-xs text-slate-500">{{ $suratJalan->pono ?: 'Tidak terhubung' }}</span>@endif</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $suratJalan->tgl?->translatedFormat('d F Y') }}</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $suratJalan->label_jenis }}</td>
                                    <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $suratJalan->status_class }}">{{ $suratJalan->label_status }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.surat-jalan.show', $suratJalan) }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Buka detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-5 py-4 sm:px-7">
                    {{ $suratJalans->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
