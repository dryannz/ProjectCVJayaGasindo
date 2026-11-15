<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'perusahaan' => ['nullable', 'string', 'max:150'],
            'telepon' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            // Honeypot: manusia tidak melihat kolom ini, bot biasanya mengisinya.
            'website' => ['nullable', 'max:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'telepon.required' => 'Nomor WhatsApp atau telepon wajib diisi.',
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, dan kurung (minimal 8 karakter).',
            'email.email' => 'Format email belum benar.',
            'alamat.required' => 'Alamat atau lokasi pekerjaan wajib diisi untuk permintaan jasa.',
            'website.max' => 'Permintaan tidak dapat diproses.',
        ];
    }
}
