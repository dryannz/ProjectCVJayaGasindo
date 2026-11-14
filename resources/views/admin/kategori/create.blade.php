@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="max-w-3xl space-y-7">
        <div>
            <a href="{{ route('admin.kategori.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke kategori</a>
            <p class="mt-7 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Katalog</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Tambah kategori</h1>
        </div>

        <form method="POST" action="{{ route('admin.kategori.store') }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            <div>
                <label for="nama" class="text-sm font-bold text-[#0b1f3a]">Nama kategori</label>
                <input id="nama" name="nama" value="{{ old('nama') }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Contoh: Regulator Gas" autofocus>
                @error('nama')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="urutan" class="text-sm font-bold text-[#0b1f3a]">Urutan tampil <span class="font-normal text-slate-500">(opsional)</span></label>
                <input id="urutan" name="urutan" value="{{ old('urutan', 0) }}" type="number" min="0" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none transition focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                @error('urutan')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                <a href="{{ route('admin.kategori.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Simpan kategori</button>
            </div>
        </form>
    </div>
@endsection
