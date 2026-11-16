<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createPoTable();
        $this->addPoColumns();
        $this->createDetailPoTable();
        $this->addDetailPoColumns();
        $this->createStokMutasiTable();
    }

    public function down(): void
    {
        // These tables may be provisioned manually in the IKM database.
    }

    private function createPoTable(): void
    {
        if (Schema::hasTable('po')) {
            return;
        }

        Schema::create('po', function (Blueprint $table): void {
            $table->string('pono', 20)->primary();
            $table->string('idcust', 20);
            $table->date('tgl_order');
            $table->date('schedule_delivery');
            $table->string('term_of_payment', 20);
            $table->string('project_name')->nullable();
            $table->string('project_location')->nullable();
            $table->foreignId('quotation_id')->nullable()->unique()->constrained('quotation')->nullOnDelete();
            $table->string('idpetugas', 20)->nullable();
            $table->string('no_po_customer', 50)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('subtotal', 15, 0)->default(0);
            $table->decimal('diskon', 15, 0)->default(0);
            $table->decimal('ppn', 15, 0)->default(0);
            $table->decimal('total', 15, 0)->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('idcust')->references('idcust')->on('customer')->restrictOnDelete();
            $table->foreign('idpetugas')->references('idpetugas')->on('petugas')->nullOnDelete();
            $table->index(['idcust', 'status']);
        });
    }

    private function addPoColumns(): void
    {
        if (! Schema::hasTable('po')) {
            return;
        }

        Schema::table('po', function (Blueprint $table): void {
            if (! Schema::hasColumn('po', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->unique()->constrained('quotation')->nullOnDelete();
            }

            if (! Schema::hasColumn('po', 'idpetugas')) {
                $table->string('idpetugas', 20)->nullable();
                $table->foreign('idpetugas')->references('idpetugas')->on('petugas')->nullOnDelete();
            }

            if (! Schema::hasColumn('po', 'no_po_customer')) {
                $table->string('no_po_customer', 50)->nullable();
            }

            if (! Schema::hasColumn('po', 'status')) {
                $table->string('status', 20)->default('draft')->index();
            }

            if (! Schema::hasColumn('po', 'subtotal')) {
                $table->decimal('subtotal', 15, 0)->default(0);
            }

            if (! Schema::hasColumn('po', 'diskon')) {
                $table->decimal('diskon', 15, 0)->default(0);
            }

            if (! Schema::hasColumn('po', 'ppn')) {
                $table->decimal('ppn', 15, 0)->default(0);
            }

            if (! Schema::hasColumn('po', 'total')) {
                $table->decimal('total', 15, 0)->default(0);
            }

            if (! Schema::hasColumn('po', 'catatan')) {
                $table->text('catatan')->nullable();
            }
        });
    }

    private function createDetailPoTable(): void
    {
        if (Schema::hasTable('detail_po')) {
            return;
        }

        Schema::create('detail_po', function (Blueprint $table): void {
            $table->id();
            $table->string('idbarangjasa', 20)->nullable();
            $table->string('pono', 20);
            $table->unsignedInteger('qty');
            $table->decimal('amount', 15, 0)->default(0);
            $table->string('nama_barang');
            $table->string('unit', 30)->nullable();
            $table->decimal('harga_satuan', 15, 0)->default(0);
            $table->timestamps();

            $table->foreign('idbarangjasa')->references('idbarangjasa')->on('barang_jasa')->nullOnDelete();
            $table->foreign('pono')->references('pono')->on('po')->cascadeOnDelete();
            $table->index('idbarangjasa');
        });
    }

    private function addDetailPoColumns(): void
    {
        if (! Schema::hasTable('detail_po')) {
            return;
        }

        Schema::table('detail_po', function (Blueprint $table): void {
            if (! Schema::hasColumn('detail_po', 'id')) {
                // The IKM table is currently empty. Back up before applying this to an existing database.
                $table->id();
            }

            if (! Schema::hasColumn('detail_po', 'nama_barang')) {
                $table->string('nama_barang')->nullable();
            }

            if (! Schema::hasColumn('detail_po', 'unit')) {
                $table->string('unit', 30)->nullable();
            }

            if (! Schema::hasColumn('detail_po', 'harga_satuan')) {
                $table->decimal('harga_satuan', 15, 0)->default(0);
            }
        });

        if (Schema::hasColumn('detail_po', 'amount')) {
            Schema::table('detail_po', function (Blueprint $table): void {
                $table->decimal('amount', 15, 0)->change();
            });
        }
    }

    private function createStokMutasiTable(): void
    {
        if (Schema::hasTable('stok_mutasi')) {
            return;
        }

        Schema::create('stok_mutasi', function (Blueprint $table): void {
            $table->id();
            $table->string('idbarangjasa', 20);
            $table->string('jenis', 20);
            $table->unsignedInteger('qty');
            $table->unsignedInteger('stok_sebelum');
            $table->unsignedInteger('stok_sesudah');
            $table->string('referensi_tipe', 20)->nullable();
            $table->string('referensi_id', 20)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('idpetugas', 20)->nullable();
            $table->timestamps();

            $table->foreign('idbarangjasa')->references('idbarangjasa')->on('barang_jasa')->restrictOnDelete();
            $table->foreign('idpetugas')->references('idpetugas')->on('petugas')->nullOnDelete();
            $table->index('idbarangjasa');
            $table->index('created_at');
        });
    }
};
