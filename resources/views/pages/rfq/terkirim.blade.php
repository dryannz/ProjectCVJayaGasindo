@extends('layouts.layout', ['title' => 'Permintaan Tercatat'])

@section('content')
    <section class="mx-auto max-w-2xl px-5 py-16 text-center lg:py-24">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-red-600">Permintaan tercatat</p>
        <h1 class="mt-3 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Terima kasih, permintaan Anda sudah kami terima.</h1>
        <p class="mt-4 text-base leading-7 text-slate-600">Nomor permintaan Anda <strong class="font-mono text-[#0b1f3a]">{{ $kode }}</strong>. Tim kami akan menghubungi Anda untuk menyampaikan penawaran.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('katalog.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-yellow-400 px-5 text-sm font-black text-[#0b1f3a] hover:bg-yellow-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-400">Kembali ke katalog</a>
            <a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-[#0b1f3a] px-5 text-sm font-bold text-[#0b1f3a] hover:bg-[#0b1f3a] hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Hubungi kami</a>
        </div>
    </section>
@endsection
