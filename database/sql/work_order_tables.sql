-- Work Order and customer code for CV Mandiri Jaya Gasindo Engineering.
-- Target: MariaDB 10.4 and phpMyAdmin.
-- Export or back up the database in phpMyAdmin before importing this file.
-- Run after po_stok_tables.sql and quotation_tables.sql.
-- This script does not delete columns or data.
-- MySQL 8 may require manual review if IF NOT EXISTS clauses are unsupported.

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'customer'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'customer'
          AND column_name = 'kode_singkat'
    ),
    'ALTER TABLE `customer` ADD COLUMN `kode_singkat` VARCHAR(10) NULL AFTER `nama_pt`',
    'SELECT 1'
);
PREPARE add_customer_code_column FROM @sql;
EXECUTE add_customer_code_column;
DEALLOCATE PREPARE add_customer_code_column;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'customer'
          AND column_name = 'kode_singkat'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'customer'
          AND column_name = 'kode_singkat'
          AND non_unique = 0
    ),
    'ALTER TABLE `customer` ADD UNIQUE KEY `customer_kode_singkat_unique` (`kode_singkat`)',
    'SELECT 1'
);
PREPARE add_customer_code_unique FROM @sql;
EXECUTE add_customer_code_unique;
DEALLOCATE PREPARE add_customer_code_unique;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = 'petugas'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'petugas'
          AND column_name = 'is_active'
    ),
    'ALTER TABLE `petugas` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`',
    'SELECT 1'
);
PREPARE add_petugas_active_column FROM @sql;
EXECUTE add_petugas_active_column;
DEALLOCATE PREPARE add_petugas_active_column;

CREATE TABLE IF NOT EXISTS `work_order` (
    `nomor_wo` VARCHAR(50) NOT NULL,
    `pono` VARCHAR(20) NOT NULL,
    `idcust` VARCHAR(20) NOT NULL,
    `tanggal_terbit` DATE NOT NULL,
    `lokasi_pekerjaan` VARCHAR(255) NOT NULL,
    `uraian_pekerjaan` TEXT NOT NULL,
    `keluhan_tangki` TEXT NULL,
    `idpetugas` VARCHAR(20) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'menunggu',
    `tanggal_mulai` DATE NULL,
    `tanggal_selesai` DATE NULL,
    `tanggal_bast` DATE NULL,
    `catatan_teknisi` TEXT NULL,
    `catatan_admin` TEXT NULL,
    `alasan_batal` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`nomor_wo`),
    KEY `work_order_status_index` (`status`),
    KEY `work_order_idcust_status_index` (`idcust`, `status`),
    KEY `work_order_idpetugas_status_index` (`idpetugas`, `status`),
    CONSTRAINT `work_order_pono_foreign`
        FOREIGN KEY (`pono`) REFERENCES `po` (`pono`) ON DELETE RESTRICT,
    CONSTRAINT `work_order_idcust_foreign`
        FOREIGN KEY (`idcust`) REFERENCES `customer` (`idcust`) ON DELETE RESTRICT,
    CONSTRAINT `work_order_idpetugas_foreign`
        FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `detail_work_order` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nomor_wo` VARCHAR(50) NOT NULL,
    `detail_po_id` BIGINT UNSIGNED NOT NULL,
    `nama_barang` VARCHAR(255) NOT NULL,
    `qty` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `detail_work_order_nomor_wo_detail_po_id_unique` (`nomor_wo`, `detail_po_id`),
    CONSTRAINT `detail_work_order_nomor_wo_foreign`
        FOREIGN KEY (`nomor_wo`) REFERENCES `work_order` (`nomor_wo`) ON DELETE CASCADE,
    CONSTRAINT `detail_work_order_detail_po_id_foreign`
        FOREIGN KEY (`detail_po_id`) REFERENCES `detail_po` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name IN ('customer', 'petugas', 'work_order', 'detail_work_order')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT 'work_order' AS tabel, COUNT(*) AS jumlah_baris FROM `work_order`
UNION ALL
SELECT 'detail_work_order', COUNT(*) FROM `detail_work_order`;
