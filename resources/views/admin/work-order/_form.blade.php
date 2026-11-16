@php
    $isEdit = isset($workOrder) && $workOrder !== null;
    $workOrder = $workOrder ?? null;
    $oldItems = old('items');
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.work-order.update', $workOrder) : route('admin.work-order.store', $po) }}" class="space-y-7">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="work-order-data-title">
        <div class="border-b border-slate-200 pb-4">
            <h2 id="work-order-data-title" class="text-xl font-black text-[#0b1f3a]">Data pekerjaan</h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">PO <span class="font-mono font-bold text-[#0b1f3a]">{{ $po->pono }}</span> · {{ $po->customer?->nama_pt ?? '-' }}</p>
        </div>
        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="tanggal_terbit" class="text-sm font-bold text-[#0b1f3a]">Tanggal terbit</label>
                <input id="tanggal_terbit" name="tanggal_terbit" type="date" value="{{ old('tanggal_terbit', $workOrder?->tanggal_terbit?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                @error('tanggal_terbit')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="lokasi_pekerjaan" class="text-sm font-bold text-[#0b1f3a]">Lokasi pekerjaan</label>
                <input id="lokasi_pekerjaan" name="lokasi_pekerjaan" value="{{ old('lokasi_pekerjaan', $workOrder?->lokasi_pekerjaan) }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Lokasi pekerjaan customer">
                @error('lokasi_pekerjaan')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="uraian_pekerjaan" class="text-sm font-bold text-[#0b1f3a]">Uraian pekerjaan</label>
                <textarea id="uraian_pekerjaan" name="uraian_pekerjaan" rows="4" required maxlength="5000" class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Jelaskan pekerjaan yang perlu dilakukan">{{ old('uraian_pekerjaan', $workOrder?->uraian_pekerjaan) }}</textarea>
                @error('uraian_pekerjaan')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="keluhan_tangki" class="text-sm font-bold text-[#0b1f3a]">Keluhan tangki <span class="font-normal text-slate-500">(opsional)</span></label>
                <textarea id="keluhan_tangki" name="keluhan_tangki" rows="3" maxlength="5000" class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Catatan keluhan atau kondisi awal">{{ old('keluhan_tangki', $workOrder?->keluhan_tangki) }}</textarea>
                @error('keluhan_tangki')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="work-order-items-title">
        <div class="border-b border-slate-200 pb-4">
            <h2 id="work-order-items-title" class="text-xl font-black text-[#0b1f3a]">Item jasa</h2>
            <p class="mt-1 text-sm leading-6 text-slate-600">Pilih item jasa yang dikerjakan. Qty tidak boleh melebihi sisa alokasi PO.</p>
        </div>
        @if (count($items) === 0)
            <div class="mt-5 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-4 text-sm leading-6 text-yellow-900">Tidak ada item jasa yang masih dapat dialokasikan pada PO ini.</div>
        @else
            <div class="mt-5 space-y-3">
                @foreach ($items as $index => $item)
                    @php
                        $detail = $item['detail'];
                        $oldItem = is_array($oldItems) && is_array($oldItems[$index] ?? null) ? $oldItems[$index] : null;
                        $selected = $oldItem !== null ? isset($oldItem['detail_po_id']) : $item['selected_qty'] > 0;
                        $selectedQty = $oldItem['qty'] ?? ($item['selected_qty'] > 0 ? $item['selected_qty'] : 1);
                    @endphp
                    <div class="grid gap-4 rounded-xl border border-slate-200 p-4 sm:grid-cols-[minmax(0,1fr)_8rem] sm:items-center" data-wo-item>
                        <div class="flex gap-3">
                            <input id="item-{{ $index }}" type="checkbox" name="items[{{ $index }}][detail_po_id]" value="{{ $detail->id }}" @checked($selected) class="mt-1 h-5 w-5 rounded border-slate-300 text-[#0b1f3a] focus:ring-yellow-300" data-wo-item-toggle>
                            <label for="item-{{ $index }}" class="min-w-0 cursor-pointer">
                                <span class="block font-bold text-[#0b1f3a]">{{ $detail->nama_barang }}</span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">PO {{ $detail->qty }} {{ $detail->unit ?: 'unit' }} · Sisa yang dapat dipilih {{ $item['available_qty'] }} {{ $detail->unit ?: 'unit' }}</span>
                            </label>
                        </div>
                        <div>
                            <label for="qty-{{ $index }}" class="text-xs font-bold uppercase tracking-[0.1em] text-slate-500">Qty WO</label>
                            <input id="qty-{{ $index }}" name="items[{{ $index }}][qty]" type="number" min="1" max="{{ $item['available_qty'] }}" value="{{ $selectedQty }}" @disabled(! $selected) class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" data-wo-item-qty>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        @error('items')<p class="mt-3 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
    </section>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ $isEdit ? route('admin.work-order.show', $workOrder) : route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
        <button type="submit" @disabled(count($items) === 0) class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:cursor-not-allowed disabled:bg-slate-400">{{ $isEdit ? 'Simpan perubahan' : 'Buat Work Order' }}</button>
    </div>
</form>

@push('scripts')
    <script>
        document.querySelectorAll('[data-wo-item]').forEach((row) => {
            const toggle = row.querySelector('[data-wo-item-toggle]');
            const quantity = row.querySelector('[data-wo-item-qty]');

            toggle?.addEventListener('change', () => {
                quantity.disabled = !toggle.checked;
                if (toggle.checked && !quantity.value) {
                    quantity.value = '1';
                }
            });
        });
    </script>
@endpush
