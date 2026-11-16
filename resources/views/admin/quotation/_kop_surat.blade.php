@php
    $perusahaan = config('perusahaan');
@endphp

<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm print:border-0 print:shadow-none" aria-label="Kop surat penawaran">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b-4 border-double border-[#0b1f3a] pb-5">
        <div class="flex items-start gap-4">
            @if (($perusahaan['logo'] ?? null) !== null)
                <img src="{{ asset($perusahaan['logo']) }}" alt="Logo {{ $perusahaan['nama'] ?? 'perusahaan' }}" width="64" height="64" class="h-16 w-16 object-contain">
            @endif
            <div>
                <p class="text-xl font-black uppercase tracking-wide text-[#0b1f3a]">{{ $perusahaan['nama'] ?? 'CV Mandiri Jaya Gasindo Engineering' }}</p>
                <p class="mt-1 max-w-md text-xs leading-5 text-slate-600">{{ $perusahaan['alamat'] ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-600">
                    @if (($perusahaan['telepon'] ?? null) !== null)Telp: {{ $perusahaan['telepon'] }} &middot; @endif
                    @if (($perusahaan['email'] ?? null) !== null){{ $perusahaan['email'] }}@endif
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-red-600">Nomor</p>
            <p class="font-mono text-sm font-bold text-[#0b1f3a]">{{ $quotation->nomor_quotation }}</p>
            <p class="mt-2 text-xs font-bold uppercase tracking-[0.15em] text-red-600">Tanggal</p>
            <p class="text-sm font-semibold text-slate-800">{{ $quotation->tanggal->translatedFormat('d M Y') }}</p>
        </div>
    </div>
    <p class="mt-4 text-center text-base font-black uppercase tracking-[0.2em] text-[#0b1f3a]">Penawaran Harga</p>
    <p class="mt-1 text-center text-xs text-slate-500">Kepada: <strong>{{ $quotation->customer?->nama_pt ?? '-' }}</strong>@if($quotation->customer) &mdash; {{ $quotation->customer->alamat_pt }}@endif</p>
</section>
