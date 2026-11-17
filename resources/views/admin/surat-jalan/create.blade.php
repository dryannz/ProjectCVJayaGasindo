@extends('layouts.admin')

@section('title', 'Buat Surat Jalan')

@section('content')
    @php
        $oldItems = collect(old('items', []))->keyBy('detail_po_id');
    @endphp

    <div class="space-y-6">
        <section class="rounded-[1.75rem] bg-[#0b1f3a] px-6 py-8 text-white shadow-xl shadow-[#0b1f3a]/15 sm:px-9">
            <a href="{{ route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center font-bold text-yellow-300 underline decoration-yellow-300 decoration-2 underline-offset-4 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-300">Kembali ke detail PO</a>
            <p class="mt-5 text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Dokumen operasional</p>
            <h1 class="mt-3 text-3xl font-black sm:text-5xl">Buat Surat Jalan</h1>
            <p class="mt-3 font-mono text-sm text-slate-300">PO {{ $po->pono }}</p>
            <p class="mt-2 text-sm leading-6 text-slate-300">Sisa qty hanya dihitung dari Surat Jalan yang sudah diterbitkan atau diterima.</p>
        </section>

        <form method="POST" action="{{ route('admin.surat-jalan.store', $po) }}" class="space-y-6">
            @csrf
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="surat-jalan-information-title">
                <div class="border-b border-slate-200 pb-5">
                    <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Identitas dokumen</p>
                    <h2 id="surat-jalan-information-title" class="mt-2 text-xl font-black text-[#0b1f3a]">Data Surat Jalan</h2>
                </div>
                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="tgl" class="text-sm font-bold text-[#0b1f3a]">Tanggal Surat Jalan</label>
                        <input id="tgl" name="tgl" type="date" value="{{ old('tgl', now()->toDateString()) }}" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    </div>
                    <div>
                        <label for="jenis" class="text-sm font-bold text-[#0b1f3a]">Jenis</label>
                        <select id="jenis" name="jenis" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                            @foreach (
                                \App\Models\SuratJalan::JENIS as $value => $label
                            )
                                <option value="{{ $value }}" @selected(old('jenis', \App\Models\SuratJalan::JENIS_PENGIRIMAN) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="attn" class="text-sm font-bold text-[#0b1f3a]">Ditujukan kepada</label>
                        <input id="attn" name="attn" value="{{ old('attn', $po->customer?->pic) }}" maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nama penerima atau PIC">
                    </div>
                    <div>
                        <label for="mobil" class="text-sm font-bold text-[#0b1f3a]">Kendaraan</label>
                        <input id="mobil" name="mobil" value="{{ old('mobil') }}" maxlength="100" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nomor kendaraan, bila ada">
                    </div>
                    <div>
                        <label for="rev" class="text-sm font-bold text-[#0b1f3a]">Revisi</label>
                        <input id="rev" name="rev" value="{{ old('rev', 'Rev - 00') }}" maxlength="20" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="surat-jalan-items-title">
                <div class="border-b border-slate-200 p-5 sm:p-7">
                    <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Alokasi PO</p>
                    <h2 id="surat-jalan-items-title" class="mt-2 text-xl font-black text-[#0b1f3a]">Pilih item yang dikirim</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Qty maksimal mengikuti sisa PO. Item jasa tetap dapat dicantumkan, tetapi tidak mengubah stok.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <caption class="sr-only">Item PO yang dapat dimasukkan ke Surat Jalan</caption>
                        <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                            <tr>
                                <th class="w-16 px-5 py-4 font-bold">Pilih</th>
                                <th class="px-5 py-4 font-bold">Item</th>
                                <th class="px-5 py-4 text-center font-bold">Qty PO</th>
                                <th class="px-5 py-4 text-center font-bold">Sudah dialokasikan</th>
                                <th class="px-5 py-4 text-center font-bold">Qty Surat Jalan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($items as $item)
                                @php
                                    $detail = $item['detail'];
                                    $oldItem = $oldItems->get((string) $detail->id, $oldItems->get($detail->id, []));
                                    $isSelected = $oldItem !== [];
                                    $requestedQty = $oldItem['qty'] ?? $item['remaining'];
                                @endphp
                                <tr class="align-top {{ $item['remaining'] < 1 ? 'bg-slate-50 text-slate-400' : '' }}">
                                    <td class="px-5 py-4"><input type="hidden" name="items[{{ $detail->id }}][detail_po_id]" value="{{ $detail->id }}"><label for="select-item-{{ $detail->id }}" class="sr-only">Pilih {{ $detail->nama_barang }}</label><input id="select-item-{{ $detail->id }}" type="checkbox" name="items[{{ $detail->id }}][selected]" value="1" @checked($isSelected) @disabled($item['remaining'] < 1) class="h-5 w-5 rounded border-slate-300 text-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></td>
                                    <td class="px-5 py-4"><p class="font-bold text-slate-900">{{ $detail->nama_barang }}</p><p class="mt-1 text-xs text-slate-500">{{ $detail->unit ?: 'Unit tidak diisi' }} - {{ ucfirst($detail->barangJasa?->tipe ?? 'snapshot') }}</p></td>
                                    <td class="px-5 py-4 text-center">{{ $detail->qty }}</td>
                                    <td class="px-5 py-4 text-center">{{ $item['used'] }}</td>
                                    <td class="px-5 py-4"><label class="sr-only" for="qty-{{ $detail->id }}">Qty Surat Jalan untuk {{ $detail->nama_barang }}</label><input id="qty-{{ $detail->id }}" name="items[{{ $detail->id }}][qty]" type="number" min="1" max="{{ max(1, $item['remaining']) }}" value="{{ $requestedQty }}" @disabled($item['remaining'] < 1) class="block min-h-11 w-28 rounded-lg border border-slate-300 px-3 text-center text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-12 text-center text-slate-600">PO ini belum memiliki item.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
                <label for="catatan" class="text-sm font-bold text-[#0b1f3a]">Catatan</label>
                <textarea id="catatan" name="catatan" rows="4" maxlength="5000" class="mt-2 block w-full rounded-lg border border-slate-300 px-3 py-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Catatan pengiriman, bila ada">{{ old('catatan') }}</textarea>
                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white transition hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Simpan sebagai draft</button>
                    <a href="{{ route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-slate-700 hover:border-[#0b1f3a] hover:text-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
                </div>
            </section>
        </form>
    </div>
@endsection
