<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignWorkOrderRequest extends FormRequest
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
            'idpetugas' => ['required', 'string', 'exists:petugas,idpetugas'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'idpetugas.required' => 'Pilih teknisi terlebih dahulu.',
            'idpetugas.exists' => 'Teknisi tidak ditemukan.',
        ];
    }
}
