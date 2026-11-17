<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom milik migrasi ini yang harus dihapus saat rollback.
     * nosuratjalan, tgl, idpetugas, created_at, dan updated_at sengaja tidak
     * disertakan karena sudah ada di tabel milik IKM sebelum migrasi ini.
     */
    private const ADDED_COLUMNS = [
        'pono',
        'idcust',
        'attn',
        'rev',
        'mobil',
        'jenis',
        'status',
        'catatan',
        'nama_penerima',
        'tanggal_diterima',
        'alasan_batal',
    ];

    private const ADDED_INDEXES = [
        'surat_jalan_pono_status_index',
        'surat_jalan_idcust_status_index',
        'surat_jalan_jenis_index',
        'surat_jalan_status_index',
    ];

    public function up(): void
    {
        $this->createOrExtendSuratJalanTable();
        $this->widenDocumentNumberColumns();
        $this->createDetailSuratJalanTable();
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_surat_jalan');

        $this->dropSuratJalanInvoiceForeignKeys();

        if (Schema::hasTable('surat_jalan') && Schema::hasColumn('surat_jalan', 'nosuratjalan')) {
            Schema::table('surat_jalan', function (Blueprint $table): void {
                $table->string('nosuratjalan', 20)->change();
            });
        }

        if (Schema::hasTable('detail_invoice_sj') && Schema::hasColumn('detail_invoice_sj', 'nosuratjalan')) {
            Schema::table('detail_invoice_sj', function (Blueprint $table): void {
                $table->string('nosuratjalan', 20)->change();
            });
        }

        $this->restoreSuratJalanInvoiceForeignKey();

        if (Schema::hasTable('stok_mutasi') && Schema::hasColumn('stok_mutasi', 'referensi_id')) {
            Schema::table('stok_mutasi', function (Blueprint $table): void {
                $table->string('referensi_id', 20)->nullable()->change();
            });
        }

        $this->dropSuratJalanLinkForeignKeys();
        $this->dropAddedSuratJalanIndexesAndColumns();
    }

    private function createOrExtendSuratJalanTable(): void
    {
        if (! Schema::hasTable('surat_jalan')) {
            Schema::create('surat_jalan', function (Blueprint $table): void {
                $table->string('nosuratjalan', 50)->primary();
                $table->date('tgl');
                $table->string('idpetugas', 20);
                $table->string('pono', 20);
                $table->string('idcust', 20);
                $table->string('attn', 255)->nullable();
                $table->string('rev', 20)->default('Rev - 00');
                $table->string('mobil', 100)->nullable();
                $table->string('jenis', 30)->default('pengiriman');
                $table->string('status', 20)->default('draft');
                $table->text('catatan')->nullable();
                $table->string('nama_penerima', 255)->nullable();
                $table->date('tanggal_diterima')->nullable();
                $table->text('alasan_batal')->nullable();
                $table->timestamps();

                $table->foreign('idpetugas', 'surat_jalan_idpetugas_foreign')
                    ->references('idpetugas')
                    ->on('petugas')
                    ->restrictOnDelete();
                $table->foreign('pono', 'surat_jalan_pono_foreign')
                    ->references('pono')
                    ->on('po')
                    ->restrictOnDelete();
                $table->foreign('idcust', 'surat_jalan_idcust_foreign')
                    ->references('idcust')
                    ->on('customer')
                    ->restrictOnDelete();
                $table->index('jenis', 'surat_jalan_jenis_index');
                $table->index('status', 'surat_jalan_status_index');
                $table->index(['pono', 'status'], 'surat_jalan_pono_status_index');
                $table->index(['idcust', 'status'], 'surat_jalan_idcust_status_index');
            });

            return;
        }

        if (DB::table('surat_jalan')->exists()) {
            throw new RuntimeException(
                'Migrasi Surat Jalan dihentikan: tabel surat_jalan berisi data. Kosongkan tabel terlebih dahulu sebelum menambahkan kolom pono dan idcust sebagai NOT NULL.',
            );
        }

        $addPonoForeignKey = false;
        $addCustomerForeignKey = false;

        Schema::table('surat_jalan', function (Blueprint $table) use (&$addPonoForeignKey, &$addCustomerForeignKey): void {
            if (! Schema::hasColumn('surat_jalan', 'pono')) {
                $table->string('pono', 20);
                $addPonoForeignKey = true;
            } else {
                $table->string('pono', 20)->nullable(false)->change();
            }

            if (! Schema::hasColumn('surat_jalan', 'idcust')) {
                $table->string('idcust', 20);
                $addCustomerForeignKey = true;
            } else {
                $table->string('idcust', 20)->nullable(false)->change();
            }

            if (! Schema::hasColumn('surat_jalan', 'attn')) {
                $table->string('attn', 255)->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'rev')) {
                $table->string('rev', 20)->default('Rev - 00');
            }

            if (! Schema::hasColumn('surat_jalan', 'mobil')) {
                $table->string('mobil', 100)->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'jenis')) {
                $table->string('jenis', 30)->default('pengiriman');
            }

            if (! Schema::hasColumn('surat_jalan', 'status')) {
                $table->string('status', 20)->default('draft');
            }

            if (! Schema::hasColumn('surat_jalan', 'catatan')) {
                $table->text('catatan')->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'nama_penerima')) {
                $table->string('nama_penerima', 255)->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'tanggal_diterima')) {
                $table->date('tanggal_diterima')->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'alasan_batal')) {
                $table->text('alasan_batal')->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('surat_jalan', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if ($addPonoForeignKey) {
            Schema::table('surat_jalan', function (Blueprint $table): void {
                $table->foreign('pono', 'surat_jalan_pono_foreign')
                    ->references('pono')
                    ->on('po')
                    ->restrictOnDelete();
            });
        }

        if ($addCustomerForeignKey) {
            Schema::table('surat_jalan', function (Blueprint $table): void {
                $table->foreign('idcust', 'surat_jalan_idcust_foreign')
                    ->references('idcust')
                    ->on('customer')
                    ->restrictOnDelete();
            });
        }

        $indexes = [
            'surat_jalan_jenis_index' => ['jenis'],
            'surat_jalan_status_index' => ['status'],
            'surat_jalan_pono_status_index' => ['pono', 'status'],
            'surat_jalan_idcust_status_index' => ['idcust', 'status'],
        ];

        foreach ($indexes as $name => $columns) {
            if (! Schema::hasIndex('surat_jalan', $name)) {
                Schema::table('surat_jalan', function (Blueprint $table) use ($columns, $name): void {
                    $table->index($columns, $name);
                });
            }
        }
    }

    private function widenDocumentNumberColumns(): void
    {
        if (Schema::hasTable('stok_mutasi') && Schema::hasColumn('stok_mutasi', 'referensi_id')) {
            Schema::table('stok_mutasi', function (Blueprint $table): void {
                $table->string('referensi_id', 50)->nullable()->change();
            });
        }

        if (! Schema::hasTable('surat_jalan') || ! Schema::hasColumn('surat_jalan', 'nosuratjalan')) {
            return;
        }

        $this->dropSuratJalanInvoiceForeignKeys();

        Schema::table('surat_jalan', function (Blueprint $table): void {
            $table->string('nosuratjalan', 50)->change();
        });

        if (Schema::hasTable('detail_invoice_sj') && Schema::hasColumn('detail_invoice_sj', 'nosuratjalan')) {
            Schema::table('detail_invoice_sj', function (Blueprint $table): void {
                $table->string('nosuratjalan', 50)->change();
            });
        }

        $this->restoreSuratJalanInvoiceForeignKey();
    }

    private function createDetailSuratJalanTable(): void
    {
        if (Schema::hasTable('detail_surat_jalan')) {
            return;
        }

        Schema::create('detail_surat_jalan', function (Blueprint $table): void {
            $table->id();
            $table->string('nosuratjalan', 50);
            $table->unsignedBigInteger('detail_po_id');
            $table->string('nama_barang');
            $table->unsignedInteger('qty');
            $table->string('unit', 30)->nullable();
            $table->timestamps();

            $table->foreign('nosuratjalan', 'detail_surat_jalan_nosuratjalan_foreign')
                ->references('nosuratjalan')
                ->on('surat_jalan')
                ->cascadeOnDelete();
            $table->foreign('detail_po_id', 'detail_surat_jalan_detail_po_id_foreign')
                ->references('id')
                ->on('detail_po')
                ->restrictOnDelete();
            $table->unique(
                ['nosuratjalan', 'detail_po_id'],
                'detail_surat_jalan_nosuratjalan_detail_po_id_unique',
            );
        });
    }

    private function dropSuratJalanInvoiceForeignKeys(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('detail_invoice_sj')) {
            return;
        }

        $constraints = DB::select(
            <<<'SQL'
            SELECT DISTINCT CONSTRAINT_NAME AS constraint_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'detail_invoice_sj'
              AND COLUMN_NAME = 'nosuratjalan'
              AND REFERENCED_TABLE_NAME = 'surat_jalan'
            SQL,
        );

        foreach ($constraints as $constraint) {
            $name = (string) $constraint->constraint_name;
            DB::statement('ALTER TABLE `detail_invoice_sj` DROP FOREIGN KEY '.$this->quoteIdentifier($name));
        }
    }

    private function restoreSuratJalanInvoiceForeignKey(): void
    {
        if (
            DB::getDriverName() !== 'mysql'
            || ! Schema::hasTable('detail_invoice_sj')
            || ! Schema::hasTable('surat_jalan')
            || ! Schema::hasColumn('detail_invoice_sj', 'nosuratjalan')
        ) {
            return;
        }

        $constraintExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', 'detail_invoice_sj')
            ->where('COLUMN_NAME', 'nosuratjalan')
            ->where('REFERENCED_TABLE_NAME', 'surat_jalan')
            ->exists();

        if (! $constraintExists) {
            DB::statement(
                'ALTER TABLE `detail_invoice_sj` ADD CONSTRAINT `fk_sjinvoice` FOREIGN KEY (`nosuratjalan`) REFERENCES `surat_jalan` (`nosuratjalan`)',
            );
        }
    }

    private function dropSuratJalanLinkForeignKeys(): void
    {
        if (! Schema::hasTable('surat_jalan')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $constraints = DB::select(
                <<<'SQL'
            SELECT DISTINCT CONSTRAINT_NAME AS constraint_name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'surat_jalan'
              AND COLUMN_NAME IN ('pono', 'idcust')
              AND REFERENCED_TABLE_NAME IS NOT NULL
            SQL,
            );

            foreach ($constraints as $constraint) {
                $name = (string) $constraint->constraint_name;
                DB::statement('ALTER TABLE `surat_jalan` DROP FOREIGN KEY '.$this->quoteIdentifier($name));
            }

            return;
        }

        foreach (Schema::getForeignKeys('surat_jalan') as $foreignKey) {
            if (array_intersect($foreignKey['columns'], ['pono', 'idcust']) === []) {
                continue;
            }

            Schema::table('surat_jalan', function (Blueprint $table) use ($foreignKey): void {
                $table->dropForeign($foreignKey['columns']);
            });
        }
    }

    /**
     * Mengembalikan surat_jalan ke bentuk awal (nosuratjalan, tgl, idpetugas,
     * created_at, updated_at). Tabel itu sendiri tidak dihapus karena milik IKM.
     */
    private function dropAddedSuratJalanIndexesAndColumns(): void
    {
        if (! Schema::hasTable('surat_jalan')) {
            return;
        }

        foreach (self::ADDED_INDEXES as $index) {
            if (Schema::hasIndex('surat_jalan', $index)) {
                Schema::table('surat_jalan', function (Blueprint $table) use ($index): void {
                    $table->dropIndex($index);
                });
            }
        }

        $columns = array_values(array_filter(
            self::ADDED_COLUMNS,
            fn (string $column): bool => Schema::hasColumn('surat_jalan', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table('surat_jalan', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
};
