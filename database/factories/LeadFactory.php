<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kode' => 'RFQ-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'nama' => fake()->name(),
            'perusahaan' => fake()->company(),
            'telepon' => fake()->numerify('08##########'),
            'email' => fake()->safeEmail(),
            'alamat' => fake()->address(),
            'catatan' => null,
            'jenis' => 'produk',
            'status' => Lead::STATUS_BARU,
            'pesan_whatsapp' => 'Halo, saya ingin meminta penawaran.',
            'ip_address' => '127.0.0.1',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }
}
