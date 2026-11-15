@extends('layouts.layout', [
    'title' => 'Keranjang RFQ',
    'description' => 'Kumpulkan produk dan jasa yang Anda butuhkan, lalu minta penawaran dari CV. Mandiri Jaya Gasindo Engineering lewat WhatsApp.',
])

@section('content')
    <section class="bg-[#0b1f3a] text-white">
        <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-yellow-300">Request for Quotation</p>
            <h1 class="mt-3 text-4xl font-black leading-tight sm:text-5xl">Keranjang RFQ</h1>
            <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-300">Kumpulkan kebutuhan Anda, isi data kontak, lalu kirim ke WhatsApp kami. Harga dan ketersediaan kami konfirmasikan lewat penawaran.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-14">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->has('keranjang'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">{{ $errors->first('keranjang') }}</div>
        @endif

        @if ($items->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Keranjang kosong</p>
                <h2 class="mt-3 text-2xl font-black text-[#0b1f3a]">Belum ada produk atau jasa yang dipilih.</h2>
                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-600">Buka katalog, lalu tekan "Tambah ke keranjang RFQ" pada produk atau jasa yang Anda butuhkan.</p>
                <a href="{{ route('katalog.index') }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-yellow-400 px-5 text-sm font-black text-[#0b1f3a] hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Jelajahi katalog</a>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(20rem,.8fr)] lg:items-start">
                <div>
                    <div class="flex items-end justify-between gap-4 border-b border-slate-200 pb-4">
                        <h2 class="text-2xl font-black text-[#0b1f3a]">{{ $items->count() }} item dalam permintaan</h2>
                        <a href="{{ route('katalog.index') }}" class="text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900">Tambah item lain</a>
                    </div>

                    <ul class="divide-y divide-slate-200">
                        @foreach ($items as $item)
                            @php($produk = $item['produk'])
                            <li class="py-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-red-600">{{ $produk->tipe === 'jasa' ? 'Jasa' : 'Produk fisik' }}@if ($produk->kategori) &middot; {{ $produk->kategori->nama }}@endif</p>
                                        <h3 class="mt-1 text-lg font-black leading-snug text-[#0b1f3a]"><a href="{{ route('katalog.produk', ['barangJasa' => $produk]) }}" class="hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">{{ $produk->nama_barang }}</a></h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $produk->idbarangjasa }}
                                            @if ($produk->ukuran && $produk->ukuran !== '-') &middot; {{ $produk->ukuran }}@endif
                                        </p>
                                        <p class="mt-2 text-sm font-bold text-[#0b1f3a]">{{ $produk->label_harga }}</p>
                                    </div>

                                    <div class="flex items-end gap-2">
                                        <form method="POST" action="{{ route('rfq.update', ['barangJasa' => $produk]) }}" class="flex items-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label for="qty-{{ $produk->idbarangjasa }}" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Jumlah{{ $produk->unit && $produk->unit !== '-' ? ' ('.$produk->unit.')' : '' }}</label>
                                                <input id="qty-{{ $produk->idbarangjasa }}" name="qty" type="number" inputmode="numeric" min="1" max="9999" value="{{ $item['qty'] }}" class="mt-1 block min-h-11 w-28 rounded-lg border border-slate-300 bg-white px-3 text-sm font-bold outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                                            </div>
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Ubah</button>
                                        </form>
                                        <form method="POST" action="{{ route('rfq.remove', ['barangJasa' => $produk]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:border-red-500 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" aria-label="Hapus {{ $produk->nama_barang }} dari keranjang">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <form method="POST" action="{{ route('rfq.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:sticky lg:top-28" novalidate>
                    @csrf
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Data pemohon</p>
                    <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Kirim permintaan</h2>

                    @if ($errors->any() && ! $errors->has('keranjang'))
                        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800" role="alert">Periksa kembali isian yang ditandai.</div>
                    @endif

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-bold text-[#0b1f3a]">Nama <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="nama" name="nama" value="{{ old('nama') }}" required autocomplete="name" @error('nama') aria-invalid="true" aria-describedby="nama-error" @enderror class="mt-1 block min-h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 {{ $errors->has('nama') ? 'border-red-500' : 'border-slate-300' }}">
                            @error('nama')<p id="nama-error" class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="perusahaan" class="block text-sm font-bold text-[#0b1f3a]">Perusahaan <span class="font-normal text-slate-500">(opsional)</span></label>
                            <input id="perusahaan" name="perusahaan" value="{{ old('perusahaan') }}" autocomplete="organization" class="mt-1 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                            @error('perusahaan')<p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="telepon" class="block text-sm font-bold text-[#0b1f3a]">No. WhatsApp / HP <span class="text-red-600" aria-hidden="true">*</span></label>
                            <input id="telepon" name="telepon" type="tel" inputmode="tel" value="{{ old('telepon') }}" required autocomplete="tel" placeholder="0812xxxxxxxx" @error('telepon') aria-invalid="true" aria-describedby="telepon-error" @enderror class="mt-1 block min-h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 {{ $errors->has('telepon') ? 'border-red-500' : 'border-slate-300' }}">
                            @error('telepon')<p id="telepon-error" class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-bold text-[#0b1f3a]">Email <span class="font-normal text-slate-500">(opsional)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="mt-1 block min-h-11 w-full rounded-lg border px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300' }}">
                            @error('email')<p id="email-error" class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="alamat" class="block text-sm font-bold text-[#0b1f3a]">{{ $adaJasa ? 'Lokasi pekerjaan' : 'Alamat pengiriman' }} @if ($adaJasa)<span class="text-red-600" aria-hidden="true">*</span>@else<span class="font-normal text-slate-500">(opsional)</span>@endif</label>
                            <textarea id="alamat" name="alamat" rows="3" @if ($adaJasa) required @endif @error('alamat') aria-invalid="true" aria-describedby="alamat-error" @enderror class="mt-1 block w-full rounded-lg border px-3 py-2 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300 {{ $errors->has('alamat') ? 'border-red-500' : 'border-slate-300' }}">{{ old('alamat') }}</textarea>
                            @error('alamat')<p id="alamat-error" class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="catatan" class="block text-sm font-bold text-[#0b1f3a]">Catatan <span class="font-normal text-slate-500">(opsional)</span></label>
                            <textarea id="catatan" name="catatan" rows="3" placeholder="Misalnya jadwal kebutuhan atau kondisi tangki" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">{{ old('catatan') }}</textarea>
                            @error('catatan')<p class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
                        </div>

                        {{-- Honeypot anti-bot: disembunyikan dari pengguna dan pembaca layar. --}}
                        <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                            <label for="website">Jangan diisi</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>
                    </div>

                    <button type="submit" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-yellow-400 px-5 text-sm font-black text-[#0b1f3a] transition hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Kirim permintaan via WhatsApp</button>
                    <p class="mt-3 text-xs leading-5 text-slate-500">Permintaan Anda dicatat, lalu WhatsApp terbuka dengan pesan yang sudah terisi. Tidak ada pembayaran pada tahap ini.</p>
                </form>
            </div>
        @endif
    </section>
@endsection
