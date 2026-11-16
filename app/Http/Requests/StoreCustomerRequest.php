<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_singkat' => strtoupper(trim((string) $this->input('kode_singkat', ''))),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'notelp_pt' => ['required', 'string', 'max:20', 'regex:/^[0-9+\\-\\s()]{8,20}$/'],
            'nama_pt' => ['required', 'string', 'max:255'],
            'kode_singkat' => ['required', 'string', 'min:2', 'max:10', 'regex:/^[A-Z0-9]{2,10}$/', Rule::unique('customer', 'kode_singkat')],
            'alamat_pt' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'notelp_pt.required' => 'Nomor telepon perusahaan wajib diisi.',
            'notelp_pt.max' => 'Nomor telepon maksimal 20 karakter.',
            'notelp_pt.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, dan kurung (minimal 8 karakter).',
            'nama_pt.required' => 'Nama perusahaan wajib diisi.',
            'nama_pt.max' => 'Nama perusahaan maksimal 255 karakter.',
            'kode_singkat.required' => 'Kode singkat customer wajib diisi.',
            'kode_singkat.min' => 'Kode singkat customer minimal 2 karakter.',
            'kode_singkat.max' => 'Kode singkat customer maksimal 10 karakter.',
            'kode_singkat.regex' => 'Kode singkat hanya boleh berisi huruf kapital dan angka.',
            'kode_singkat.unique' => 'Kode singkat tersebut sudah digunakan customer lain.',
            'alamat_pt.required' => 'Alamat perusahaan wajib diisi.',
            'alamat_pt.max' => 'Alamat perusahaan maksimal 255 karakter.',
            'pic.max' => 'Nama PIC maksimal 50 karakter.',
        ];
    }
}
