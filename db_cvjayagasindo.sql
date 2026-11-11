-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 11, 2026 at 04:48 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.4.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_cvjayagasindo`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_jasa`
--

CREATE TABLE `barang_jasa` (
  `idbarangjasa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `idcust` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notelp_pt` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_pt` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pic` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_invoice_sj`
--

CREATE TABLE `detail_invoice_sj` (
  `invno` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nosuratjalan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_po`
--

CREATE TABLE `detail_po` (
  `idbarangjasa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nopo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `invno` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idpetugas` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `jatuh_tempo` date DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(10,0) NOT NULL,
  `ppn` decimal(10,0) DEFAULT NULL,
  `pph` decimal(10,0) DEFAULT NULL,
  `discount` decimal(10,0) DEFAULT NULL,
  `pono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `idpetugas` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namapetugas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notelp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin_sales',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po`
--

CREATE TABLE `po` (
  `pono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idcust` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_order` date NOT NULL,
  `schedule_delivery` date NOT NULL,
  `term_of_payment` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `surat_jalan`
--

CREATE TABLE `surat_jalan` (
  `nosuratjalan` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl` date NOT NULL,
  `idpetugas` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_jasa`
--
ALTER TABLE `barang_jasa`
  ADD PRIMARY KEY (`idbarangjasa`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`idcust`);

--
-- Indexes for table `detail_invoice_sj`
--
ALTER TABLE `detail_invoice_sj`
  ADD KEY `fk_invoicesj` (`invno`),
  ADD KEY `fk_sjinvoice` (`nosuratjalan`);

--
-- Indexes for table `detail_po`
--
ALTER TABLE `detail_po`
  ADD KEY `fk_po` (`nopo`),
  ADD KEY `fk_barangjasa` (`idbarangjasa`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invno`),
  ADD KEY `fk_petugasinvoice` (`idpetugas`),
  ADD KEY `fk_poinvoice` (`pono`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`idpetugas`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `po`
--
ALTER TABLE `po`
  ADD PRIMARY KEY (`pono`),
  ADD KEY `fk_customer` (`idcust`);

--
-- Indexes for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD PRIMARY KEY (`nosuratjalan`),
  ADD KEY `fk_petugas` (`idpetugas`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_invoice_sj`
--
ALTER TABLE `detail_invoice_sj`
  ADD CONSTRAINT `fk_invoicesj` FOREIGN KEY (`invno`) REFERENCES `invoice` (`invno`),
  ADD CONSTRAINT `fk_sjinvoice` FOREIGN KEY (`nosuratjalan`) REFERENCES `surat_jalan` (`nosuratjalan`);

--
-- Constraints for table `detail_po`
--
ALTER TABLE `detail_po`
  ADD CONSTRAINT `fk_barangjasa` FOREIGN KEY (`idbarangjasa`) REFERENCES `barang_jasa` (`idbarangjasa`),
  ADD CONSTRAINT `fk_po` FOREIGN KEY (`nopo`) REFERENCES `po` (`pono`);

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_petugasinvoice` FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`),
  ADD CONSTRAINT `fk_poinvoice` FOREIGN KEY (`pono`) REFERENCES `po` (`pono`);

--
-- Constraints for table `po`
--
ALTER TABLE `po`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`idcust`) REFERENCES `customer` (`idcust`);

--
-- Constraints for table `surat_jalan`
--
ALTER TABLE `surat_jalan`
  ADD CONSTRAINT `fk_petugas` FOREIGN KEY (`idpetugas`) REFERENCES `petugas` (`idpetugas`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
