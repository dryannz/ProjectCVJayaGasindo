@extends('layouts.layout', [
    'title' => $barangJasa->nama_barang,
    'description' => $barangJasa->deskripsi ?: $barangJasa->nama_barang . ' dari CV. Mandiri Jaya Gasindo Engineering.',
])

@section('content')
    @php
        $productImage = $barangJasa->gambar
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($barangJasa->gambar)
            : null;
        $offerUrl = $barangJasa->tautan_penawaran;
        $isWhatsAppOffer = \Illuminate\Support\Str::startsWith($offerUrl, 'https://wa.me/');
    @endphp

    <section class="bg-[#0b1f3a] text-white">
        <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8 lg:py-14">
            <nav class="text-sm text-slate-300" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-yellow-300">Beranda</a>
                <span class="mx-2 text-slate-500" aria-hidden="true">/</span>
                <a href="{{ route('katalog.index') }}" class="hover:text-yellow-300">Katalog</a>
                @if ($barangJasa->kategori)
                    <span class="mx-2 text-slate-500" aria-hidden="true">/</span>
                    <a href="{{ route('katalog.kategori', ['kategori' => $barangJasa->kategori->slug]) }}" class="hover:text-yellow-300">{{ $barangJasa->kategori->nama }}</a>
                @endif
                <span class="mx-2 text-slate-500" aria-hidden="true">/</span>
                <span class="text-yellow-300" aria-current="page">{{ $barangJasa->nama_barang }}</span>
            </nav>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(20rem,.95fr)] lg:items-start">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-[#102d4f] shadow-xl">
                @if ($productImage)
                    <img src="{{ $productImage }}" alt="{{ $barangJasa->nama_barang }}" class="aspect-[4/3] h-full w-full object-cover">
                @else
                    <div class="flex aspect-[4/3] items-end justify-between bg-[radial-gradient(circle_at_top_right,_rgba(250,204,21,.42),_transparent_36%),linear-gradient(135deg,#0b1f3a,#173f66_62%,#b91c1c)] p-7 text-white">
                        <span class="text-6xl font-black tracking-[-0.08em] text-yellow-300 sm:text-8xl">MJG</span>
                        <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-100">Foto menyusul</span>
                    </div>
                @endif
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-[#0b1f3a]">{{ $barangJasa->tipe === 'jasa' ? 'Jasa' : 'Produk fisik' }}</span>
                    @if ($barangJasa->kategori)
                        <a href="{{ route('katalog.kategori', ['kategori' => $barangJasa->kategori->slug]) }}" class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-red-700 hover:bg-red-100">{{ $barangJasa->kategori->nama }}</a>
                    @endif
                </div>
                <h1 class="mt-5 text-4xl font-black leading-tight text-[#0b1f3a] sm:text-5xl">{{ $barangJasa->nama_barang }}</h1>
                <p class="mt-4 font-mono text-sm font-bold tracking-[0.12em] text-slate-500">Kode {{ $barangJasa->idbarangjasa }}</p>

                <div class="mt-8 rounded-2xl border border-slate-200 bg-[#f7f4ec] p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Informasi penawaran</p>
                    <p class="mt-2 text-2xl font-black text-[#0b1f3a]">{{ $barangJasa->label_harga }}</p>
                    @if ($barangJasa->label_ketersediaan)
                        <p class="mt-2 text-sm font-bold {{ $barangJasa->isTersedia() ? 'text-emerald-700' : 'text-red-700' }}">{{ $barangJasa->label_ketersediaan }}</p>
                    @endif
                </div>

                <dl class="mt-6 grid gap-4 border-y border-slate-200 py-6 sm:grid-cols-2">
                    @if ($barangJasa->ukuran && $barangJasa->ukuran !== '-')
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Ukuran</dt>
                            <dd class="mt-1 font-bold text-[#0b1f3a]">{{ $barangJasa->ukuran }}</dd>
                        </div>
                    @endif
                    @if ($barangJasa->unit && $barangJasa->unit !== '-')
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Satuan</dt>
                            <dd class="mt-1 font-bold text-[#0b1f3a]">{{ $barangJasa->unit }}</dd>
                        </div>
                    @endif
                    @if ($barangJasa->label_ketersediaan && $barangJasa->tipe !== 'jasa')
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Stok</dt>
                            <dd class="mt-1 font-bold text-[#0b1f3a]">{{ number_format((int) $barangJasa->stok) }} {{ $barangJasa->unit }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $offerUrl }}" @if ($isWhatsAppOffer) target="_blank" rel="noopener noreferrer" @endif class="inline-flex min-h-12 flex-1 items-center justify-center rounded-xl bg-yellow-400 px-5 text-center text-sm font-black text-[#0b1f3a] transition hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">
                        {{ $isWhatsAppOffer ? 'Minta penawaran via WhatsApp' : 'Hubungi kami' }}
                    </a>
                    <a href="{{ route('katalog.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[#0b1f3a] px-5 text-center text-sm font-bold text-[#0b1f3a] transition hover:bg-[#0b1f3a] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke katalog</a>
                </div>
            </div>
        </div>

        <div class="mt-14 max-w-3xl border-t border-slate-200 pt-10">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Tentang produk</p>
            <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Detail dan penggunaan</h2>
            <p class="mt-4 whitespace-pre-line text-base leading-8 text-slate-600">{{ $barangJasa->deskripsi ?: 'Hubungi tim kami untuk mendapatkan detail, spesifikasi, dan rekomendasi penggunaan produk ini.' }}</p>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="border-t border-slate-200 bg-[#f7f4ec]">
            <div class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Pilihan lain</p>
                        <h2 class="mt-2 text-3xl font-black text-[#0b1f3a]">Produk terkait</h2>
                    </div>
                    <a href="{{ $barangJasa->kategori ? route('katalog.kategori', ['kategori' => $barangJasa->kategori->slug]) : route('katalog.index', ['kategori' => 'lainnya']) }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] hover:bg-[#0b1f3a] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Lihat semua</a>
                </div>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($relatedProducts as $produk)
                        @include('pages.katalog._product-card', ['produk' => $produk])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection