@php
    $isEdit = isset($quotation) && $quotation !== null;
    $model = $isEdit ? $quotation : null;
    $isLocked = $isEdit && ! $model->isEditable();
    $leadData = $lead ?? null;
    $customerTerpilih = old('idcust', $model?->idcust ?? ($customerSaran?->idcust));
    $modeCustomer = old('customer_mode', $model !== null || $customerTerpilih !== null ? 'existing' : 'baru');
    $barisAwal = old('items');

    if ($barisAwal === null) {
        $barisAwal = $model?->details?->map(fn ($d): array => [
            'idbarangjasa' => $d->idbarangjasa,
            'nama_barang' => $d->nama_barang,
            'tipe' => $d->tipe,
            'ukuran' => $d->ukuran,
            'unit' => $d->unit,
            'qty' => (string) $d->qty,
            'harga_satuan' => (string) $d->harga_satuan,
        ])->all() ?? [];
    }

    if ($barisAwal === [] && $leadData !== null) {
        // Salin item lead sebagai item awal; harga kosong kecuali produk fix.
        $barisAwal = $leadData->items->map(function ($item): array {
            $produk = $item->idbarangjasa !== null ? \App\Models\BarangJasa::find($item->idbarangjasa) : null;
            $harga = $produk !== null ? \App\Support\QuotationService::hargaAwal($produk, null)['harga_satuan'] : null;

            return [
                'idbarangjasa' => $item->idbarangjasa,
                'nama_barang' => $item->nama_barang,
                'tipe' => $item->tipe,
                'ukuran' => $item->ukuran,
                'unit' => $item->unit,
                'qty' => (string) $item->qty,
                'harga_satuan' => $harga !== null ? (string) $harga : '',
            ];
        })->values()->all();
    }

    if ($barisAwal === []) {
        $barisAwal = [['idbarangjasa' => '', 'nama_barang' => '', 'tipe' => 'fisik', 'ukuran' => '', 'unit' => '', 'qty' => '1', 'harga_satuan' => '']];
    }
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.quotation.update', $model) : route('admin.quotation.store') }}" class="space-y-8" data-quotation-form data-ppn-default="{{ old('ppn_persen', 0) }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    @if ($leadData !== null && ! $isEdit)
        <input type="hidden" name="lead_id" value="{{ $leadData->id }}">
    @endif


    <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="cust-title">
        <div class="border-b border-slate-200 pb-4">
            <h2 id="cust-title" class="text-lg font-black text-[#0b1f3a]">Customer &amp; masa berlaku</h2>
            @if (! $isEdit && $leadData !== null && $customerSaran !== null)
                <p class="mt-1 text-sm text-emerald-700">Customer serupa ditemukan: <strong>{{ $customerSaran->nama_pt }}</strong> ({{ $customerSaran->idcust }}). Pilih itu, atau buat baru di bawah.</p>
            @endif
        </div>

        @if (! $isEdit && $leadData !== null)
            <fieldset class="space-y-3">
                <legend class="text-sm font-bold text-[#0b1f3a]">Sumber data customer</legend>
                <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-4 text-sm">
                    <input type="radio" name="customer_mode" value="existing" @checked($modeCustomer === 'existing') data-customer-mode class="h-4 w-4 border-slate-300 text-red-600 focus:ring-red-500">
                    <span>Pakai customer yang sudah ada</span>
                </label>
                <label class="flex min-h-11 items-center gap-3 rounded-xl border border-slate-200 px-4 text-sm">
                    <input type="radio" name="customer_mode" value="baru" @checked($modeCustomer === 'baru') data-customer-mode class="h-4 w-4 border-slate-300 text-red-600 focus:ring-red-500">
                    <span>Buat customer baru dari data lead</span>
                </label>
            </fieldset>

            <div data-customer-existing class="{{ $modeCustomer === 'existing' ? '' : 'hidden' }}">
                <label for="idcust" class="text-sm font-bold text-[#0b1f3a]">Customer</label>
                <select id="idcust" name="idcust" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Pilih customer</option>
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->idcust }}" @selected((string) $customerTerpilih === (string) $cust->idcust)>{{ $cust->nama_pt }} ({{ $cust->idcust }})</option>
                    @endforeach
                </select>
                @error('idcust')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>

            <div data-customer-baru class="grid gap-5 sm:grid-cols-2 {{ $modeCustomer === 'baru' ? '' : 'hidden' }}">
                <div class="sm:col-span-2">
                    <label for="cb_nama_pt" class="text-sm font-bold text-[#0b1f3a]">Nama perusahaan</label>
                    <input id="cb_nama_pt" name="customer_baru[nama_pt]" value="{{ old('customer_baru.nama_pt', $customerDariLead['nama_pt'] ?? '') }}" maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    @error('customer_baru.nama_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cb_notelp_pt" class="text-sm font-bold text-[#0b1f3a]">Nomor telepon</label>
                    <input id="cb_notelp_pt" name="customer_baru[notelp_pt]" value="{{ old('customer_baru.notelp_pt', $customerDariLead['notelp_pt'] ?? '') }}" maxlength="20" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    @error('customer_baru.notelp_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cb_pic" class="text-sm font-bold text-[#0b1f3a]">PIC <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="cb_pic" name="customer_baru[pic]" value="{{ old('customer_baru.pic', $customerDariLead['pic'] ?? '') }}" maxlength="50" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    @error('customer_baru.pic')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="cb_alamat_pt" class="text-sm font-bold text-[#0b1f3a]">Alamat</label>
                    <input id="cb_alamat_pt" name="customer_baru[alamat_pt]" value="{{ old('customer_baru.alamat_pt', $customerDariLead['alamat_pt'] ?? '') }}" maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    @error('customer_baru.alamat_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>
        @else
            <div>
                <label for="idcust" class="text-sm font-bold text-[#0b1f3a]">Customer</label>
                <select id="idcust" name="idcust" @disabled($isLocked) class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100 disabled:text-slate-500">
                    @foreach ($customers as $cust)
                        <option value="{{ $cust->idcust }}" @selected((string) old('idcust', $model?->idcust) === (string) $cust->idcust)>{{ $cust->nama_pt }} ({{ $cust->idcust }})</option>
                    @endforeach
                </select>
                @error('idcust')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="tanggal" class="text-sm font-bold text-[#0b1f3a]">Tanggal</label>
                <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', $model?->tanggal?->toDateString() ?? now()->toDateString()) }}" @disabled($isLocked) required class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                @error('tanggal')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="berlaku_sampai" class="text-sm font-bold text-[#0b1f3a]">Berlaku sampai <span class="font-normal text-slate-500">(opsional)</span></label>
                <input id="berlaku_sampai" type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai', $model?->berlaku_sampai?->toDateString()) }}" @disabled($isLocked) class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                @error('berlaku_sampai')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="item-title">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h2 id="item-title" class="text-lg font-black text-[#0b1f3a]">Item penawaran</h2>
                <p class="mt-1 text-sm text-slate-500">Harga disimpan sebagai snapshot: mengubah harga produk kelak tidak mengubah dokumen ini.</p>
            </div>
            @if (! $isLocked)
                <button type="button" data-add-item class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">+ Tambah item</button>
            @endif
        </div>

        @if ($isLocked)
            <p class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-900">Quotation sudah terkirim. Isi item terkunci; hanya status dan catatan yang dapat diubah.</p>
        @endif

        <div data-item-rows class="space-y-4">
            @foreach ($barisAwal as $i => $baris)
                <div data-item-row class="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-12">
                    <input type="hidden" name="items[{{ $i }}][idbarangjasa]" value="{{ $baris['idbarangjasa'] }}">
                    <div class="sm:col-span-5">
                        <label for="item_{{ $i }}_nama" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Nama barang / jasa</label>
                        <input id="item_{{ $i }}_nama" name="items[{{ $i }}][nama_barang]" value="{{ $baris['nama_barang'] }}" @disabled($isLocked) required maxlength="255" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="item_{{ $i }}_qty" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Qty</label>
                        <input id="item_{{ $i }}_qty" type="number" min="1" step="1" name="items[{{ $i }}][qty]" value="{{ $baris['qty'] }}" @disabled($isLocked) required data-item-qty class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                    </div>
                    <div class="sm:col-span-3">
                        <label for="item_{{ $i }}_harga" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Harga satuan (Rp)</label>
                        <input id="item_{{ $i }}_harga" type="number" min="0" step="1" name="items[{{ $i }}][harga_satuan]" value="{{ $baris['harga_satuan'] }}" @disabled($isLocked) required data-item-harga class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                    </div>
                    <div class="flex items-end sm:col-span-2">
                        <input type="hidden" name="items[{{ $i }}][tipe]" value="{{ $baris['tipe'] }}">
                        <input type="hidden" name="items[{{ $i }}][ukuran]" value="{{ $baris['ukuran'] }}">
                        <input type="hidden" name="items[{{ $i }}][unit]" value="{{ $baris['unit'] }}">
                        @if (! $isLocked)
                            <button type="button" data-remove-item class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" aria-label="Hapus item {{ $i + 1 }}">Hapus</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" aria-labelledby="total-title">
        <h2 id="total-title" class="text-lg font-black text-[#0b1f3a]">Catatan, syarat &amp; total</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="diskon" class="text-sm font-bold text-[#0b1f3a]">Diskon (Rp)</label>
                <input id="diskon" type="number" min="0" step="1" name="diskon" value="{{ old('diskon', $model?->diskon ?? 0) }}" @disabled($isLocked) data-diskon class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                @error('diskon')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ppn_persen" class="text-sm font-bold text-[#0b1f3a]">PPN (%) <span class="font-normal text-slate-500">(opsional, default 0)</span></label>
                <input id="ppn_persen" type="number" min="0" max="100" step="0.5" name="ppn_persen" value="{{ old('ppn_persen', 0) }}" @disabled($isLocked) data-ppn class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">
                @error('ppn_persen')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="catatan" class="text-sm font-bold text-[#0b1f3a]">Catatan</label>
                <textarea id="catatan" name="catatan" rows="3" @disabled($isLocked) class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">{{ old('catatan', $model?->catatan) }}</textarea>
                @error('catatan')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="syarat" class="text-sm font-bold text-[#0b1f3a]">Syarat dan ketentuan <span class="font-normal text-slate-500">(opsional)</span></label>
                <textarea id="syarat" name="syarat" rows="4" @disabled($isLocked) class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 disabled:bg-slate-100">{{ old('syarat', $model?->syarat) }}</textarea>
                @error('syarat')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <dl class="space-y-2 rounded-xl bg-slate-50 p-4 text-sm" data-total-preview>
            <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-900" data-subtotal>Rp 0</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Diskon</dt><dd class="font-semibold text-slate-900" data-diskon-label>Rp 0</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">PPN</dt><dd class="font-semibold text-slate-900" data-ppn-label>Rp 0</dd></div>
            <div class="flex justify-between border-t border-slate-200 pt-2 text-base"><dt class="font-black text-[#0b1f3a]">Total</dt><dd class="font-black text-[#0b1f3a]" data-total>Rp 0</dd></div>
        </dl>
        <p class="text-xs text-slate-500">Pratinjau di layar ini bersifat bantu. Total resmi dihitung ulang di server saat disimpan.</p>
    </section>

    <div class="flex flex-wrap justify-end gap-3">
        <a href="{{ $isEdit ? route('admin.quotation.show', $model) : route('admin.quotation.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
        <button type="submit" @disabled($isLocked) class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 disabled:bg-slate-300">{{ $isEdit ? 'Simpan perubahan' : 'Simpan quotation' }}</button>
    </div>
</form>

@if (! $isLocked)
    @push('scripts')
        <script>
            (() => {
                const form = document.querySelector('[data-quotation-form]');
                if (!form) return;

                const rows = form.querySelector('[data-item-rows]');
                const rupiah = (n) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(n)));

                const hitung = () => {
                    let subtotal = 0;
                    form.querySelectorAll('[data-item-row]').forEach((row) => {
                        const qty = parseFloat(row.querySelector('[data-item-qty]')?.value) || 0;
                        const harga = parseFloat(row.querySelector('[data-item-harga]')?.value) || 0;
                        subtotal += Math.max(0, qty) * Math.max(0, harga);
                    });
                    const diskon = Math.min(Math.max(0, parseFloat(form.querySelector('[data-diskon]')?.value) || 0), subtotal);
                    const ppnPersen = Math.max(0, parseFloat(form.querySelector('[data-ppn]')?.value) || 0);
                    const dasar = subtotal - diskon;
                    const ppn = Math.round(dasar * ppnPersen / 100);
                    form.querySelector('[data-subtotal]').textContent = rupiah(subtotal);
                    form.querySelector('[data-diskon-label]').textContent = rupiah(diskon);
                    form.querySelector('[data-ppn-label]').textContent = rupiah(ppn);
                    form.querySelector('[data-total]').textContent = rupiah(dasar + ppn);
                };

                form.addEventListener('input', hitung);

                form.querySelector('[data-add-item]')?.addEventListener('click', () => {
                    const i = rows.querySelectorAll('[data-item-row]').length;
                    const wrap = document.createElement('div');
                    wrap.setAttribute('data-item-row', '');
                    wrap.className = 'grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-12';
                    wrap.innerHTML = `
                        <input type="hidden" name="items[\${i}][idbarangjasa]" value="">
                        <div class="sm:col-span-5">
                            <label class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Nama barang / jasa</label>
                            <input name="items[\${i}][nama_barang]" required maxlength="255" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Qty</label>
                            <input type="number" min="1" step="1" value="1" name="items[\${i}][qty]" required data-item-qty class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Harga satuan (Rp)</label>
                            <input type="number" min="0" step="1" name="items[\${i}][harga_satuan]" required data-item-harga class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                        </div>
                        <div class="flex items-end sm:col-span-2">
                            <input type="hidden" name="items[\${i}][tipe]" value="fisik">
                            <input type="hidden" name="items[\${i}][ukuran]" value="">
                            <input type="hidden" name="items[\${i}][unit]" value="">
                            <button type="button" data-remove-item class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:bg-red-50" aria-label="Hapus item">Hapus</button>
                        </div>`;
                    rows.appendChild(wrap);
                    hitung();
                });

                form.addEventListener('click', (event) => {
                    const btn = event.target instanceof Element ? event.target.closest('[data-remove-item]') : null;
                    if (!btn) return;
                    btn.closest('[data-item-row]')?.remove();
                    hitung();
                });

                document.querySelectorAll('[data-customer-mode]').forEach((radio) => {
                    radio.addEventListener('change', () => {
                        const pakaiBaru = form.querySelector('[data-customer-mode]:checked')?.value === 'baru';
                        form.querySelector('[data-customer-existing]')?.classList.toggle('hidden', pakaiBaru);
                        form.querySelector('[data-customer-baru]')?.classList.toggle('hidden', !pakaiBaru);
                    });
                });

                hitung();
            })();
        </script>
    @endpush
@endif

