@extends('layouts.admin')

@section('title', 'Quotation '.$quotation->nomor_quotation)

@section('content')
    <div class="mx-auto max-w-4xl space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('admin.quotation.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke quotation</a>
                <p class="mt-7 font-mono text-sm font-bold text-slate-500">{{ $quotation->nomor_quotation }}</p>
                <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Quotation {{ $quotation->customer?->nama_pt ?? '' }}</h1>
            </div>
            <span class="inline-flex rounded-lg px-3 py-2 text-sm font-bold {{ $quotation->label_warna_status }}">{{ $quotation->label_status }}</span>
        </div>

        @if ($quotation->isEditable())
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-yellow-200 bg-yellow-50 p-4">
                <p class="text-sm font-semibold text-yellow-900">Quotation masih draft dan dapat diedit.</p>
                <a href="{{ route('admin.quotation.edit', $quotation) }}" class="ml-auto inline-flex min-h-11 items-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white hover:bg-[#132c4d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Edit quotation</a>
                <form method="POST" action="{{ route('admin.quotation.status', $quotation) }}">
                    @csrf
                    <input type="hidden" name="status" value="terkirim">
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Kirim ke customer</button>
                </form>
            </div>
        @endif

        @if ($quotation->status === \App\Models\Quotation::STATUS_TERKIRIM)
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm font-semibold text-slate-700">Tindak lanjut keputusan customer:</p>
                <form method="POST" action="{{ route('admin.quotation.status', $quotation) }}" class="ml-auto">
                    @csrf
                    <input type="hidden" name="status" value="disetujui">
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-emerald-600 px-5 text-sm font-bold text-white hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Disetujui</button>
                </form>
                <form method="POST" action="{{ route('admin.quotation.status', $quotation) }}">
                    @csrf
                    <input type="hidden" name="status" value="ditolak">
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 bg-white px-5 text-sm font-bold text-red-700 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Ditolak</button>
                </form>
            </div>
        @endif

        @include('admin.quotation._kop_surat')

        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="info-title">
            <h2 id="info-title" class="text-lg font-black text-[#0b1f3a]">Informasi quotation</h2>
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Customer</dt><dd class="mt-1 font-bold text-[#0b1f3a]">{{ $quotation->customer?->nama_pt ?? '-' }} @if($quotation->customer)<span class="font-mono text-xs text-slate-500">({{ $quotation->idcust }})</span>@endif</dd></div>
                <div><dt class="text-slate-500">Sales</dt><dd class="mt-1 font-bold text-[#0b1f3a]">{{ $quotation->petugas?->nama ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Tanggal</dt><dd class="mt-1 font-semibold text-slate-800">{{ $quotation->tanggal->translatedFormat('d F Y') }}</dd></div>
                <div><dt class="text-slate-500">Berlaku sampai</dt><dd class="mt-1 font-semibold text-slate-800">{{ $quotation->berlaku_sampai?->translatedFormat('d F Y') ?? '-' }}</dd></div>
                @if ($quotation->lead !== null)
                    <div class="sm:col-span-2"><dt class="text-slate-500">Asal lead</dt><dd class="mt-1"><a href="{{ route('admin.leads.show', $quotation->lead) }}" class="font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4">Lead #{{ $quotation->lead->id }} &mdash; {{ $quotation->lead->nama }}</a></dd></div>
                @endif
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <h2 class="sr-only">Rincian item</h2>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th class="px-5 py-4 font-bold">Item</th>
                            <th class="px-5 py-4 text-center font-bold">Qty</th>
                            <th class="px-5 py-4 text-right font-bold">Harga</th>
                            <th class="px-5 py-4 text-right font-bold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($quotation->details as $detail)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $detail->nama_barang }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ collect([$detail->ukuran, $detail->unit])->filter()->implode(' / ') ?: '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-center text-slate-700">{{ $detail->qty }} {{ $detail->unit ?? '' }}</td>
                                <td class="px-5 py-4 text-right text-slate-700">Rp {{ number_format((float) $detail->harga_satuan, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-slate-900">Rp {{ number_format((float) $detail->jumlah, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada item pada quotation ini.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 text-sm">
                        <tr><td colspan="3" class="px-5 py-2 text-right text-slate-500">Subtotal</td><td class="px-5 py-2 text-right font-semibold text-slate-900">Rp {{ number_format((float) $quotation->subtotal, 0, ',', '.') }}</td></tr>
                        <tr><td colspan="3" class="px-5 py-2 text-right text-slate-500">Diskon</td><td class="px-5 py-2 text-right font-semibold text-slate-900">-Rp {{ number_format((float) $quotation->diskon, 0, ',', '.') }}</td></tr>
                        <tr><td colspan="3" class="px-5 py-2 text-right text-slate-500">PPN</td><td class="px-5 py-2 text-right font-semibold text-slate-900">Rp {{ number_format((float) $quotation->ppn, 0, ',', '.') }}</td></tr>
                        <tr class="text-base"><td colspan="3" class="px-5 py-3 text-right font-black text-[#0b1f3a]">Total</td><td class="px-5 py-3 text-right font-black text-[#0b1f3a]">Rp {{ number_format((float) $quotation->total, 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </section>

        @if ($quotation->catatan !== null && $quotation->catatan !== '')
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-[#0b1f3a]">Catatan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $quotation->catatan }}</p>
            </section>
        @endif

        @if ($quotation->syarat !== null && $quotation->syarat !== '')
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-[#0b1f3a]">Syarat dan ketentuan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $quotation->syarat }}</p>
            </section>
        @endif
    </div>
@endsection

