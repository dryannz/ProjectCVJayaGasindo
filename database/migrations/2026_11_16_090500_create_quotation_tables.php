<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel existing di MySQL, dibuat manual lewat phpMyAdmin: hanya buat bila belum ada.
        if (! Schema::hasTable('customer')) {
            Schema::create('customer', function (Blueprint $table): void {
                $table->string('idcust', 20)->primary();
                $table->string('notelp_pt', 20);
                $table->string('nama_pt');
                $table->string('alamat_pt');
                $table->string('pic', 50)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('nomor_dokumen')) {
            Schema::create('nomor_dokumen', function (Blueprint $table): void {
                $table->id();
                $table->string('jenis', 20);
                $table->string('periode', 7);
                $table->unsignedInteger('terakhir')->default(0);
                $table->unique(['jenis', 'periode']);
            });
        }

        if (! Schema::hasTable('quotation')) {
            Schema::create('quotation', function (Blueprint $table): void {
                // Tipe kolom FK harus cocok persis dengan tabel induk.
                $table->id();
                $table->string('nomor_quotation', 30)->unique();
                $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
                $table->string('idcust', 20);
                $table->string('idpetugas', 20);
                $table->date('tanggal');
                $table->date('berlaku_sampai')->nullable();
                $table->string('status', 20)->default('draft')->index();
                $table->text('catatan')->nullable();
                $table->text('syarat')->nullable();
                $table->decimal('subtotal', 15, 0)->default(0);
                $table->decimal('diskon', 15, 0)->default(0);
                $table->decimal('ppn', 15, 0)->default(0);
                $table->decimal('total', 15, 0)->default(0);
                $table->timestamps();

                $table->foreign('idcust')->references('idcust')->on('customer')->restrictOnDelete();
                $table->foreign('idpetugas')->references('idpetugas')->on('petugas')->restrictOnDelete();
                $table->index(['idcust', 'status']);
            });
        }

        if (! Schema::hasTable('detail_quotation')) {
            Schema::create('detail_quotation', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('quotation_id')->constrained('quotation')->cascadeOnDelete();
                $table->string('idbarangjasa', 20)->nullable();
                // Snapshot: dokumen lama tidak berubah saat harga produk diubah.
                $table->string('nama_barang');
                $table->string('tipe', 20)->default('fisik');
                $table->string('ukuran', 50)->nullable();
                $table->string('unit', 30)->nullable();
                $table->unsignedInteger('qty')->default(1);
                $table->decimal('harga_satuan', 15, 0)->default(0);
                $table->decimal('jumlah', 15, 0)->default(0);
                $table->timestamps();

                $table->foreign('idbarangjasa')->references('idbarangjasa')->on('barang_jasa')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_quotation');
        Schema::dropIfExists('quotation');
        Schema::dropIfExists('nomor_dokumen');
    }
};
