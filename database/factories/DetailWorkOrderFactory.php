<?php

namespace Database\Factories;

use App\Models\DetailPo;
use App\Models\DetailWorkOrder;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailWorkOrder>
 */
class DetailWorkOrderFactory extends Factory
{
    protected $model = DetailWorkOrder::class;

    public function definition(): array
    {
        return [
            'nomor_wo' => WorkOrder::factory(),
            'detail_po_id' => DetailPo::factory(),
            'nama_barang' => fake()->words(3, true),
            'qty' => fake()->numberBetween(1, 5),
        ];
    }
}
