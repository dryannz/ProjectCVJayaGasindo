@extends('layouts.staff')

@section('title', 'Work Order Teknisi')

@section('content')
    <div class="space-y-7">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Area kerja teknisi</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Work Order Teknisi</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Daftar ini hanya berisi pekerjaan yang ditugaskan kepada {{ $petugas->namapetugas }}.</p>
        </div>

        <form method="GET" action="{{ route('teknisi.work-order') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(0,1fr)_12rem_auto] sm:items-end">
            <div><label for="technician-work-order-search" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari Work Order</label><input id="technician-work-order-search" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nomor WO, PO, atau customer"></div>
            <div><label for="technician-work-order-status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label><select id="technician-work-order-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300"><option value="">Semua status</option>@foreach ($statuses as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select></div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="technician-work-order-list-title">
            <div class="border-b border-slate-200 p-5 sm:p-7"><h2 id="technician-work-order-list-title" class="text-xl font-black text-[#0b1f3a]">Pekerjaan saya</h2></div>
            @if ($workOrders->isEmpty())
                <div class="px-5 py-14 text-center"><p class="font-bold text-[#0b1f3a]">Belum ada Work Order yang ditugaskan.</p><p class="mt-2 text-sm leading-6 text-slate-600">Admin akan menampilkan pekerjaan baru setelah menugaskan Work Order kepada Anda.</p></div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($workOrders as $workOrder)
                        <a href="{{ route('teknisi.work-order.show', $workOrder) }}" class="block p-5 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-[#0b1f3a] sm:p-7">
                            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start"><div><p class="font-mono text-sm font-bold text-[#0b1f3a]">{{ $workOrder->nomor_wo }}</p><p class="mt-2 font-bold text-slate-800">{{ $workOrder->customer?->nama_pt ?? '-' }}</p></div><span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold {{ $workOrder->status_class }}">{{ $workOrder->label_status }}</span></div>
                            <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-3"><div><dt class="text-slate-500">PO</dt><dd class="mt-1 font-mono font-semibold text-slate-800">{{ $workOrder->pono }}</dd></div><div><dt class="text-slate-500">Tanggal terbit</dt><dd class="mt-1 font-semibold text-slate-800">{{ $workOrder->tanggal_terbit?->translatedFormat('d M Y') }}</dd></div><div><dt class="text-slate-500">Qty item</dt><dd class="mt-1 font-semibold text-slate-800">{{ $workOrder->details->sum('qty') }}</dd></div></dl>
                        </a>
                    @endforeach
                </div>
                <div class="border-t border-slate-200 px-5 py-4">{{ $workOrders->links() }}</div>
            @endif
        </section>
    </div>
@endsection
