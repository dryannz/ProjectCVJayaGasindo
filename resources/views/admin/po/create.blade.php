@extends('layouts.admin')

@section('title', 'Buat PO')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div><a href="{{ route('admin.quotation.show', $quotation) }}" class="inline-flex min-h-11 items-center font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke Quotation</a><h1 class="mt-4 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Buat Purchase Order</h1><p class="mt-2 text-sm leading-6 text-slate-600">Item disalin dari Quotation yang sudah disetujui. Periksa qty, harga, dan jadwal sebelum menyimpan.</p></div>
        @include('admin.po._form', ['quotation' => $quotation])
    </div>
@endsection