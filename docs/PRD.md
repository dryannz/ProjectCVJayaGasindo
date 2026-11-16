# Product Requirements Document (PRD)

**Sistem Informasi Terintegrasi CV Mandiri Jaya Gasindo Engineering Berbasis Web: Company Profile, E-Catalog RFQ, dan Sistem Administrasi**

Versi 1.0 (draf untuk ditinjau). Disusun dari Project Charter 6 Oktober 2026 dan dokumen Informasi Tambahan dari IKM.
Tim: Titis Novianti W (PM), Meliarosi Divavatima (BA), Nadya Muazarah (Risk Manager), Adriyan (System Analyst).

> Catatan: file ini adalah hasil transkripsi manual dari `Product Requirements Document (PRD).pdf`. Isinya mengikuti PDF; tidak ada aturan yang ditambahkan. Bagian 7 sampai 18 yang dirujuk PDF **tidak ada** dalam berkas (PDF hanya 8 halaman).

---

## 1. Ringkasan Dokumen

PRD ini menjabarkan apa yang harus dibangun, untuk siapa, dan bagaimana produk dinyatakan berhasil. Menjadi acuan bersama tim dan IKM untuk menyusun BPMN, User Requirements, SDD, UI/UX Wireframe, Test Case, hingga UAT.

| Item | Keterangan |
|---|---|
| Nama produk | Sistem Informasi Terintegrasi CV Mandiri Jaya Gasindo Engineering (MJG) |
| IKM / Sponsor | CV Mandiri Jaya Gasindo Engineering |
| Jenis produk | Aplikasi web berbasis browser, tiga modul: Company Profile, E-Catalog dengan RFQ, Sistem Administrasi |
| Sumber dokumen | (1) Project Charter v1.0, 6 Oktober 2026; (2) Informasi Tambahan dari IKM |
| Pembimbing | M. Fakhir Rizal, S.T., M.Kom. |
| Status | Draf untuk ditinjau tim dan disetujui IKM sebelum fase Desain Sistem |

Prioritas memakai MoSCoW: Must (wajib saat BAST), Should (sangat diharapkan), Could (opsional). Butir bertanda **(Usulan)** adalah rekomendasi tim yang perlu dikonfirmasi ke IKM. Hal yang belum jelas dikumpulkan di Bagian 18 (tidak ada dalam berkas ini).

## 2. Latar Belakang dan Permasalahan

CV Mandiri Jaya Gasindo Engineering bergerak di bidang supplier gas industri dan jasa instalasi gas serta media. Belum ada sistem digital terintegrasi, sehingga promosi, penerimaan order, pencatatan stok, negosiasi harga, dan pembuatan nota masih manual.

| No | Permasalahan | Dampak | Respons produk |
|---|---|---|---|
| 1 | Belum ada company profile online dan lokasi di Google Maps | Sulit ditemukan calon customer; promosi dari mulut ke mulut | Modul Company Profile dengan SEO on-page dan peta tersemat |
| 2 | Katalog produk dan jasa tidak tersedia daring | Customer harus bertanya manual | E-Catalog dengan kategori dan aturan tampilan harga |
| 3 | Negosiasi harga gas dalam jumlah besar dan jasa lewat telepon/WhatsApp tanpa alur terdokumentasi | Permintaan penawaran hilang di chat, sulit di-follow-up | Keranjang RFQ, template WhatsApp terstruktur, Lead Log |
| 4 | **Pencatatan order dan stok manual** | Rawan salah catat; data operasional tidak terpusat | **Master produk, stok otomatis, dan alur dokumen terintegrasi** |
| 5 | Dokumen transaksi (penawaran, PO, surat tugas, surat jalan, nota) dibuat manual | Format tidak konsisten, memakan waktu | Cetak PDF dengan kop surat perusahaan |
| 6 | Pembagian tugas dan akses informasi belum terkontrol | Data sensitif (harga, laporan) bisa diakses sembarang orang | Role-Based Access Control (Admin/Sales, Teknisi, Pimpinan) |

## 3. Tujuan Produk dan Indikator Keberhasilan

### 3.1 Tujuan produk
1. Membangun website Company Profile dan E-Catalog yang dapat diakses publik.
2. Menyediakan fitur Request for Quotation (RFQ) yang terhubung ke WhatsApp perusahaan dengan template pesan.
3. Membangun sistem administrasi untuk mengelola order, stok, quotation, PO, work order, surat jalan, dan invoice.
4. Mengurangi pencatatan manual agar data operasional terstruktur, terpusat, dan mudah dikelola.

