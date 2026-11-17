-- Surat Jalan untuk CV Mandiri Jaya Gasindo Engineering.
-- Target: MariaDB 10.4 dan phpMyAdmin.
-- Wajib export atau backup database melalui phpMyAdmin sebelum import.
-- Jalankan setelah tabel customer, petugas, po, detail_po, dan stok_mutasi tersedia.
-- Script ini tidak menghapus kolom atau data.
-- MySQL 8 dapat memerlukan review manual untuk klausa IF NOT EXISTS.

CREATE TABLE IF NOT EXISTS `surat_jalan` (
    `nosuratjalan` VARCHAR(50) NOT NULL,
    `tgl` DATE NOT NULL,
    `idpetugas` VARCHAR(20) NOT NULL,
    `pono` VARCHAR(20) NOT NULL,
    `idcust` VARCHAR(20) NOT NULL,
    `attn` VARCHAR(255) NULL,
    `rev` VARCHAR(20) NOT NULL DEFAULT 'Rev - 00',
    `mobil` VARCHAR(100) NULL,
    `jenis` VARCHAR(30) NOT NULL DEFAULT 'pengiriman',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `catatan` TEXT NULL,
    `nama_penerima` VARCHAR(255) NULL,
    `tanggal_diterima` DATE NULL,
    `alasan_batal` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`nosuratjalan`),
    KEY `surat_jalan_jenis_index` (`jenis`),
    KEY `surat_jalan_status_index` (`status`),
    KEY `surat_jalan_pono_status_index` (`pono`, `status`),
    KEY `surat_jalan_idcust_status_index` (`idcust`, `status`),
    CONSTRAINT `surat_jalan_idpetugas_foreign`
        FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`)
        ON DELETE RESTRICT,
    CONSTRAINT `surat_jalan_pono_foreign`
        FOREIGN KEY (`pono`) REFERENCES `po` (`pono`)
        ON DELETE RESTRICT,
    CONSTRAINT `surat_jalan_idcust_foreign`
        FOREIGN KEY (`idcust`) REFERENCES `customer` (`idcust`)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP PROCEDURE IF EXISTS `guard_surat_jalan_empty`;
DELIMITER //
CREATE PROCEDURE `guard_surat_jalan_empty`()
BEGIN
    IF EXISTS (SELECT 1 FROM `surat_jalan`) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Migrasi Surat Jalan dihentikan: surat_jalan berisi data; kosongkan tabel sebelum menambah pono/idcust NOT NULL.';
    END IF;
END//
DELIMITER ;
CALL `guard_surat_jalan_empty`();
DROP PROCEDURE `guard_surat_jalan_empty`;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND column_name = 'pono'
    ),
    'ALTER TABLE `surat_jalan` MODIFY COLUMN `pono` VARCHAR(20) NOT NULL',
    'ALTER TABLE `surat_jalan` ADD COLUMN `pono` VARCHAR(20) NOT NULL AFTER `idpetugas`'
);
PREPARE ensure_surat_jalan_pono FROM @sql;
EXECUTE ensure_surat_jalan_pono;
DEALLOCATE PREPARE ensure_surat_jalan_pono;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND column_name = 'idcust'
    ),
    'ALTER TABLE `surat_jalan` MODIFY COLUMN `idcust` VARCHAR(20) NOT NULL',
    'ALTER TABLE `surat_jalan` ADD COLUMN `idcust` VARCHAR(20) NOT NULL AFTER `pono`'
);
PREPARE ensure_surat_jalan_idcust FROM @sql;
EXECUTE ensure_surat_jalan_idcust;
DEALLOCATE PREPARE ensure_surat_jalan_idcust;

ALTER TABLE `surat_jalan`
    ADD COLUMN IF NOT EXISTS `attn` VARCHAR(255) NULL AFTER `idcust`,
    ADD COLUMN IF NOT EXISTS `rev` VARCHAR(20) NOT NULL DEFAULT 'Rev - 00' AFTER `attn`,
    ADD COLUMN IF NOT EXISTS `mobil` VARCHAR(100) NULL AFTER `rev`,
    ADD COLUMN IF NOT EXISTS `jenis` VARCHAR(30) NOT NULL DEFAULT 'pengiriman' AFTER `mobil`,
    ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'draft' AFTER `jenis`,
    ADD COLUMN IF NOT EXISTS `catatan` TEXT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `nama_penerima` VARCHAR(255) NULL AFTER `catatan`,
    ADD COLUMN IF NOT EXISTS `tanggal_diterima` DATE NULL AFTER `nama_penerima`,
    ADD COLUMN IF NOT EXISTS `alasan_batal` TEXT NULL AFTER `tanggal_diterima`,
    ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NULL DEFAULT NULL;

SET @sql = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND index_name = 'surat_jalan_jenis_index'
    ),
    'ALTER TABLE `surat_jalan` ADD INDEX `surat_jalan_jenis_index` (`jenis`)',
    'SELECT 1'
);
PREPARE add_surat_jalan_jenis_index FROM @sql;
EXECUTE add_surat_jalan_jenis_index;
DEALLOCATE PREPARE add_surat_jalan_jenis_index;

SET @sql = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND index_name = 'surat_jalan_status_index'
    ),
    'ALTER TABLE `surat_jalan` ADD INDEX `surat_jalan_status_index` (`status`)',
    'SELECT 1'
);
PREPARE add_surat_jalan_status_index FROM @sql;
EXECUTE add_surat_jalan_status_index;
DEALLOCATE PREPARE add_surat_jalan_status_index;

SET @sql = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.table_constraints
        WHERE constraint_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND constraint_name = 'surat_jalan_pono_foreign'
    ),
    'ALTER TABLE `surat_jalan` ADD CONSTRAINT `surat_jalan_pono_foreign` FOREIGN KEY (`pono`) REFERENCES `po` (`pono`) ON DELETE RESTRICT',
    'SELECT 1'
);
PREPARE add_surat_jalan_pono_fk FROM @sql;
EXECUTE add_surat_jalan_pono_fk;
DEALLOCATE PREPARE add_surat_jalan_pono_fk;

SET @sql = IF(
    NOT EXISTS (
        SELECT 1
        FROM information_schema.table_constraints
        WHERE constraint_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND constraint_name = 'surat_jalan_idcust_foreign'
    ),
    'ALTER TABLE `surat_jalan` ADD CONSTRAINT `surat_jalan_idcust_foreign` FOREIGN KEY (`idcust`) REFERENCES `customer` (`idcust`) ON DELETE RESTRICT',
    'SELECT 1'
);
PREPARE add_surat_jalan_customer_fk FROM @sql;
EXECUTE add_surat_jalan_customer_fk;
DEALLOCATE PREPARE add_surat_jalan_customer_fk;

SET @fk_name = (
    SELECT constraint_name
    FROM information_schema.key_column_usage
    WHERE table_schema = DATABASE()
      AND table_name = 'detail_invoice_sj'
      AND column_name = 'nosuratjalan'
      AND referenced_table_name = 'surat_jalan'
    LIMIT 1
);
SET @sql = IF(
    @fk_name IS NULL,
    'SELECT 1',
    CONCAT('ALTER TABLE `detail_invoice_sj` DROP FOREIGN KEY `', REPLACE(@fk_name, '`', '``'), '`')
);
PREPARE drop_detail_invoice_sj_fk FROM @sql;
EXECUTE drop_detail_invoice_sj_fk;
DEALLOCATE PREPARE drop_detail_invoice_sj_fk;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'surat_jalan'
          AND column_name = 'nosuratjalan'
    ),
    'ALTER TABLE `surat_jalan` MODIFY COLUMN `nosuratjalan` VARCHAR(50) NOT NULL',
    'SELECT 1'
);
PREPARE widen_surat_jalan_number FROM @sql;
EXECUTE widen_surat_jalan_number;
DEALLOCATE PREPARE widen_surat_jalan_number;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'detail_invoice_sj'
          AND column_name = 'nosuratjalan'
    ),
    'ALTER TABLE `detail_invoice_sj` MODIFY COLUMN `nosuratjalan` VARCHAR(50) NOT NULL',
    'SELECT 1'
);
PREPARE widen_detail_invoice_sj_number FROM @sql;
EXECUTE widen_detail_invoice_sj_number;
DEALLOCATE PREPARE widen_detail_invoice_sj_number;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'detail_invoice_sj'
          AND column_name = 'nosuratjalan'
    )
    AND NOT EXISTS (
        SELECT 1
        FROM information_schema.table_constraints
        WHERE constraint_schema = DATABASE()
          AND table_name = 'detail_invoice_sj'
          AND constraint_name = 'fk_sjinvoice'
    ),
    'ALTER TABLE `detail_invoice_sj` ADD CONSTRAINT `fk_sjinvoice` FOREIGN KEY (`nosuratjalan`) REFERENCES `surat_jalan` (`nosuratjalan`)',
    'SELECT 1'
);
PREPARE restore_detail_invoice_sj_fk FROM @sql;
EXECUTE restore_detail_invoice_sj_fk;
DEALLOCATE PREPARE restore_detail_invoice_sj_fk;

