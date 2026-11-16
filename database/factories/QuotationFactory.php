<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Petugas;
use App\Models\Quotation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    public function definition(): array
    {
        $tanggal = fake()->date();

        return [
            'nomor_quotation' => 'QT-'.fake()->unique()->numerify('#####'),
            'lead_id' => null,
            'idcust' => Customer::factory(),
            'idpetugas' => Petugas::factory(),
            'tanggal' => $tanggal,
            'berlaku_sampai' => fake()->boolean() ? date('Y-m-d', strtotime($tanggal.' +30 days')) : null,
            'status' => Quotation::STATUS_DRAFT,
            'catatan' => null,
            'syarat' => null,
            'subtotal' => 0,
            'diskon' => 0,
            'ppn' => 0,
            'total' => 0,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }

    public function terkirim(): static
    {
        return $this->state(fn (): array => [
            'status' => Quotation::STATUS_TERKIRIM,
        ]);
    }
}
