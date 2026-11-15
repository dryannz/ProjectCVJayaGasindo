@php
    $productImage = $produk->gambar
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($produk->gambar)
        : null;
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-yellow-300 hover:shadow-xl">
    <a href="{{ route('katalog.produk', ['barangJasa' => $produk]) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#0b1f3a]" aria-label="Lihat detail {{ $produk->nama_barang }}">
        <div class="relative aspect-[4/3] overflow-hidden bg-[#102d4f]">
            @if ($productImage)
                <img src="{{ $productImage }}" alt="{{ $produk->nama_barang }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
            @else
                <div class="flex h-full items-end justify-between bg-[radial-gradient(circle_at_top_right,_rgba(250,204,21,.42),_transparent_36%),linear-gradient(135deg,#0b1f3a,#173f66_62%,#b91c1c)] p-5 text-white">
                    <span class="text-4xl font-black tracking-[-0.08em] text-yellow-300">MJG</span>
                    <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-100">Foto menyusul</span>
                </div>
            @endif
            <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-[#0b1f3a]">
                {{ $produk->tipe === 'jasa' ? 'Jasa' : 'Produk fisik' }}
            </span>
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-5 p-5">
        <div class="flex flex-1 flex-col gap-3">
            @if ($produk->kategori)
                <a href="{{ route('katalog.kategori', ['kategori' => $produk->kategori->slug]) }}" class="w-fit text-xs font-bold uppercase tracking-[0.14em] text-red-600 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                    {{ $produk->kategori->nama }}
                </a>
            @else
                <span class="w-fit text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Lainnya</span>
            @endif
            <h2 class="text-xl font-black leading-tight text-[#0b1f3a]">
                <a href="{{ route('katalog.produk', ['barangJasa' => $produk]) }}" class="hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">{{ $produk->nama_barang }}</a>
            </h2>
            @if ($produk->deskripsi)
                <p class="text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($produk->deskripsi, 105) }}</p>
            @endif
        </div>

        <div class="border-t border-slate-100 pt-4">
            <p class="text-base font-black text-[#0b1f3a]">{{ $produk->label_harga }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                @if ($produk->ukuran && $produk->ukuran !== '-')
                    <span>{{ $produk->ukuran }}</span>
                @endif
                @if ($produk->unit && $produk->unit !== '-')
                    <span aria-hidden="true">&middot;</span>
                    <span>{{ $produk->unit }}</span>
                @endif
                @if ($produk->label_ketersediaan)
                    <span aria-hidden="true">&middot;</span>
                    <span class="{{ $produk->isTersedia() ? 'text-emerald-700' : 'text-red-700' }}">{{ $produk->label_ketersediaan }}</span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('rfq.add', ['barangJasa' => $produk]) }}">
            @csrf
            <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-yellow-400 px-4 text-sm font-black text-[#0b1f3a] transition hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Tambah ke keranjang RFQ</button>
        </form>
    </div>
</article>