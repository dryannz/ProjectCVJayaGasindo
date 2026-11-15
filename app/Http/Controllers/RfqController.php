<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRfqRequest;
use App\Models\BarangJasa;
use App\Models\Lead;
use App\Support\RfqCart;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RfqController extends Controller
{
    public function index(RfqCart $cart): View
    {
        $items = $cart->items();

        return view('pages.rfq.index', [
            'items' => $items,
            'adaJasa' => $items->contains(fn (array $item): bool => $item['produk']->tipe === 'jasa'),
        ]);
    }

    public function add(Request $request, RfqCart $cart, BarangJasa $barangJasa): RedirectResponse
    {
        abort_if(! $barangJasa->is_active, 404);

        $data = $request->validate([
            'qty' => ['nullable', 'integer', 'min:1', 'max:'.RfqCart::MAX_QTY],
            'lanjut' => ['nullable', 'in:rfq'],
        ]);

        $cart->add($barangJasa->idbarangjasa, (int) ($data['qty'] ?? 1));

        if (($data['lanjut'] ?? null) === 'rfq') {
            return to_route('rfq.index');
        }

        return back()->with('rfq_added', "{$barangJasa->nama_barang} masuk ke keranjang RFQ.");
    }

    public function update(Request $request, RfqCart $cart, BarangJasa $barangJasa): RedirectResponse
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:'.RfqCart::MAX_QTY],
        ]);

        $cart->set($barangJasa->idbarangjasa, (int) $data['qty']);

        return to_route('rfq.index');
    }

    public function remove(RfqCart $cart, BarangJasa $barangJasa): RedirectResponse
    {
        $cart->remove($barangJasa->idbarangjasa);

        return to_route('rfq.index')->with('success', 'Item dihapus dari keranjang RFQ.');
    }

    public function store(StoreRfqRequest $request, RfqCart $cart): RedirectResponse
    {
        $items = $cart->items();

        if ($items->isEmpty()) {
            return to_route('rfq.index')->withErrors(['keranjang' => 'Keranjang RFQ masih kosong. Pilih produk atau jasa dari katalog terlebih dahulu.']);
        }

        $adaJasa = $items->contains(fn (array $item): bool => $item['produk']->tipe === 'jasa');

        // Permintaan jasa butuh lokasi pekerjaan; produk fisik boleh menyusul lewat WhatsApp.
        if ($adaJasa && blank($request->input('alamat'))) {
            return back()->withInput()->withErrors(['alamat' => 'Alamat atau lokasi pekerjaan wajib diisi untuk permintaan jasa.']);
        }

        $data = $request->safe()->except('website');

        $lead = DB::transaction(function () use ($data, $items, $request): Lead {
            $lead = Lead::create([
                ...$data,
                'kode' => Lead::buatKode(),
                'jenis' => $this->tentukanJenis($items),
                'status' => Lead::STATUS_BARU,
                'pesan_whatsapp' => '',
                'ip_address' => $request->ip(),
            ]);

            foreach ($items as $item) {
                $produk = $item['produk'];

                $lead->items()->create([
                    'idbarangjasa' => $produk->idbarangjasa,
                    'nama_barang' => $produk->nama_barang,
                    'tipe' => $produk->tipe,
                    'ukuran' => $produk->ukuran,
                    'unit' => $produk->unit,
                    'label_harga' => $produk->label_harga,
                    'qty' => $item['qty'],
                ]);
            }

            $lead->update(['pesan_whatsapp' => $this->susunPesan($lead->load('items'))]);

            return $lead;
        });

        $cart->clear();

        $tautan = WhatsApp::urlPerusahaan($lead->pesan_whatsapp);

        if ($tautan !== null) {
            return redirect()->away($tautan);
        }

        // Nomor WhatsApp perusahaan belum diisi: permintaan tetap tercatat.
        return to_route('rfq.terkirim')->with('rfq_lead', $lead->kode);
    }

    public function terkirim(Request $request): View|RedirectResponse
    {
        $kode = $request->session()->get('rfq_lead');

        if (! $kode) {
            return to_route('katalog.index');
        }

        $request->session()->keep('rfq_lead');

        return view('pages.rfq.terkirim', ['kode' => $kode]);
    }

    /** @param Collection<int, array{produk: BarangJasa, qty: int}> $items */
    private function tentukanJenis(Collection $items): string
    {
        $tipe = $items->pluck('produk.tipe')->unique();

        return match (true) {
            $tipe->count() > 1 => 'campuran',
            $tipe->first() === 'jasa' => 'jasa',
            default => 'produk',
        };
    }

    private function susunPesan(Lead $lead): string
    {
        $baris = [
            sprintf('Halo %s, saya ingin meminta penawaran (RFQ).', config('perusahaan.nama')),
            '',
            "No. permintaan: {$lead->kode}",
            "Nama: {$lead->nama}",
        ];

        if ($lead->perusahaan) {
            $baris[] = "Perusahaan: {$lead->perusahaan}";
        }

        $baris[] = "No. HP/WA: {$lead->telepon}";

        if ($lead->email) {
            $baris[] = "Email: {$lead->email}";
        }

        if ($lead->alamat) {
            $baris[] = "Alamat/lokasi: {$lead->alamat}";
        }

        $baris[] = '';
        $baris[] = 'Daftar permintaan:';

        foreach ($lead->items as $nomor => $item) {
            $rincian = collect([$item->ukuran, $item->unit])
                ->filter(fn (?string $nilai): bool => filled($nilai) && $nilai !== '-')
                ->implode(', ');

            $baris[] = sprintf(
                '%d. %s%s - %d %s',
                $nomor + 1,
                $item->nama_barang,
                $rincian !== '' ? " ({$rincian})" : '',
                $item->qty,
                $item->tipe === 'jasa' ? 'paket/layanan' : ($item->unit && $item->unit !== '-' ? $item->unit : 'unit'),
            );
        }

        if ($lead->catatan) {
            $baris[] = '';
            $baris[] = "Catatan: {$lead->catatan}";
        }

        $baris[] = '';
        $baris[] = 'Mohon informasi penawaran harga dan ketersediaannya. Terima kasih.';

        return implode("\n", $baris);
    }
}
