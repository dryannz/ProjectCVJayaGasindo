<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SuratJalanMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_surat_jalan_link_columns_are_not_nullable(): void
    {
        $columns = collect(Schema::getColumns('surat_jalan'))->keyBy('name');

        $this->assertFalse($columns->get('pono')['nullable']);
        $this->assertFalse($columns->get('idcust')['nullable']);
    }

    public function test_non_empty_legacy_surat_jalan_is_rejected_before_link_columns_are_added(): void
    {
        $this->createLegacySuratJalanTable();
        DB::table('surat_jalan')->insert(['nosuratjalan' => 'LEGACY-001']);

        $migration = $this->migration();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tabel surat_jalan berisi data');

        $migration->up();
    }

    public function test_down_removes_link_columns_added_to_an_empty_legacy_table(): void
    {
        $this->createLegacySuratJalanTable();
        $migration = $this->migration();

        $migration->up();

        $this->assertTrue(Schema::hasColumn('surat_jalan', 'pono'));
        $this->assertTrue(Schema::hasColumn('surat_jalan', 'idcust'));

        $migration->down();

        $this->assertFalse(Schema::hasColumn('surat_jalan', 'pono'));
        $this->assertFalse(Schema::hasColumn('surat_jalan', 'idcust'));
    }

    private function createLegacySuratJalanTable(): void
    {
        Schema::dropIfExists('detail_surat_jalan');
        Schema::dropIfExists('surat_jalan');
        Schema::create('surat_jalan', function (Blueprint $table): void {
            $table->string('nosuratjalan', 50)->primary();
        });
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_11_17_004620_create_surat_jalan_tables.php');
    }
}
