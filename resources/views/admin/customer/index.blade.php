@extends('layouts.admin')

@section('title', 'Customer')

@section('content')
    <div class="space-y-7">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em] text-red-600">Penjualan</p>
                <h1 class="mt-2 text-3xl font-black text-[#0b1f3a] sm:text-4xl">Customer</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Daftar perusahaan yang pernah atau akan menerima penawaran dari kami.</p>
            </div>
            <a href="{{ route('admin.customer.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Tambah customer</a>
        </div>

        <form method="GET" action="{{ route('admin.customer.index') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1.8fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Cari customer</label>
                <input id="q" name="q" value="{{ request('q') }}" class="mt-2 block min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="ID, nama PT, atau PIC">
            </div>
            <div class="sm:col-span-1">
                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-[#0b1f3a] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#132c4d] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Cari</button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <caption class="sr-only">Daftar customer</caption>
                    <thead class="bg-[#0b1f3a] text-xs uppercase tracking-[0.12em] text-slate-200">
                        <tr>
                            <th class="px-5 py-4 font-bold">ID</th>
                            <th class="px-5 py-4 font-bold">Nama perusahaan</th>
                            <th class="px-5 py-4 font-bold">Telepon</th>
                            <th class="px-5 py-4 font-bold">PIC</th>
                            <th class="px-5 py-4 text-right font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($customers as $customer)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4 font-mono text-xs font-bold text-[#0b1f3a]">{{ $customer->idcust }}</td>
                                <td class="px-5 py-4 font-bold text-[#0b1f3a]">{{ $customer->nama_pt }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $customer->notelp_pt }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $customer->pic ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.customer.edit', $customer) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Edit</a>
                                        <form method="POST" action="{{ route('admin.customer.destroy', $customer) }}" onsubmit="return confirm('Hapus customer {{ $customer->nama_pt }}? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700 hover:border-red-500 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">Belum ada customer. Tambahkan customer pertama untuk mulai membuat quotation.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>


            <div class="divide-y divide-slate-200 md:hidden">
                @forelse ($customers as $customer)
                    <article class="space-y-3 p-5">
                        <div class="min-w-0">
                            <p class="font-bold text-[#0b1f3a]">{{ $customer->nama_pt }}</p>
                            <p class="mt-1 text-xs font-mono text-slate-500">{{ $customer->idcust }}</p>
                        </div>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-slate-500">Telepon</dt><dd class="mt-1 font-semibold text-slate-800">{{ $customer->notelp_pt }}</dd></div>
                            <div><dt class="text-slate-500">PIC</dt><dd class="mt-1 font-semibold text-slate-800">{{ $customer->pic ?? '-' }}</dd></div>
                            <div class="col-span-2"><dt class="text-slate-500">Alamat</dt><dd class="mt-1 font-semibold text-slate-800">{{ $customer->alamat_pt }}</dd></div>
                        </dl>
                        <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                            <a href="{{ route('admin.customer.edit', $customer) }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-bold text-[#0b1f3a]">Edit</a>
                            <form method="POST" action="{{ route('admin.customer.destroy', $customer) }}" onsubmit="return confirm('Hapus customer {{ $customer->nama_pt }}? Tindakan ini tidak dapat dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex min-h-10 items-center rounded-lg border border-red-200 px-3 text-sm font-bold text-red-700">Hapus</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="px-5 py-14 text-center text-sm text-slate-500">Belum ada customer. Tambahkan customer pertama untuk mulai membuat quotation.</p>
                @endforelse
            </div>

            @if ($customers->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
