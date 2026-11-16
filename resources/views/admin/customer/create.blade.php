@extends('layouts.admin')

@section('title', 'Tambah Customer')

@section('content')
    <div class="max-w-5xl space-y-7">
        <div>
            <a href="{{ route('admin.customer.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke customer</a>
            <p class="mt-7 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Tambah customer</h1>
        </div>

        @include('admin.customer._form', ['customer' => null])
    </div>
@endsection
