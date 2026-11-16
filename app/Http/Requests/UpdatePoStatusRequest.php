<?php

namespace App\Http\Requests;

use App\Models\Po;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePoStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Po::STATUSES))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'status.required' => 'Status tujuan wajib dipilih.',
            'status.in' => 'Status tujuan tidak valid.',
        ];
    }
}
