@extends('layouts.admin')

@section('title', 'Produk')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Katalog</p>
                <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Produk & jasa</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Kelola barang fisik dan layanan dengan status, harga, stok, dan gambar yang jelas.</p>
            </div>
            <a href="{{ route('admin.produk.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Tambah produk</a>
        </div>

        <form method="GET" action="{{ route('admin.produk.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[1.8fr_1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari produk</label>
                <input id="q" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nama atau ID produk">
            </div>
            <div>
                <label for="kategori_id" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Kategori</label>
                <select id="kategori_id" name="kategori_id" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua kategori</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>{{ $kategori->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tipe" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Tipe</label>
                <select id="tipe" name="tipe" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua tipe</option>
                    <option value="fisik" @selected(request('tipe') === 'fisik')>Fisik</option>
                    <option value="jasa" @selected(request('tipe') === 'jasa')>Jasa</option>
                </select>
            </div>
            <div>
                <label for="status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                <select id="status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-[#0b1f3a] px-4 text-sm font-bold text-white hover:bg-[#12335d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
                <a href="{{ route('admin.produk.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]" aria-label="Reset filter">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[1040px] text-left text-sm">
                    <caption class="sr-only">Daftar produk dan jasa</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th class="px-5 py-4 font-bold">Produk</th>
                            <th class="px-5 py-4 font-bold">Kategori</th>
                            <th class="px-5 py-4 font-bold">Tipe</th>
                            <th class="px-5 py-4 font-bold">Harga</th>
                            <th class="px-5 py-4 font-bold">Stok</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                            <th class="px-5 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($barangJasa as $produk)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if ($produk->gambar)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($produk->gambar) }}" alt="Gambar {{ $produk->nama_barang }}" class="h-12 w-12 rounded-lg object-cover ring-1 ring-slate-200">
                                        @else
                                            <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 text-xs font-black text-[#0b1f3a]" aria-hidden="true">MJG</span>
                                        @endif
                                        <div>
                                            <p class="font-bold text-[#0b1f3a]">{{ $produk->nama_barang }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">{{ $produk->idbarangjasa }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $produk->kategori?->nama ?? 'Tanpa kategori' }}</td>
                                <td class="px-5 py-4 capitalize text-slate-600">{{ $produk->tipe }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    @if ($produk->mode_harga === 'rfq')
                                        Tanya harga
                                    @elseif ($produk->mode_harga === 'khusus')
                                        Harga khusus
                                    @else
                                        Rp {{ number_format((float) $produk->price, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $produk->tipe === 'jasa' ? '-' : number_format((int) $produk->stok) }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $produk->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $produk->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.produk.edit', $produk) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Edit</a>
                                        <form method="POST" action="{{ route('admin.produk.status', $produk) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-yellow-300 px-3 text-sm font-bold text-[#0b1f3a] hover:bg-yellow-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">{{ $produk->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.produk.destroy', $produk) }}" onsubmit="return confirm('Hapus produk ini? Produk yang sudah dipakai PO akan ditolak oleh sistem.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:border-red-500 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500">Belum ada produk yang cocok dengan filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-200 md:hidden">
                @forelse ($barangJasa as $produk)
                    <article class="space-y-4 p-5">
                        <div class="flex items-start gap-3">
                            @if ($produk->gambar)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($produk->gambar) }}" alt="Gambar {{ $produk->nama_barang }}" class="h-16 w-16 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <span class="flex h-16 w-16 items-center justify-center rounded-lg bg-yellow-100 text-xs font-black text-[#0b1f3a]" aria-hidden="true">MJG</span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><h2 class="font-bold text-[#0b1f3a]">{{ $produk->nama_barang }}</h2><span class="rounded-lg px-2 py-1 text-xs font-bold {{ $produk->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $produk->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                                <p class="mt-1 text-xs font-semibold text-slate-500">{{ $produk->idbarangjasa }} · {{ ucfirst($produk->tipe) }}</p>
                            </div>
                        </div>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-slate-500">Kategori</dt><dd class="mt-1 font-semibold text-slate-800">{{ $produk->kategori?->nama ?? 'Tanpa kategori' }}</dd></div>
                            <div><dt class="text-slate-500">Stok</dt><dd class="mt-1 font-semibold text-slate-800">{{ $produk->tipe === 'jasa' ? '-' : number_format((int) $produk->stok) }}</dd></div>
                            <div class="col-span-2"><dt class="text-slate-500">Harga</dt><dd class="mt-1 font-semibold text-slate-800">@if ($produk->mode_harga === 'rfq') Tanya harga @elseif ($produk->mode_harga === 'khusus') Harga khusus @else Rp {{ number_format((float) $produk->price, 0, ',', '.') }} @endif</dd></div>
                        </dl>
                        <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.produk.edit', $produk) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a]">Edit</a>
                            <form method="POST" action="{{ route('admin.produk.status', $produk) }}">@csrf @method('PATCH')<button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-yellow-300 px-3 text-sm font-bold text-[#0b1f3a]">{{ $produk->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                            <form method="POST" action="{{ route('admin.produk.destroy', $produk) }}" onsubmit="return confirm('Hapus produk ini?');">@csrf @method('DELETE')<button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700">Hapus</button></form>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-14 text-center text-sm text-slate-500">Belum ada produk yang cocok dengan filter ini.</p>
                @endforelse
            </div>

            @if ($barangJasa->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $barangJasa->links() }}</div>
            @endif
        </div>
    </div>
@endsection
