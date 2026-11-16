<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer') || Schema::hasColumn('customer', 'kode_singkat')) {
            return;
        }

        Schema::table('customer', function (Blueprint $table): void {
            $table->string('kode_singkat', 10)->nullable()->after('nama_pt');
            $table->unique('kode_singkat', 'customer_kode_singkat_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer') || ! Schema::hasColumn('customer', 'kode_singkat')) {
            return;
        }

        $indexes = Schema::getIndexes('customer');

        if (collect($indexes)->contains(fn (array $index): bool => $index['name'] === 'customer_kode_singkat_unique')) {
            Schema::table('customer', function (Blueprint $table): void {
                $table->dropUnique('customer_kode_singkat_unique');
            });
        }

        Schema::table('customer', function (Blueprint $table): void {
            $table->dropColumn('kode_singkat');
        });
    }
};
