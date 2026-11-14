<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('harga_khusus')) {
            return;
        }

        Schema::create('harga_khusus', function (Blueprint $table): void {
            $table->id();
            $table->string('idcust', 20);
            $table->string('idbarangjasa', 20);
            $table->decimal('price', 15, 2);
            $table->foreign('idcust')->references('idcust')->on('customer')->cascadeOnDelete();
            $table->foreign('idbarangjasa')->references('idbarangjasa')->on('barang_jasa')->cascadeOnDelete();
            $table->unique(['idcust', 'idbarangjasa']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // The table may be provisioned manually, so a rollback must not delete it.
    }
};
