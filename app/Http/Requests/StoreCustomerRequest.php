<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'notelp_pt' => ['required', 'string', 'max:20', 'regex:/^[0-9+\\-\\s()]{8,20}$/'],
            'nama_pt' => ['required', 'string', 'max:255'],
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
            'alamat_pt.required' => 'Alamat perusahaan wajib diisi.',
            'alamat_pt.max' => 'Alamat perusahaan maksimal 255 karakter.',
            'pic.max' => 'Nama PIC maksimal 50 karakter.',
        ];
    }
}
