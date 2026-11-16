@extends('layouts.admin')

@section('title', 'Buat Quotation')

@section('content')
    <div class="max-w-5xl space-y-7">
        <div>
            <a href="{{ $lead !== null ? route('admin.leads.show', $lead) : route('admin.quotation.index') }}" class="inline-flex min-h-10 items-center rounded-lg text-sm font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Kembali</a>
            <p class="mt-7 text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a]">Buat quotation</h1>
            @if ($lead !== null)
                <p class="mt-2 text-sm text-slate-600">Dari lead <strong>{{ $lead->nama }}</strong>@if($lead->perusahaan !== null && $lead->perusahaan !== '') &mdash; {{ $lead->perusahaan }}@endif.</p>
            @endif
        </div>

        @include('admin.quotation._form', ['quotation' => null])
    </div>
@endsection
