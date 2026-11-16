@extends('layouts.admin')

@section('title', 'Work Order')

@section('content')
    <div class="space-y-7">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Pelaksanaan jasa</p>
            <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Work Order</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Pantau pekerjaan jasa dari PO, status pelaksanaan, dan teknisi yang bertugas.</p>
        </div>

        <form method="GET" action="{{ route('admin.work-order.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-[minmax(0,1.6fr)_12rem_14rem_auto] lg:items-end">
            <div>
                <label for="work-order-search" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari Work Order</label>
                <input id="work-order-search" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="Nomor WO, PO, atau customer">
            </div>
            <div>
                <label for="work-order-status" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Status</label>
                <select id="work-order-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="work-order-technician" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Teknisi</label>
                <select id="work-order-technician" name="idpetugas" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                    <option value="">Semua teknisi</option>
                    @foreach ($technicians as $technician)
                        <option value="{{ $technician->idpetugas }}" @selected(request('idpetugas') === $technician->idpetugas)>{{ $technician->namapetugas }}{{ $technician->is_active ? '' : ' (nonaktif)' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[#0b1f3a] px-5 text-sm font-bold text-white transition hover:bg-[#14345d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Terapkan</button>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="work-order-list-title">
            <div class="border-b border-slate-200 p-5 sm:p-7">
                <h2 id="work-order-list-title" class="text-xl font-black text-[#0b1f3a]">Daftar Work Order</h2>
            </div>
            @if ($workOrders->isEmpty())
                <div class="px-5 py-14 text-center">
                    <p class="font-bold text-[#0b1f3a]">Belum ada Work Order yang cocok.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Buat Work Order dari detail PO yang memiliki item jasa dan status Diproses atau Selesai.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[880px] text-left text-sm">
                        <caption class="sr-only">Daftar Work Order admin</caption>
                        <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                            <tr>
                                <th class="px-5 py-4 font-bold">Nomor WO</th>
                                <th class="px-5 py-4 font-bold">Customer</th>
                                <th class="px-5 py-4 font-bold">PO</th>
                                <th class="px-5 py-4 font-bold">Tanggal</th>
                                <th class="px-5 py-4 font-bold">Teknisi</th>
                                <th class="px-5 py-4 font-bold">Status</th>
                                <th class="px-5 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($workOrders as $workOrder)
                                <tr class="align-top hover:bg-slate-50">
                                    <td class="px-5 py-4"><a href="{{ route('admin.work-order.show', $workOrder) }}" class="font-mono text-xs font-bold text-[#0b1f3a] underline decoration-yellow-400 decoration-2 underline-offset-4">{{ $workOrder->nomor_wo }}</a></td>
                                    <td class="px-5 py-4 font-semibold text-slate-800">{{ $workOrder->customer?->nama_pt ?? '-' }}</td>
                                    <td class="px-5 py-4"><a href="{{ route('admin.po.show', $workOrder->po) }}" class="font-mono text-xs font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4">{{ $workOrder->pono }}</a></td>
                                    <td class="px-5 py-4 text-slate-700">{{ $workOrder->tanggal_terbit?->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-4 text-slate-700">{{ $workOrder->petugas?->namapetugas ?? 'Belum ditugaskan' }}</td>
                                    <td class="px-5 py-4"><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $workOrder->status_class }}">{{ $workOrder->label_status }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.work-order.show', $workOrder) }}" class="inline-flex min-h-10 items-center rounded-lg px-3 font-bold text-red-700 underline decoration-red-300 decoration-2 underline-offset-4 hover:text-red-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Buka detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-5 py-4">{{ $workOrders->links() }}</div>
            @endif
        </section>
    </div>
@endsection
