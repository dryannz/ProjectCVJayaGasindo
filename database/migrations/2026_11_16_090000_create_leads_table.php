<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table): void {
                $table->id();
                $table->string('kode', 30)->unique();
                $table->string('nama', 100);
                $table->string('perusahaan', 150)->nullable();
                $table->string('telepon', 30);
                $table->string('email', 150)->nullable();
                $table->text('alamat')->nullable();
                $table->text('catatan')->nullable();
                $table->string('jenis', 20)->default('produk');
                $table->string('status', 20)->default('baru')->index();
                $table->text('pesan_whatsapp');
                $table->string('ip_address', 45)->nullable();
                $table->string('ditangani_oleh', 20)->nullable();
                $table->text('catatan_internal')->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('lead_items')) {
            Schema::create('lead_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                // Snapshot: tetap terbaca walaupun produk kelak diubah atau dihapus.
                $table->string('idbarangjasa', 20)->nullable();
                $table->string('nama_barang');
                $table->string('tipe', 20)->default('fisik');
                $table->string('ukuran', 50)->nullable();
                $table->string('unit', 30)->nullable();
                $table->string('label_harga')->nullable();
                $table->unsignedInteger('qty')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_items');
        Schema::dropIfExists('leads');
    }
};
