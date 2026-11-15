@extends('layouts.admin')

@section('title', 'Lead '.$lead->kode)

@section('content')
    <div class="space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('admin.leads.index') }}" class="inline-flex min-h-11 items-center text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">&larr; Kembali ke Lead Log</a>
                <h1 class="mt-1 text-3xl font-black text-[#0b1f3a] sm:text-4xl">{{ $lead->kode }}</h1>
                <p class="mt-2 text-sm text-slate-600">Masuk {{ $lead->created_at->translatedFormat('d F Y, H:i') }} &middot; {{ $lead->label_jenis }}</p>
            </div>
            @if ($lead->tautan_hubungi)
                <a href="{{ $lead->tautan_hubungi }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Hubungi via WhatsApp</a>
            @endif
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(18rem,.7fr)] lg:items-start">
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="pemohon">
                    <h2 id="pemohon" class="text-lg font-black text-[#0b1f3a]">Data pemohon</h2>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div><dt class="text-slate-500">Nama</dt><dd class="mt-1 font-semibold text-slate-900">{{ $lead->nama }}</dd></div>
                        <div><dt class="text-slate-500">Perusahaan</dt><dd class="mt-1 font-semibold text-slate-900">{{ $lead->perusahaan ?: '-' }}</dd></div>
                        <div><dt class="text-slate-500">No. HP / WhatsApp</dt><dd class="mt-1 font-semibold text-slate-900">{{ $lead->telepon }}</dd></div>
                        <div><dt class="text-slate-500">Email</dt><dd class="mt-1 break-words font-semibold text-slate-900">{{ $lead->email ?: '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">Alamat / lokasi</dt><dd class="mt-1 whitespace-pre-line font-semibold text-slate-900">{{ $lead->alamat ?: '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-slate-500">Catatan dari pemohon</dt><dd class="mt-1 whitespace-pre-line font-semibold text-slate-900">{{ $lead->catatan ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="item">
                    <h2 id="item" class="px-6 pt-6 text-lg font-black text-[#0b1f3a]">Item yang diminta</h2>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <caption class="sr-only">Item permintaan penawaran</caption>
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-bold">Produk / jasa</th>
                                    <th scope="col" class="px-6 py-3 font-bold">Harga tampil</th>
                                    <th scope="col" class="px-6 py-3 text-right font-bold">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($lead->items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-[#0b1f3a]">{{ $item->nama_barang }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $item->idbarangjasa }} &middot; {{ $item->tipe === 'jasa' ? 'Jasa' : 'Produk fisik' }}@if ($item->ukuran && $item->ukuran !== '-') &middot; {{ $item->ukuran }}@endif</p>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600">{{ $item->label_harga ?: '-' }}</td>
                                        <td class="px-6 py-4 text-right font-bold text-slate-900">{{ number_format($item->qty) }} {{ $item->unit && $item->unit !== '-' ? $item->unit : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <details class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <summary class="cursor-pointer text-sm font-bold text-[#0b1f3a]">Pesan WhatsApp yang dikirim pelanggan</summary>
                    <pre class="mt-4 whitespace-pre-wrap break-words rounded-lg bg-slate-50 p-4 font-sans text-sm leading-6 text-slate-700">{{ $lead->pesan_whatsapp }}</pre>
                </details>
            </div>

            <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-black text-[#0b1f3a]">Tindak lanjut</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                        <select id="status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                            @foreach (\App\Models\Lead::STATUSES as $kode => $label)
                                <option value="{{ $kode }}" @selected(old('status', $lead->status) === $kode)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="catatan_internal" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Catatan internal</label>
                        <textarea id="catatan_internal" name="catatan_internal" rows="5" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Hasil telepon, kesepakatan harga, jadwal survei">{{ old('catatan_internal', $lead->catatan_internal) }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Hanya terlihat oleh staf. Tidak ditampilkan ke pelanggan.</p>
                    </div>
                    @if ($lead->penanganan)
                        <p class="text-xs text-slate-500">Ditangani oleh <strong class="text-slate-700">{{ $lead->penanganan->namapetugas }}</strong></p>
                    @endif
                </div>
                <button type="submit" class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Simpan</button>
            </form>
        </div>
    </div>
@endsection
