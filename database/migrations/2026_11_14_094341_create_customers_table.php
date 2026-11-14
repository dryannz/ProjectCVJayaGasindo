<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer')) {
            return;
        }

        Schema::create('customer', function (Blueprint $table): void {
            $table->string('idcust', 20)->primary();
            $table->string('notelp_pt', 20);
            $table->string('nama_pt');
            $table->string('alamat_pt');
            $table->string('pic', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The table may be provisioned manually, so a rollback must not delete it.
    }
};
