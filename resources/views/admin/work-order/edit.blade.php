@extends('layouts.admin')

@section('title', 'Edit '.$workOrder->nomor_wo)

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('admin.work-order.show', $workOrder) }}" class="inline-flex min-h-11 items-center font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali ke detail Work Order</a>
            <p class="mt-5 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Pelaksanaan jasa</p>
            <h1 class="mt-2 font-mono text-2xl font-black text-[#0b1f3a] sm:text-4xl">{{ $workOrder->nomor_wo }}</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Edit hanya tersedia sebelum Work Order selesai atau dibatalkan.</p>
        </div>

        @include('admin.work-order._form')
    </div>
@endsection
