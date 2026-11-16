@extends('layouts.admin')

@section('title', 'Edit Quotation')

@section('content')
    <div class="max-w-5xl space-y-7">
        <div>
            <a href="{{ route('admin.quotation.show', $quotation) }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali</a>
            <p class="mt-7 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Edit {{ $quotation->nomor_quotation }}</h1>
        </div>

        @include('admin.quotation._form', ['quotation' => $quotation])
    </div>
@endsection
