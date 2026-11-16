@extends('layouts.admin')

@section('title', 'Buat Work Order')

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.po.show', $po) }}" class="inline-flex min-h-11 items-center font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke detail PO</a>
            <p class="mt-5 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Pelaksanaan jasa</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Buat Work Order</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Buat dokumen pekerjaan dari item jasa pada PO yang dipilih.</p>
        </div>

        @if (! $po->customer?->kode_singkat)
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm leading-6 text-yellow-950" role="alert">
                <p class="font-bold">Customer belum memiliki kode singkat.</p>
                <p class="mt-1">Isi kode singkat customer sebelum membuat Work Order agar nomor dokumen dapat dibuat.</p>
                <a href="{{ route('admin.customer.edit', $po->customer) }}" class="mt-3 inline-flex min-h-10 items-center font-bold underline decoration-yellow-800 decoration-2 underline-offset-4">Buka data customer</a>
            </div>
        @endif

        @include('admin.work-order._form')
    </div>
@endsection
