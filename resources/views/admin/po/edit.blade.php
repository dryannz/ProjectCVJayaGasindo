@extends('layouts.admin')

@section('title', 'Edit PO '.$po->pono)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div><a href="{{ route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke detail PO</a><h1 class="mt-4 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Edit {{ $po->pono }}</h1><p class="mt-2 text-sm leading-6 text-slate-600">PO masih draft, sehingga data header, qty, harga, dan catatan dapat disesuaikan.</p></div>
        @include('admin.po._form', ['po' => $po, 'quotation' => $po->quotation])
    </div>
@endsection