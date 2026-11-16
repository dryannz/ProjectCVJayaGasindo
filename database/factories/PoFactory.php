<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Petugas;
use App\Models\Po;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Po> */
class PoFactory extends Factory
{
    protected $model = Po::class;

    public function definition(): array
    {
        return [
            'pono' => 'PO/'.fake()->year().'/'.fake()->month().'/'.fake()->unique()->numerify('####'),
            'idcust' => Customer::factory(),
            'tgl_order' => now()->toDateString(),
            'schedule_delivery' => now()->addDays(7)->toDateString(),
            'term_of_payment' => '30 hari',
            'project_name' => 'Proyek '.fake()->word(),
            'project_location' => fake()->address(),
            'quotation_id' => null,
            'idpetugas' => Petugas::factory(),
            'no_po_customer' => null,
            'status' => Po::STATUS_DRAFT,
            'subtotal' => 0,
            'diskon' => 0,
            'ppn' => 0,
            'total' => 0,
            'catatan' => null,
        ];
    }

    public function diproses(): static
    {
        return $this->state(fn (): array => ['status' => Po::STATUS_DIPROSES]);
    }
}
