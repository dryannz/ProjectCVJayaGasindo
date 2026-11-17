<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReceiveSuratJalanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_penerima' => ['required', 'string', 'max:255'],
            'tanggal_diterima' => ['required', 'date'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama_penerima.required' => 'Nama penerima wajib diisi.',
            'nama_penerima.max' => 'Nama penerima maksimal 255 karakter.',
            'tanggal_diterima.required' => 'Tanggal diterima wajib diisi.',
            'tanggal_diterima.date' => 'Tanggal diterima tidak valid.',
        ];
    }
}
