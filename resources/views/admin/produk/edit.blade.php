@extends('layouts.admin')

@section('title', 'Edit Produk')

@section('content')
    <div class="max-w-5xl space-y-7">
        <div>
            <a href="{{ route('admin.produk.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke produk</a>
            <p class="mt-7 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Katalog</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Edit produk</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">ID <span class="font-bold text-[#0b1f3a]">{{ $barangJasa->idbarangjasa }}</span> tidak dapat diubah.</p>
        </div>

        @include('admin.produk._form', ['formAction' => route('admin.produk.update', $barangJasa), 'formMethod' => 'PUT'])
    </div>
@endsection
