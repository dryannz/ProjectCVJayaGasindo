<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('barang_jasa')) {
            Schema::create('barang_jasa', function (Blueprint $table): void {
                $table->string('idbarangjasa', 20)->primary();
                $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
                $table->string('tipe', 20)->default('fisik');
                $table->string('nama_barang');
                $table->text('deskripsi')->nullable();
                $table->string('ukuran', 50);
                $table->string('unit', 30);
                $table->string('mode_harga', 20)->default('fix');
                $table->decimal('price', 15, 2)->nullable();
                $table->unsignedInteger('stok')->nullable();
                $table->string('gambar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('barang_jasa', function (Blueprint $table): void {
            if (! Schema::hasColumn('barang_jasa', 'kategori_id')) {
                $table->foreignId('kategori_id')->nullable()->constrained('kategori')->nullOnDelete();
            }

            if (! Schema::hasColumn('barang_jasa', 'tipe')) {
                $table->string('tipe', 20)->default('fisik');
            }

            if (! Schema::hasColumn('barang_jasa', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }

            if (! Schema::hasColumn('barang_jasa', 'mode_harga')) {
                $table->string('mode_harga', 20)->default('fix');
            }

            if (! Schema::hasColumn('barang_jasa', 'stok')) {
                $table->unsignedInteger('stok')->nullable();
            }

            if (! Schema::hasColumn('barang_jasa', 'gambar')) {
                $table->string('gambar')->nullable();
            }

            if (! Schema::hasColumn('barang_jasa', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (! Schema::hasColumn('barang_jasa', 'ukuran')) {
                $table->string('ukuran', 50)->nullable();
            }

            if (! Schema::hasColumn('barang_jasa', 'unit')) {
                $table->string('unit', 30)->nullable();
            }
        });
    }

    public function down(): void
    {
        // The table may be provisioned manually, so a rollback must not delete it.
    }
};
