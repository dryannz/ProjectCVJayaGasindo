<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('work_order')) {
            Schema::create('work_order', function (Blueprint $table): void {
                $table->string('nomor_wo', 50)->primary();
                $table->string('pono', 20);
                $table->string('idcust', 20);
                $table->date('tanggal_terbit');
                $table->string('lokasi_pekerjaan');
                $table->text('uraian_pekerjaan');
                $table->text('keluhan_tangki')->nullable();
                $table->string('idpetugas', 20)->nullable();
                $table->string('status', 20)->default('menunggu')->index();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->date('tanggal_bast')->nullable();
                $table->text('catatan_teknisi')->nullable();
                $table->text('catatan_admin')->nullable();
                $table->text('alasan_batal')->nullable();
                $table->timestamps();

                $table->foreign('pono', 'work_order_pono_foreign')
                    ->references('pono')
                    ->on('po')
                    ->restrictOnDelete();
                $table->foreign('idcust', 'work_order_idcust_foreign')
                    ->references('idcust')
                    ->on('customer')
                    ->restrictOnDelete();
                $table->foreign('idpetugas', 'work_order_idpetugas_foreign')
                    ->references('idpetugas')
                    ->on('petugas')
                    ->nullOnDelete();
                $table->index(['idcust', 'status'], 'work_order_idcust_status_index');
                $table->index(['idpetugas', 'status'], 'work_order_idpetugas_status_index');
            });
        }

        if (! Schema::hasTable('detail_work_order')) {
            Schema::create('detail_work_order', function (Blueprint $table): void {
                $table->id();
                $table->string('nomor_wo', 50);
                $table->unsignedBigInteger('detail_po_id');
                $table->string('nama_barang');
                $table->unsignedInteger('qty');
                $table->timestamps();

                $table->foreign('nomor_wo', 'detail_work_order_nomor_wo_foreign')
                    ->references('nomor_wo')
                    ->on('work_order')
                    ->cascadeOnDelete();
                $table->foreign('detail_po_id', 'detail_work_order_detail_po_id_foreign')
                    ->references('id')
                    ->on('detail_po')
                    ->restrictOnDelete();
                $table->unique(
                    ['nomor_wo', 'detail_po_id'],
                    'detail_work_order_nomor_wo_detail_po_id_unique',
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_work_order');
        Schema::dropIfExists('work_order');
    }
};