SET @sql = IF(
    EXISTS (
        SELECT 1
        FROM information_schema.columns
        WHERE table_schema = DATABASE()
          AND table_name = 'stok_mutasi'
          AND column_name = 'referensi_id'
    ),
    'ALTER TABLE `stok_mutasi` MODIFY COLUMN `referensi_id` VARCHAR(50) NULL',
    'SELECT 1'
);
PREPARE widen_stock_reference FROM @sql;
EXECUTE widen_stock_reference;
DEALLOCATE PREPARE widen_stock_reference;

CREATE TABLE IF NOT EXISTS `detail_surat_jalan` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nosuratjalan` VARCHAR(50) NOT NULL,
    `detail_po_id` BIGINT UNSIGNED NOT NULL,
    `nama_barang` VARCHAR(255) NOT NULL,
    `qty` INT UNSIGNED NOT NULL,
    `unit` VARCHAR(30) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `detail_surat_jalan_nosuratjalan_detail_po_id_unique` (`nosuratjalan`, `detail_po_id`),
    CONSTRAINT `detail_surat_jalan_nosuratjalan_foreign`
        FOREIGN KEY (`nosuratjalan`) REFERENCES `surat_jalan` (`nosuratjalan`)
        ON DELETE CASCADE,
    CONSTRAINT `detail_surat_jalan_detail_po_id_foreign`
        FOREIGN KEY (`detail_po_id`) REFERENCES `detail_po` (`id`)
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name IN ('surat_jalan', 'detail_surat_jalan', 'detail_invoice_sj', 'stok_mutasi')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT 'surat_jalan' AS tabel, COUNT(*) AS jumlah_baris FROM `surat_jalan`
UNION ALL
SELECT 'detail_surat_jalan', COUNT(*) FROM `detail_surat_jalan`;
