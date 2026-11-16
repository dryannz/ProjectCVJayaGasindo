<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        $customers = Customer::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('idcust', 'like', "%{$search}%")
                        ->orWhere('nama_pt', 'like', "%{$search}%")
                        ->orWhere('kode_singkat', 'like', "%{$search}%")
                        ->orWhere('pic', 'like', "%{$search}%");
                });
            })
            ->orderBy('idcust')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customer.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('admin.customer.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['idcust'] = $this->generateIdcust();

        Customer::create($data);

        return to_route('admin.customer.index')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customer.edit', ['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('admin.customer.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->quotations()->exists()
            || (Schema::hasTable('po') && DB::table('po')->where('idcust', $customer->idcust)->exists())) {
            return back()->withErrors([
                'customer' => 'Customer sudah dipakai pada quotation atau PO dan tidak dapat dihapus.',
            ]);
        }

        $customer->delete();

        return to_route('admin.customer.index')->with('success', 'Customer berhasil dihapus.');
    }

    /**
     * ID otomatis CUST-0001, aman terhadap permintaan bersamaan:
     * lockForUpdate lalu fallback retry saat terjadi duplikat.
     */
    public function generateIdcust(): string
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function (): string {
                    $lastId = Customer::query()
                        ->where('idcust', 'like', 'CUST-%')
                        ->lockForUpdate()
                        ->orderByDesc('idcust')
                        ->value('idcust');

                    $sequence = is_string($lastId) ? ((int) substr($lastId, 5)) + 1 : 1;

                    return sprintf('CUST-%04d', $sequence);
                });
            } catch (QueryException $exception) {
                if (! in_array((int) $exception->getCode(), [19, 23000], true)) {
                    throw $exception;
                }
            }
        }

        throw ValidationException::withMessages([
            'nama_pt' => 'ID customer tidak dapat dibuat sekarang. Silakan coba lagi.',
        ]);
    }
}
