@extends('layouts.admin')

@section('title', 'Dashboard Admin Sales')

@section('content')
    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-[1.75rem] bg-[#0b1f3a] px-6 py-8 text-white shadow-xl shadow-[#0b1f3a]/15 sm:px-9 sm:py-10" aria-labelledby="dashboard-title">
            <div class="relative z-10 max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-yellow-300">Ringkasan kerja</p>
                <h1 id="dashboard-title" class="mt-3 text-3xl font-black leading-tight sm:text-5xl">Selamat datang, {{ $petugas->namapetugas }}.</h1>
                <p class="mt-4 max-w-xl text-sm leading-7 text-slate-300 sm:text-base">Mulai dari katalog yang rapi. Modul transaksi akan mengikuti setelah data dasar siap dipakai.</p>
            </div>
            <div class="absolute -right-12 -top-20 h-64 w-64 rounded-full border-[24px] border-yellow-400/20" aria-hidden="true"></div>
            <div class="absolute -bottom-24 right-24 h-40 w-40 rounded-full bg-red-600/20" aria-hidden="true"></div>
        </section>

        <section aria-labelledby="metric-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Data yang tersedia</p>
                    <h2 id="metric-title" class="mt-2 text-2xl font-black text-[#0b1f3a]">Kondisi katalog hari ini</h2>
                </div>
                <p class="text-sm text-slate-500">Angka diambil langsung dari database.</p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Produk aktif</p>
                    <p class="mt-4 text-4xl font-black text-[#0b1f3a]">{{ number_format($activeProductCount) }}</p>
                    <a href="{{ route('admin.produk.index', ['status' => 'aktif']) }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Lihat produk aktif</a>
                </article>
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">Kategori</p>
                    <p class="mt-4 text-4xl font-black text-[#0b1f3a]">{{ number_format($categoryCount) }}</p>
                    <a href="{{ route('admin.kategori.index') }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kelola kategori</a>
                </article>
                <article class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 shadow-sm">
                    <p class="text-sm font-semibold text-yellow-900">Stok fisik menipis</p>
                    <p class="mt-4 text-4xl font-black text-[#0b1f3a]">{{ number_format($lowStockCount) }}</p>
                    <a href="{{ route('admin.produk.index', ['tipe' => 'fisik']) }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg font-bold text-[#0b1f3a] underline decoration-yellow-500 decoration-2 underline-offset-4 hover:text-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Periksa stok fisik</a>
                </article>
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]" aria-labelledby="next-title">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Langkah berikutnya</p>
                <h2 id="next-title" class="mt-2 text-2xl font-black text-[#0b1f3a]">Rapikan katalog sebelum menerima order.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">Tambahkan kategori dan produk yang benar-benar dijual. Produk jasa tidak membutuhkan stok, sementara harga RFQ akan diminta saat proses penawaran tersedia.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('admin.produk.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Tambah produk</a>
                    <a href="{{ route('admin.kategori.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-[#0b1f3a] transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Tambah kategori</a>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Permintaan penawaran</p>
                <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Lead Log</h2>
                <p class="mt-3 text-4xl font-black text-[#0b1f3a]">{{ number_format($leadBaruCount) }} <span class="text-base font-bold text-slate-500">baru dari {{ number_format($leadTotalCount) }} lead</span></p>
                <p class="mt-3 text-sm leading-7 text-slate-600">Lead yang masuk dari keranjang RFQ publik dapat ditindaklanjuti dari sini.</p>
                <a href="{{ route('admin.leads.index', ['status' => 'baru']) }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Lihat lead baru</a>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7 lg:col-span-2">
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Transaksi</p>
                <h2 class="mt-2 text-2xl font-black text-[#0b1f3a]">Quotation</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @foreach (\App\Models\Quotation::STATUSES as $kode => $label)
                        <a href="{{ route('admin.quotation.index', ['status' => $kode]) }}" class="rounded-xl border border-slate-200 bg-white p-4 transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">
                            <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                            <p class="mt-1 text-2xl font-black text-[#0b1f3a]">{{ number_format($quotationStatusCounts[$kode] ?? 0) }}</p>
                        </a>
                    @endforeach
                </div>
                <p class="mt-4 text-sm leading-7 text-slate-600">PO, Surat Jalan, dan Invoice: belum tersedia.</p>
                <a href="{{ route('admin.quotation.index') }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kelola quotation</a>
                <div class="mt-6 border-t border-slate-200 pt-5">
                    <p class="text-sm font-bold text-[#0b1f3a]">Purchase Order</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach (\App\Models\Po::STATUSES as $kode => $label)
                            <a href="{{ route('admin.po.index', ['status' => $kode]) }}" class="rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-1 text-xl font-black text-[#0b1f3a]">{{ number_format($poStatusCounts[$kode] ?? 0) }}</p></a>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.po.index') }}" class="mt-4 inline-flex min-h-11 items-center font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kelola PO</a>
                </div>
            </div>
        </section>
    </div>
@endsection
