@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Katalog</p>
                <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Kategori produk</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Susun pengelompokan yang mudah dipahami admin sales dan calon pembeli.</p>
            </div>
            <a href="{{ route('admin.kategori.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Tambah kategori</a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <caption class="sr-only">Daftar kategori produk</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th class="px-5 py-4 font-bold">Urutan</th>
                            <th class="px-5 py-4 font-bold">Nama</th>
                            <th class="px-5 py-4 font-bold">Slug</th>
                            <th class="px-5 py-4 font-bold">Produk</th>
                            <th class="px-5 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($kategoris as $kategori)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-semibold text-slate-500">{{ $kategori->urutan }}</td>
                                <td class="px-5 py-4 font-bold text-[#0b1f3a]">{{ $kategori->nama }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $kategori->slug }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $kategori->barang_jasa_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.kategori.edit', $kategori) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Edit</a>
                                        <form method="POST" action="{{ route('admin.kategori.destroy', $kategori) }}" onsubmit="return confirm('Kategori ini memiliki {{ $kategori->barang_jasa_count }} produk. Jika dihapus, produk akan menjadi tanpa kategori. Lanjutkan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:border-red-500 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">Belum ada kategori. Tambahkan kategori pertama untuk mulai menyusun katalog.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($kategoris->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $kategoris->links() }}</div>
            @endif
        </div>
    </div>
@endsection