### 3.2 Indikator keberhasilan

| ID | Indikator | Cara verifikasi |
|---|---|---|
| KPI-01 | Company Profile dapat diakses dan menampilkan informasi perusahaan, layanan, kontak, lokasi dengan benar | Uji fungsional halaman publik saat UAT |
| KPI-02 | E-Catalog menampilkan produk dan jasa sesuai data IKM, termasuk seluruh jenis gas pada daftar kartu nama | Pencocokan katalog dengan data master IKM |
| KPI-03 | RFQ mengarahkan customer ke WhatsApp dengan template sesuai, setiap klik tercatat sebagai Lead | Uji skenario RFQ per kategori; cek tabel Lead |
| KPI-04 | Sistem Administrasi dapat mengelola produk, stok, order, quotation, dan mencetak dokumen | Uji alur Inquiry sampai Invoice |
| KPI-05 | Sistem sesuai alur bisnis yang disepakati dan data sesuai data IKM | Review bersama BA dan perwakilan IKM |
| KPI-06 | Sistem lolos User Acceptance Test dan disetujui perwakilan IKM | Berita acara UAT |
| KPI-07 (Usulan) | Halaman publik terbaca baik di layar HP | Uji di minimal dua ukuran layar HP |
| KPI-08 (Usulan) | Data master produk awal terisi lewat Import Excel | Uji impor berkas dari IKM |

## 4. Ruang Lingkup

### 4.1 Dalam lingkup (in-scope)

| Modul | Cakupan |
|---|---|
| Company Profile | Home, Tentang Kami, Layanan (Instalasi dan Reparasi), Katalog Produk, Galeri Proyek, Kontak Kami; tagline; Google Maps; SEO on-page |
| E-Catalog | Empat kategori katalog, aturan tampilan harga (Harga Fix, RFQ, Harga Khusus), keranjang RFQ, form RFQ spesifik, template WhatsApp, Lead Log |
| Sistem Administrasi | Manajemen user dan role, **manajemen produk dan stok (fisik vs jasa)**, **alur dokumen Inquiry ke Quotation ke PO ke Work Order dan Surat Jalan ke Invoice**, cetak PDF lima dokumen, dashboard, Import Excel master produk |

### 4.2 Di luar lingkup (out-of-scope)
- Full accounting, aplikasi mobile native, dan ERP lengkap (sesuai Charter).
- Infrastruktur dan keamanan tingkat produksi lanjutan; sistem diserahkan sebagai prototipe fungsional.
- Hosting, domain, maintenance, dan support setelah BAST (tanggungan IKM dan pihak ketiga).
- (Usulan) Pembayaran online, integrasi WhatsApp Business API otomatis, notifikasi email. Pengiriman pesan tetap lewat tautan WhatsApp yang dibuka customer.
- (Usulan) Pembayaran bertahap (cicilan) dan pajak kompleks pada invoice, kecuali lewat Change Request.

### 4.3 Asumsi
1. IKM memiliki NIB dan bersedia menandatangani Surat Kerjasama pada 9 Oktober 2026.
2. IKM menyediakan data produk dan proses bisnis secara lengkap dan akurat.
3. IKM responsif terhadap wawancara lanjutan, validasi desain, dan UAT.
4. Koneksi internet di lokasi IKM memadai.
5. Sistem berupa prototipe fungsional; hosting, domain, maintenance jadi tanggung jawab IKM setelah BAST.
6. Tidak ada perubahan regulasi atau kebijakan IKM yang signifikan selama proyek.

### 4.4 Kendala

| Kategori | Kendala | Dampak |
|---|---|---|
| Waktu | Sekitar 10 minggu (9 Okt sampai 19 Des 2026); BAST paling lambat 26 Des 2026 | Sangat tinggi |
| Scope | Fokus MVP; full accounting, mobile app, ERP lengkap di luar scope | Tinggi |
| Sumber daya | Tim 4 mahasiswa yang juga berkuliah | Sedang-tinggi |
| Anggaran | Tidak ada anggaran pengembangan; hosting dan domain ditanggung IKM | Sedang |
| Teknologi | Prototipe fungsional/MVP, bukan production-ready | Sedang |
| Kebutuhan | Bergantung pada informasi dan validasi dari IKM | Tinggi |

## 5. Stakeholder dan Persona Pengguna

### 5.1 Stakeholder

