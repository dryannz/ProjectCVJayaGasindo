@extends('layouts.layout', [
    'title' => $kategori->nama . ' - Katalog',
    'description' => 'Lihat produk dan jasa aktif dalam kategori ' . $kategori->nama . ' dari CV. Mandiri Jaya Gasindo Engineering.',
])

@section('content')
    <section class="bg-[#0b1f3a] text-white">
        <div class="mx-auto max-w-7xl px-5 py-14 lg:px-8 lg:py-18">
            <nav class="text-sm text-slate-300" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-yellow-300">Beranda</a>
                <span class="mx-2 text-slate-500" aria-hidden="true">/</span>
                <a href="{{ route('katalog.index') }}" class="hover:text-yellow-300">Katalog</a>
                <span class="mx-2 text-slate-500" aria-hidden="true">/</span>
                <span class="text-yellow-300" aria-current="page">{{ $kategori->nama }}</span>
            </nav>
            <p class="mt-10 text-sm font-bold uppercase tracking-[0.18em] text-yellow-300">Kategori katalog</p>
            <h1 class="mt-3 text-4xl font-black sm:text-5xl">{{ $kategori->nama }}</h1>
            <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-300">Pilih produk atau layanan yang paling sesuai untuk kebutuhan operasional Anda.</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 lg:px-8 lg:py-16">
        <div class="flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-red-600">Isi kategori</p>
                <h2 class="mt-2 text-3xl font-black text-[#0b1f3a]">{{ $produkList->total() }} produk dan jasa</h2>
            </div>
            <a href="{{ route('katalog.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[#0b1f3a] px-4 text-sm font-bold text-[#0b1f3a] transition hover:bg-[#0b1f3a] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Semua katalog</a>
        </div>

        <form action="{{ route('katalog.kategori', ['kategori' => $kategori->slug]) }}" method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-[#f7f4ec] p-4 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
            <div>
                <label for="category-q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kata kunci</label>
                <input id="category-q" type="search" name="q" value="{{ $searchQuery }}" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
            </div>
            <div>
                <label for="category-tipe" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tampilkan</label>
                <select id="category-tipe" name="tipe" class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Produk & jasa</option>
                    <option value="fisik" @selected($selectedTipe === 'fisik')>Produk fisik</option>
                    <option value="jasa" @selected($selectedTipe === 'jasa')>Jasa</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white hover:bg-[#12335d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
                <a href="{{ route('katalog.kategori', ['kategori' => $kategori->slug]) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Reset filter kategori">Reset</a>
            </div>
        </form>

        @if ($produkList->count())
            <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($produkList as $produk)
                    @include('pages.katalog._product-card', ['produk' => $produk])
                @endforeach
            </div>
            @if ($produkList->hasPages())
                <div class="mt-8 border-t border-slate-200 pt-6">{{ $produkList->links() }}</div>
            @endif
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Belum ada hasil</p>
                <h3 class="mt-3 text-2xl font-black text-[#0b1f3a]">Produk di kategori ini belum cocok.</h3>
                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-600">Hapus filter atau hubungi tim kami untuk mendapatkan rekomendasi yang lebih tepat.</p>
                <a href="{{ route('katalog.kategori', ['kategori' => $kategori->slug]) }}" class="mt-6 inline-flex min-h-11 items-center justify-center rounded-xl bg-yellow-400 px-5 text-sm font-black text-[#0b1f3a] hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Hapus filter</a>
            </div>
        @endif
    </section>
@endsection