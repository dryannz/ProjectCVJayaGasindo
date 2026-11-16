-- Tabel modul Quotation — CV Mandiri Jaya Gasindo Engineering
-- Siap tempel di phpMyAdmin. Tabel existing tidak diubah.
-- ASUMSI: format nomor QT/{tahun}/{bulan}/{urut4}, masa berlaku default 30 hari, PPN opsional.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `nomor_dokumen` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `jenis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `terakhir` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_dokumen_jenis_periode_unique` (`jenis`, `periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quotation` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_quotation` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `idcust` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idpetugas` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `berlaku_sampai` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `syarat` text DEFAULT NULL,
  `subtotal` decimal(15,0) NOT NULL DEFAULT 0,
  `diskon` decimal(15,0) NOT NULL DEFAULT 0,
  `ppn` decimal(15,0) NOT NULL DEFAULT 0,
  `total` decimal(15,0) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_nomor_quotation_unique` (`nomor_quotation`),
  KEY `quotation_status_index` (`status`),
  KEY `quotation_idcust_status_index` (`idcust`, `status`),
  KEY `quotation_lead_id_foreign` (`lead_id`),
  KEY `quotation_idcust_foreign` (`idcust`),
  KEY `quotation_idpetugas_foreign` (`idpetugas`),
  CONSTRAINT `quotation_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotation_idcust_foreign` FOREIGN KEY (`idcust`) REFERENCES `customer` (`idcust`) ON DELETE SET NULL,
  CONSTRAINT `quotation_idpetugas_foreign` FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `detail_quotation` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint(20) UNSIGNED NOT NULL,
  `idbarangjasa` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fisik',
  `ukuran` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `harga_satuan` decimal(15,0) NOT NULL DEFAULT 0,
  `jumlah` decimal(15,0) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detail_quotation_quotation_id_foreign` (`quotation_id`),
  KEY `detail_quotation_idbarangjasa_foreign` (`idbarangjasa`),
  CONSTRAINT `detail_quotation_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_quotation_idbarangjasa_foreign` FOREIGN KEY (`idbarangjasa`) REFERENCES `barang_jasa` (`idbarangjasa`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