| Pihak | Peran | Kepentingan |
|---|---|---|
| CV Mandiri Jaya Gasindo Engineering | Sponsor / IKM | Sistem sesuai kebutuhan bisnis, visibilitas, order lebih mudah |
| Titis Novianti W | Project Manager | Proyek selesai tepat waktu sesuai triple constraint |
| Meliarosi Divavatima | Business Analyst | Menerjemahkan kebutuhan IKM menjadi dokumen dan fitur |
| Adriyan | System Analyst | Merancang sistem teknis dan integrasi modul |
| Nadya Muazarah | Risk Manager | Identifikasi risiko dan mitigasi |
| M. Fakhir Rizal, S.T., M.Kom. | Pembimbing | Kesesuaian proyek dengan kaidah manajemen proyek dan penilaian |

### 5.2 Persona dan hak akses

| Persona | Tujuan utama | Kebutuhan kunci | Akses |
|---|---|---|---|
| Calon customer / pengunjung (sering dari HP lewat WhatsApp) | Mengenal perusahaan, melihat produk dan jasa, meminta penawaran | Katalog jelas, harga fix untuk barang standar, RFQ mudah, nyaman di HP | Publik tanpa login |
| Customer B2B terdaftar | Melihat harga khusus yang disepakati | Login sederhana, harga khusus tampil setelah login | Halaman publik ditambah harga khusus |
| **Admin / Sales** | Mengelola katalog, menindaklanjuti Lead, membuat Quotation, PO, Invoice | Input data cepat, tabel mudah dibaca, cetak dokumen | **Akses penuh ke katalog, quotation, PO, invoice** |
| Teknisi | Mengerjakan instalasi dan reparasi | Melihat Work Order: alamat, detail pekerjaan, keluhan tangki | Hanya melihat Work Order |
| Pimpinan | Memantau kinerja usaha | Ringkasan penjualan dan stok | Hanya dashboard laporan penjualan dan stok |

## 6. Gambaran Produk

Tiga modul yang saling terhubung. E-Catalog menjadi pintu masuk customer, lalu data permintaan mengalir ke Sistem Administrasi sehingga tidak berhenti di percakapan WhatsApp.

| Modul | Fungsi utama | Pengguna utama |
|---|---|---|
| Company Profile | Memperkenalkan perusahaan, layanan, galeri proyek, kontak, lokasi | Publik |
| E-Catalog | Menampilkan produk dan jasa, aturan harga, keranjang RFQ, pengiriman permintaan ke WhatsApp | Publik, Customer B2B |
| Sistem Administrasi | Mengelola user, produk, stok, Lead, quotation, PO, work order, surat jalan, invoice, laporan, cetak PDF | Admin/Sales, Teknisi, Pimpinan |

### Alur ringkas end-to-end
1. Customer membuka E-Catalog, memasukkan item ke keranjang RFQ, mengisi form, lalu klik tombol WhatsApp.
2. Sistem menyimpan **Lead** dan membuka WhatsApp dengan pesan terstruktur.
3. Admin/Sales mengubah Lead menjadi **Quotation**.
4. Jika customer setuju, dibuat **PO**.
5. Dari PO terbit **Work Order** (untuk jasa) dan **Surat Jalan** (untuk pengiriman barang, atau penjemputan dan pengembalian tangki).
6. Setelah pekerjaan selesai, terbit **Invoice** dengan status LUNAS atau BELUM LUNAS.

---

## Lampiran: Rangkuman khusus PO dan Stok (untuk implementasi)

### Yang DITULIS PRD
- Alur dokumen: Lead, Quotation, PO, lalu Work Order (jasa) dan Surat Jalan (pengiriman), lalu Invoice.
- Sistem Administrasi mencakup "manajemen produk dan stok (fisik vs jasa)" dan "stok otomatis".
- Admin/Sales punya akses penuh ke katalog, quotation, PO, invoice. Teknisi hanya Work Order. Pimpinan hanya dashboard penjualan dan stok.
- Cetak PDF lima dokumen: penawaran, PO, surat tugas, surat jalan, nota.
- Pembayaran bertahap dan pajak kompleks di luar lingkup.

### Yang TIDAK ditulis PRD (jangan ditebak diam-diam; tandai sebagai ASUMSI dan tanyakan ke IKM)
- Kapan stok dikurangi (saat PO dibuat, diproses, atau saat Surat Jalan terbit).
- Format nomor PO dan arti nomor PO dari customer.
- Daftar dan transisi status PO.
- Aturan pembatalan PO dan pengembalian stok.
- Ambang stok menipis.
- Apakah produk jasa pernah punya stok (PRD hanya menyebut "fisik vs jasa").
- Siapa yang boleh mengoreksi stok secara manual.