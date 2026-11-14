@php
    $isEdit = $barangJasa !== null;
    $selectedType = old('tipe', $barangJasa?->tipe ?? 'fisik');
    $selectedPriceMode = old('mode_harga', $barangJasa?->mode_harga ?? 'fix');
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @if ($formMethod !== 'POST')
        @method($formMethod)
    @endif

    <section class="space-y-5" aria-labelledby="identity-title">
        <div class="border-b border-slate-200 pb-4"><h2 id="identity-title" class="text-lg font-black text-[#0b1f3a]">Identitas produk</h2><p class="mt-1 text-sm text-slate-500">Informasi yang tampil di katalog.</p></div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="tipe" class="text-sm font-bold text-[#0b1f3a]">Tipe</label>
                <select id="tipe" name="tipe" required data-product-type class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="fisik" @selected($selectedType === 'fisik')>Barang fisik</option>
                    <option value="jasa" @selected($selectedType === 'jasa')>Jasa</option>
                </select>
                @error('tipe')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kategori_id" class="text-sm font-bold text-[#0b1f3a]">Kategori <span class="font-normal text-slate-500">(opsional)</span></label>
                <select id="kategori_id" name="kategori_id" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Tanpa kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) old('kategori_id', $barangJasa?->kategori_id) === (string) $kategori->id)>{{ $kategori->nama }}</option>
                    @endforeach
                </select>
                @error('kategori_id')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="nama_barang" class="text-sm font-bold text-[#0b1f3a]">Nama produk</label>
                <input id="nama_barang" name="nama_barang" value="{{ old('nama_barang', $barangJasa?->nama_barang) }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" autofocus>
                @error('nama_barang')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="deskripsi" class="text-sm font-bold text-[#0b1f3a]">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="5000" class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">{{ old('deskripsi', $barangJasa?->deskripsi) }}</textarea>
                @error('deskripsi')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ukuran" class="text-sm font-bold text-[#0b1f3a]">Ukuran</label>
                <input id="ukuran" name="ukuran" value="{{ old('ukuran', $barangJasa?->ukuran) }}" required maxlength="50" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Contoh: 1/2 inch">
                @error('ukuran')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="unit" class="text-sm font-bold text-[#0b1f3a]">Unit</label>
                <input id="unit" name="unit" value="{{ old('unit', $barangJasa?->unit) }}" required maxlength="30" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Contoh: pcs">
                @error('unit')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="space-y-5" aria-labelledby="commercial-title">
        <div class="border-b border-slate-200 pb-4"><h2 id="commercial-title" class="text-lg font-black text-[#0b1f3a]">Harga & stok</h2><p class="mt-1 text-sm text-slate-500">Aturan harga dan stok mengikuti tipe produk.</p></div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="mode_harga" class="text-sm font-bold text-[#0b1f3a]">Mode harga</label>
                <select id="mode_harga" name="mode_harga" required data-price-mode class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="fix" @selected($selectedPriceMode === 'fix')>Harga tetap</option>
                    <option value="rfq" @selected($selectedPriceMode === 'rfq')>Tanya harga (RFQ)</option>
                    <option value="khusus" @selected($selectedPriceMode === 'khusus')>Harga khusus pelanggan</option>
                </select>
                @error('mode_harga')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div data-price-field>
                <label for="price" class="text-sm font-bold text-[#0b1f3a]">Harga <span data-price-optional class="font-normal text-slate-500"></span></label>
                <div class="mt-2 flex rounded-lg border border-slate-300 focus-within:border-[#0b1f3a] focus-within:ring-2 focus-within:ring-yellow-300">
                    <span class="flex items-center border-r border-slate-200 px-3 text-sm font-bold text-slate-500">Rp</span>
                    <input id="price" name="price" value="{{ old('price', $barangJasa?->price) }}" type="number" min="0.01" step="0.01" data-price-input class="min-h-12 min-w-0 flex-1 rounded-r-lg border-0 px-3 text-sm outline-none focus:ring-0">
                </div>
                @error('price')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div data-stock-field>
                <label for="stok" class="text-sm font-bold text-[#0b1f3a]">Stok</label>
                <input id="stok" name="stok" value="{{ old('stok', $barangJasa?->stok) }}" type="number" min="0" step="1" data-stock-input class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                <p data-stock-help class="mt-2 text-xs text-slate-500">Wajib untuk barang fisik.</p>
                @error('stok')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="space-y-5" aria-labelledby="media-title">
        <div class="border-b border-slate-200 pb-4"><h2 id="media-title" class="text-lg font-black text-[#0b1f3a]">Gambar & status</h2><p class="mt-1 text-sm text-slate-500">JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</p></div>
        <div class="grid gap-5 sm:grid-cols-[auto_1fr] sm:items-start">
            <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-xl bg-yellow-100 ring-1 ring-slate-200">
                @if ($barangJasa?->gambar)
                    <img data-image-preview src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($barangJasa->gambar) }}" alt="Gambar {{ $barangJasa->nama_barang }}" class="h-full w-full object-cover">
                @else
                    <img data-image-preview src="" alt="Pratinjau gambar produk" class="hidden h-full w-full object-cover">
                    <span data-image-placeholder class="text-xs font-black text-[#0b1f3a]">MJGE</span>
                @endif
            </div>
            <div>
                <label for="gambar" class="text-sm font-bold text-[#0b1f3a]">Gambar produk <span class="font-normal text-slate-500">(opsional)</span></label>
                <input id="gambar" name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp" data-image-input class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-yellow-100 file:px-3 file:py-2 file:font-bold file:text-[#0b1f3a] focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                @if ($isEdit && $barangJasa->gambar)
                    <label class="mt-3 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-slate-700"><input type="hidden" name="hapus_gambar" value="0"><input type="checkbox" name="hapus_gambar" value="1" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500"> Hapus gambar saat disimpan</label>
                @endif
                @error('gambar')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-4">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" value="1" type="checkbox" @checked(old('is_active', $barangJasa?->is_active ?? true)) class="mt-1 h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500">
            <div><label for="is_active" class="text-sm font-bold text-[#0b1f3a]">Produk aktif</label><p class="mt-1 text-xs leading-5 text-slate-500">Produk nonaktif tetap tersimpan, tetapi tidak dihitung sebagai katalog aktif.</p></div>
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
        <a href="{{ route('admin.produk.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
        <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">{{ $isEdit ? 'Simpan perubahan' : 'Simpan produk' }}</button>
    </div>
