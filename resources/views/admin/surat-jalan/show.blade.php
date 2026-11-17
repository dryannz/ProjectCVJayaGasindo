@extends('layouts.admin')

@section('title', 'Detail '.$suratJalan->nosuratjalan)

@section('content')
    @php
        $needsStock = $suratJalan->jenis === \App\Models\SuratJalan::JENIS_PENGIRIMAN;
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col justify-between gap-5 rounded-[1.75rem] bg-[#0b1f3a] px-6 py-8 text-white shadow-xl shadow-[#0b1f3a]/15 sm:flex-row sm:items-end sm:px-9">
            <div>
                <a href="{{ route('admin.surat-jalan.index') }}" class="inline-flex min-h-11 items-center font-bold text-yellow-300 underline decoration-yellow-300 decoration-2 underline-offset-4 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Kembali ke daftar Surat Jalan</a>
                <p class="mt-5 text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Detail Surat Jalan</p>
                <h1 class="mt-3 break-words text-3xl font-black sm:text-5xl">{{ $suratJalan->nosuratjalan }}</h1>
                <p class="mt-3 text-sm text-slate-300">PO @if ($suratJalan->po)<a href="{{ route('admin.po.show', $suratJalan->po) }}" class="font-mono font-bold text-white underline decoration-yellow-300 underline-offset-4">{{ $suratJalan->pono }}</a>@else<span class="font-mono text-slate-400">{{ $suratJalan->pono ?: 'Tidak terhubung' }}</span>@endif</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex min-h-11 items-center rounded-full px-4 py-2 text-sm font-black {{ $suratJalan->status_class }}">{{ $suratJalan->label_status }}</span>
                <button type="button" disabled class="inline-flex min-h-11 items-center rounded-lg border border-white/20 px-4 text-sm font-bold text-slate-400" title="Fitur cetak PDF belum tersedia">Cetak PDF - Menyusul</button>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Data ringkas Surat Jalan">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Customer</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $suratJalan->customer?->nama_pt ?? '-' }}</p><p class="mt-1 text-xs text-slate-500">{{ $suratJalan->idcust }}</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tanggal dokumen</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $suratJalan->tgl?->translatedFormat('d F Y') }}</p></div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jenis</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $suratJalan->label_jenis }}</p></div>
            <div class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-[0.12em] text-yellow-900">Revisi</p><p class="mt-3 font-black text-[#0b1f3a]">{{ $suratJalan->rev }}</p></div>
        </section>

        @if ($suratJalan->status === \App\Models\SuratJalan::STATUS_DRAFT)
            <section class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5" aria-labelledby="draft-actions-title">
                <h2 id="draft-actions-title" class="text-xl font-black text-[#0b1f3a]">Tindakan draft</h2>
                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $needsStock ? 'Penerbitan akan mengurangi stok fisik sesuai item pengiriman.' : 'Jenis ini tidak mengubah stok fisik saat diterbitkan.' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.surat-jalan.publish', $suratJalan) }}" onsubmit="return window.confirm('{{ $needsStock ? 'Terbitkan Surat Jalan? Stok fisik akan berkurang.' : 'Terbitkan Surat Jalan ini?' }}')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terbitkan Surat Jalan</button>
                    </form>
                </div>
            </section>
        @elseif ($suratJalan->status === \App\Models\SuratJalan::STATUS_DITERBITKAN)
            <section class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 sm:p-7" aria-labelledby="receive-title">
                <h2 id="receive-title" class="text-xl font-black text-[#0b1f3a]">Konfirmasi penerimaan</h2>
                <p class="mt-2 text-sm leading-6 text-slate-700">Isi data penerima ketika dokumen sudah diterima customer atau lokasi tujuan.</p>
                <form method="POST" action="{{ route('admin.surat-jalan.receive', $suratJalan) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PATCH')
                    <div><label for="nama_penerima" class="text-sm font-bold text-[#0b1f3a]">Nama penerima</label><input id="nama_penerima" name="nama_penerima" value="{{ old('nama_penerima') }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
                    <div><label for="tanggal_diterima" class="text-sm font-bold text-[#0b1f3a]">Tanggal diterima</label><input id="tanggal_diterima" name="tanggal_diterima" type="date" value="{{ old('tanggal_diterima', now()->toDateString()) }}" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
                    <div class="md:col-span-2"><button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Simpan penerimaan</button></div>
                </form>
            </section>
        @elseif ($suratJalan->status === \App\Models\SuratJalan::STATUS_DITERIMA)
            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 sm:p-7" aria-labelledby="received-title">
                <h2 id="received-title" class="text-xl font-black text-emerald-950">Dokumen sudah diterima</h2>
                <p class="mt-2 text-sm leading-6 text-emerald-900">{{ $suratJalan->nama_penerima }} pada {{ $suratJalan->tanggal_diterima?->translatedFormat('d F Y') }}.</p>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="surat-jalan-items-title">
            <div class="border-b border-slate-200 p-5 sm:p-7"><p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Isi dokumen</p><h2 id="surat-jalan-items-title" class="mt-2 text-xl font-black text-[#0b1f3a]">Rincian item</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <caption class="sr-only">Rincian item Surat Jalan</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200"><tr><th class="px-5 py-4 font-bold">Item</th><th class="px-5 py-4 text-center font-bold">Tipe</th><th class="px-5 py-4 text-center font-bold">Qty</th><th class="px-5 py-4 font-bold">Unit</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($suratJalan->details as $detail)
                            <tr><td class="px-5 py-4"><p class="font-bold text-slate-900">{{ $detail->nama_barang }}</p><p class="mt-1 text-xs text-slate-500">Detail PO #{{ $detail->detail_po_id }}</p></td><td class="px-5 py-4 text-center"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $detail->detailPo?->barangJasa?->tipe === 'jasa' ? 'bg-slate-100 text-slate-700' : 'bg-yellow-100 text-yellow-900' }}">{{ ucfirst($detail->detailPo?->barangJasa?->tipe ?? 'snapshot') }}</span></td><td class="px-5 py-4 text-center font-bold text-slate-800">{{ $detail->qty }}</td><td class="px-5 py-4 text-slate-700">{{ $detail->unit ?: '-' }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-slate-600">Belum ada item pada Surat Jalan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="surat-jalan-note-title">
                    <h2 id="surat-jalan-note-title" class="text-xl font-black text-[#0b1f3a]">Catatan</h2>
                    @if ($suratJalan->status === \App\Models\SuratJalan::STATUS_DIBATALKAN)
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $suratJalan->catatan ?: 'Tidak ada catatan.' }}</p>
                    @else
                        <form method="POST" action="{{ route('admin.surat-jalan.notes', $suratJalan) }}" class="mt-5 space-y-4">
                            @csrf
                            @method('PATCH')
                            <label for="catatan" class="sr-only">Catatan Surat Jalan</label>
                            <textarea id="catatan" name="catatan" rows="5" maxlength="5000" class="block w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Catatan operasional">{{ old('catatan', $suratJalan->catatan) }}</textarea>
                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Simpan catatan</button>
                        </form>
                    @endif
                </section>
                @if ($suratJalan->mobil || $suratJalan->attn)
                    <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:p-7"><h2 class="text-xl font-black text-[#0b1f3a]">Informasi pengiriman</h2><dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-500">Ditujukan kepada</dt><dd class="text-right font-bold text-slate-800">{{ $suratJalan->attn ?: '-' }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Kendaraan</dt><dd class="text-right font-bold text-slate-800">{{ $suratJalan->mobil ?: '-' }}</dd></div></dl></section>
                @endif
            </div>

            <div class="space-y-6">
                @if ($suratJalan->status === \App\Models\SuratJalan::STATUS_DIBATALKAN)
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-7"><h2 class="text-xl font-black text-red-900">Alasan pembatalan</h2><p class="mt-3 whitespace-pre-line text-sm leading-7 text-red-800">{{ $suratJalan->alasan_batal }}</p></section>
                @else
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-7" aria-labelledby="cancel-title">
                        <h2 id="cancel-title" class="text-xl font-black text-red-900">Batalkan Surat Jalan</h2>
                        <p class="mt-2 text-sm leading-6 text-red-800">{{ $needsStock && $suratJalan->isActive() ? 'Pembatalan akan mengembalikan stok fisik yang sudah dikurangi.' : 'Pembatalan menghentikan dokumen ini dari alokasi aktif PO.' }}</p>
                        <form method="POST" action="{{ route('admin.surat-jalan.cancel', $suratJalan) }}" class="mt-5 space-y-4" onsubmit="return window.confirm('{{ $needsStock && $suratJalan->isActive() ? 'Batalkan Surat Jalan? Stok fisik akan dikembalikan.' : 'Batalkan Surat Jalan ini?' }}')">
                            @csrf
                            @method('PATCH')
                            <label for="alasan_batal" class="sr-only">Alasan pembatalan</label>
                            <textarea id="alasan_batal" name="alasan_batal" rows="3" required maxlength="2000" class="block w-full rounded-lg border border-red-300 bg-white px-3 py-3 text-sm focus:border-red-700 focus:ring-2 focus:ring-red-300" placeholder="Tulis alasan pembatalan">{{ old('alasan_batal') }}</textarea>
                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-4 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Batalkan Surat Jalan</button>
                        </form>
                    </section>
                @endif
            </div>
        </section>
    </div>
@endsection
