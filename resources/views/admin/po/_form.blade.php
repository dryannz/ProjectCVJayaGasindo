@php
    $formPo = $po ?? null;
    $sourceItems = $formPo?->details ?? $quotation->details;
    $isEdit = $formPo !== null;
    $taxBase = (float) ($formPo?->subtotal ?? 0) - (float) ($formPo?->diskon ?? 0);
    $ppnPersen = $taxBase > 0 ? round(((float) ($formPo?->ppn ?? 0) / $taxBase) * 100, 2) : 0;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.po.update', $formPo) : route('admin.po.store') }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
    <input type="hidden" name="idcust" value="{{ $quotation->idcust }}">

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex flex-col gap-2 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Data utama</p>
                <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">PO dari {{ $quotation->nomor_quotation }}</h2>
            </div>
            <p class="text-sm text-slate-600">Customer: <span class="font-bold text-[#0b1f3a]">{{ $quotation->customer?->nama_pt }}</span></p>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div><label for="tgl_order" class="text-sm font-bold text-[#0b1f3a]">Tanggal order</label><input id="tgl_order" name="tgl_order" type="date" value="{{ old('tgl_order', $formPo?->tgl_order?->format('Y-m-d') ?? now()->toDateString()) }}" required class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
            <div><label for="schedule_delivery" class="text-sm font-bold text-[#0b1f3a]">Jadwal pengiriman</label><input id="schedule_delivery" name="schedule_delivery" type="date" value="{{ old('schedule_delivery', $formPo?->schedule_delivery?->format('Y-m-d') ?? now()->addDays(7)->toDateString()) }}" required class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
            <div><label for="term_of_payment" class="text-sm font-bold text-[#0b1f3a]">Termin pembayaran</label><input id="term_of_payment" name="term_of_payment" value="{{ old('term_of_payment', $formPo?->term_of_payment ?? '30 hari') }}" required maxlength="20" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
            <div><label for="no_po_customer" class="text-sm font-bold text-[#0b1f3a]">Nomor PO customer <span class="font-normal text-slate-500">(opsional)</span></label><input id="no_po_customer" name="no_po_customer" value="{{ old('no_po_customer', $formPo?->no_po_customer) }}" maxlength="50" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
        </div>
        <div class="mt-5 grid gap-5 sm:grid-cols-2"><div><label for="project_name" class="text-sm font-bold text-[#0b1f3a]">Nama proyek</label><input id="project_name" name="project_name" value="{{ old('project_name', $formPo?->project_name) }}" maxlength="255" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div><div><label for="project_location" class="text-sm font-bold text-[#0b1f3a]">Lokasi proyek</label><input id="project_location" name="project_location" value="{{ old('project_location', $formPo?->project_location) }}" maxlength="255" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div></div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="flex items-end justify-between gap-3 border-b border-slate-200 pb-5"><div><p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Snapshot quotation</p><h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Item PO</h2></div><p class="text-right text-xs leading-5 text-slate-500">Qty dan harga dapat disesuaikan saat masih draft.<br>Total akhir dihitung server.</p></div>
        <div class="mt-5 space-y-4">
            @foreach ($sourceItems as $index => $item)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <input type="hidden" name="items[{{ $index }}][idbarangjasa]" value="{{ $item->idbarangjasa }}">
                    <input type="hidden" name="items[{{ $index }}][nama_barang]" value="{{ $item->nama_barang }}">
                    <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_8rem_12rem] md:items-end">
                        <div><p class="font-bold text-[#0b1f3a]">{{ $item->nama_barang }}</p><p class="mt-1 text-xs text-slate-500">{{ $item->unit ?: 'Unit tidak diisi' }} · {{ $item->barangJasa?->tipe ?? $item->tipe ?? 'snapshot' }}</p></div>
                        <div><label for="item-qty-{{ $index }}" class="text-sm font-bold text-[#0b1f3a]">Qty</label><input id="item-qty-{{ $index }}" name="items[{{ $index }}][qty]" type="number" min="1" value="{{ old("items.{$index}.qty", $item->qty) }}" required class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
                        <div><label for="item-price-{{ $index }}" class="text-sm font-bold text-[#0b1f3a]">Harga satuan</label><input id="item-price-{{ $index }}" name="items[{{ $index }}][harga_satuan]" type="number" min="0" value="{{ old("items.{$index}.harga_satuan", $item->harga_satuan) }}" required class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
        <div class="grid gap-5 sm:grid-cols-3"><div><label for="diskon" class="text-sm font-bold text-[#0b1f3a]">Diskon</label><input id="diskon" name="diskon" type="number" min="0" value="{{ old('diskon', $formPo?->diskon ?? 0) }}" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div><div><label for="ppn_persen" class="text-sm font-bold text-[#0b1f3a]">PPN (%)</label><input id="ppn_persen" name="ppn_persen" type="number" min="0" max="100" step="0.01" value="{{ old('ppn_persen', $ppnPersen) }}" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"></div><div><label for="catatan" class="text-sm font-bold text-[#0b1f3a]">Catatan</label><textarea id="catatan" name="catatan" rows="3" maxlength="5000" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">{{ old('catatan', $formPo?->catatan) }}</textarea></div></div>
    </section>

    <div class="flex flex-wrap justify-end gap-3"><a href="{{ $isEdit ? route('admin.po.show', $formPo) : route('admin.po.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a><button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">{{ $isEdit ? 'Simpan perubahan' : 'Simpan PO draft' }}</button></div>
</form>