</form>

@push('scripts')
    <script>
        (() => {
            const typeSelect = document.querySelector('[data-product-type]');
            const priceMode = document.querySelector('[data-price-mode]');
            const stockField = document.querySelector('[data-stock-field]');
            const stockInput = document.querySelector('[data-stock-input]');
            const priceField = document.querySelector('[data-price-field]');
            const priceInput = document.querySelector('[data-price-input]');
            const priceOptional = document.querySelector('[data-price-optional]');
            const imageInput = document.querySelector('[data-image-input]');
            const imagePreview = document.querySelector('[data-image-preview]');
            const imagePlaceholder = document.querySelector('[data-image-placeholder]');

            const syncFields = () => {
                const isService = typeSelect?.value === 'jasa';
                const isRfq = priceMode?.value === 'rfq';
                const isSpecial = priceMode?.value === 'khusus';

                stockField?.classList.toggle('hidden', isService);
                if (stockInput) {
                    stockInput.disabled = isService;
                    stockInput.required = !isService;
                }

                priceField?.classList.toggle('hidden', isRfq);
                if (priceInput) {
                    priceInput.disabled = isRfq;
                    priceInput.required = !isRfq && !isSpecial;
                }

                if (priceOptional) {
                    priceOptional.textContent = isSpecial ? '(opsional)' : '';
                }
            };

            typeSelect?.addEventListener('change', syncFields);
            priceMode?.addEventListener('change', syncFields);
            imageInput?.addEventListener('change', (event) => {
                const [file] = event.target.files;
                if (!file || !imagePreview) {
                    return;
                }

                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove('hidden');
                imagePlaceholder?.classList.add('hidden');
            });
            syncFields();
        })();
    </script>
@endpush
