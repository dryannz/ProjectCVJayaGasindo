@php
    $isEdit = isset($customer) && $customer !== null;
    $model = $isEdit ? $customer : null;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.customer.update', $model) : route('admin.customer.store') }}" class="max-w-3xl space-y-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="space-y-5" aria-labelledby="identity-title">
        <div class="border-b border-slate-200 pb-4">
            <h2 id="identity-title" class="text-lg font-black text-[#0b1f3a]">Data perusahaan</h2>
            @if ($isEdit)
                <p class="mt-1 text-sm text-slate-500">ID customer <span class="font-mono font-bold">{{ $model->idcust }}</span> dibuat otomatis oleh sistem.</p>
            @else
                <p class="mt-1 text-sm text-slate-500">ID customer dibuat otomatis oleh sistem setelah data disimpan.</p>
            @endif
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="nama_pt" class="text-sm font-bold text-[#0b1f3a]">Nama perusahaan</label>
                <input id="nama_pt" name="nama_pt" value="{{ old('nama_pt', $model?->nama_pt) }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" autofocus>
                @error('nama_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="notelp_pt" class="text-sm font-bold text-[#0b1f3a]">Nomor telepon</label>
                <input id="notelp_pt" name="notelp_pt" value="{{ old('notelp_pt', $model?->notelp_pt) }}" required maxlength="20" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="0211234567">
                @error('notelp_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="kode_singkat" class="text-sm font-bold text-[#0b1f3a]">Kode singkat</label>
                <input id="kode_singkat" name="kode_singkat" value="{{ old('kode_singkat', $model?->kode_singkat) }}" required minlength="2" maxlength="10" pattern="[A-Z0-9]{2,10}" autocapitalize="characters" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm uppercase outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300" placeholder="PPKS" aria-describedby="kode-singkat-help">
                <p id="kode-singkat-help" class="mt-2 text-xs leading-5 text-slate-500">2 sampai 10 karakter huruf kapital atau angka. Dipakai pada nomor Work Order.</p>
                @error('kode_singkat')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="pic" class="text-sm font-bold text-[#0b1f3a]">PIC <span class="font-normal text-slate-500">(opsional)</span></label>
                <input id="pic" name="pic" value="{{ old('pic', $model?->pic) }}" maxlength="50" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                @error('pic')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="alamat_pt" class="text-sm font-bold text-[#0b1f3a]">Alamat</label>
                <input id="alamat_pt" name="alamat_pt" value="{{ old('alamat_pt', $model?->alamat_pt) }}" required maxlength="255" class="mt-2 block min-h-12 w-full rounded-lg border border-slate-300 px-4 text-sm outline-none focus:border-[#0b1f3a] focus:ring-2 focus:ring-yellow-300">
                @error('alamat_pt')<p class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
        <a href="{{ route('admin.customer.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-5 text-sm font-bold text-[#0b1f3a] hover:border-[#0b1f3a] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b1f3a]">Batal</a>
        <button type="submit" class="inline-flex min-h-11 items-center rounded-lg bg-red-600 px-5 text-sm font-bold text-white hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">{{ $isEdit ? 'Simpan perubahan' : 'Simpan customer' }}</button>
    </div>
</form>

@push('scripts')
    <script>
        (() => {
            const companyInput = document.querySelector('#nama_pt');
            const codeInput = document.querySelector('#kode_singkat');

            if (!companyInput || !codeInput) {
                return;
            }

            let codeEdited = codeInput.value !== '';
            const suggestCode = (companyName) => companyName
                .toUpperCase()
                .replace(/[^A-Z0-9\s]/g, '')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .map((word) => word.charAt(0))
                .join('')
                .slice(0, 10);

            companyInput.addEventListener('input', () => {
                if (!codeEdited && codeInput.value === '') {
                    codeInput.value = suggestCode(companyInput.value);
                }
            });

            codeInput.addEventListener('input', () => {
                codeEdited = true;
                codeInput.value = codeInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 10);
            });
        })();
    </script>
@endpush
