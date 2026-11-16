-- PO + stok mutasi untuk CV Mandiri Jaya Gasindo Engineering
-- PENTING: export/backup database melalui phpMyAdmin sebelum menjalankan script ini.
-- Script ini tidak menghapus kolom atau data.
-- Target utama: MariaDB 10.4. MySQL 8 dapat membutuhkan penghapusan klausa
-- IF NOT EXISTS pada ADD COLUMN dan pengecekan ulang constraint/index secara manual.

ALTER TABLE `po`
    ADD COLUMN IF NOT EXISTS `quotation_id` BIGINT UNSIGNED NULL AFTER `project_location`,
    ADD COLUMN IF NOT EXISTS `idpetugas` VARCHAR(20) NULL AFTER `quotation_id`,
    ADD COLUMN IF NOT EXISTS `no_po_customer` VARCHAR(50) NULL AFTER `idpetugas`,
    ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'draft' AFTER `no_po_customer`,
    ADD COLUMN IF NOT EXISTS `subtotal` DECIMAL(15,0) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN IF NOT EXISTS `diskon` DECIMAL(15,0) NOT NULL DEFAULT 0 AFTER `subtotal`,
    ADD COLUMN IF NOT EXISTS `ppn` DECIMAL(15,0) NOT NULL DEFAULT 0 AFTER `diskon`,
    ADD COLUMN IF NOT EXISTS `total` DECIMAL(15,0) NOT NULL DEFAULT 0 AFTER `ppn`,
    ADD COLUMN IF NOT EXISTS `catatan` TEXT NULL AFTER `total`;

SET @sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE() AND table_name = 'po' AND index_name = 'uq_po_quotation_id'
    ),
    'SELECT 1',
    'ALTER TABLE `po` ADD UNIQUE KEY `uq_po_quotation_id` (`quotation_id`)'
);
PREPARE add_po_quotation_index FROM @sql;
EXECUTE add_po_quotation_index;
DEALLOCATE PREPARE add_po_quotation_index;

SET @sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.table_constraints
        WHERE constraint_schema = DATABASE() AND table_name = 'po' AND constraint_name = 'fk_po_quotation'
    ),
    'SELECT 1',
    'ALTER TABLE `po` ADD CONSTRAINT `fk_po_quotation` FOREIGN KEY (`quotation_id`) REFERENCES `quotation` (`id`) ON DELETE SET NULL'
);
PREPARE add_po_quotation_fk FROM @sql;
EXECUTE add_po_quotation_fk;
DEALLOCATE PREPARE add_po_quotation_fk;

SET @sql = IF(
    EXISTS (
        SELECT 1 FROM information_schema.table_constraints
        WHERE constraint_schema = DATABASE() AND table_name = 'po' AND constraint_name = 'fk_po_petugas'
    ),
    'SELECT 1',
    'ALTER TABLE `po` ADD CONSTRAINT `fk_po_petugas` FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`) ON DELETE SET NULL'
);
PREPARE add_po_petugas_fk FROM @sql;
EXECUTE add_po_petugas_fk;
DEALLOCATE PREPARE add_po_petugas_fk;

ALTER TABLE `detail_po`
    ADD COLUMN IF NOT EXISTS `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD COLUMN IF NOT EXISTS `nama_barang` VARCHAR(255) NULL AFTER `amount`,
    ADD COLUMN IF NOT EXISTS `unit` VARCHAR(30) NULL AFTER `nama_barang`,
    ADD COLUMN IF NOT EXISTS `harga_satuan` DECIMAL(15,0) NOT NULL DEFAULT 0 AFTER `unit`;

ALTER TABLE `detail_po`
    MODIFY COLUMN `amount` DECIMAL(15,0) NOT NULL;

CREATE TABLE IF NOT EXISTS `stok_mutasi` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `idbarangjasa` VARCHAR(20) NOT NULL,
    `jenis` VARCHAR(20) NOT NULL,
    `qty` INT UNSIGNED NOT NULL,
    `stok_sebelum` INT UNSIGNED NOT NULL,
    `stok_sesudah` INT UNSIGNED NOT NULL,
    `referensi_tipe` VARCHAR(20) NULL,
    `referensi_id` VARCHAR(20) NULL,
    `keterangan` TEXT NULL,
    `idpetugas` VARCHAR(20) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `stok_mutasi_barang_index` (`idbarangjasa`),
    KEY `stok_mutasi_created_index` (`created_at`),
    CONSTRAINT `fk_stok_mutasi_barang` FOREIGN KEY (`idbarangjasa`) REFERENCES `barang_jasa` (`idbarangjasa`),
    CONSTRAINT `fk_stok_mutasi_petugas` FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cek struktur dan data sebelum melanjutkan ke penggunaan aplikasi.
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name IN ('po', 'detail_po', 'stok_mutasi')
ORDER BY TABLE_NAME, ORDINAL_POSITION;

SELECT 'po' AS tabel, COUNT(*) AS jumlah_baris FROM `po`
UNION ALL
SELECT 'detail_po', COUNT(*) FROM `detail_po`
UNION ALL
SELECT 'stok_mutasi', COUNT(*) FROM `stok_mutasi`;