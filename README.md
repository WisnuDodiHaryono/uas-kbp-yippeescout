# 📦 CTVY Scout - Managerial DSS
**Sistem Pendukung Keputusan (DSS) Pengadaan Aset Thrift Berbasis OOP**

Aplikasi berbasis web ini dikembangkan menggunakan bahasa PHP (Object-Oriented Programming) murni. Berfungsi sebagai sistem manajerial untuk mengevaluasi kelayakan pembelian aset pakaian preloved (*thrift*) berdasarkan parameter fisik terukur, serta mencegah kerugian finansial akibat pembelian barang *overpriced*.

## 🚀 Fitur Utama
- **Secure Authentication:** Sistem login untuk melindungi data manajerial.
- **Weighted OOP Algorithm:** Kalkulasi *Overall Rating* (OVR 1-100) menggunakan prinsip Enkapsulasi dari 6 atribut pakaian (Warna, Bahan, Kualitas, Gaya, Fit, Fungsi).
- **Business Intelligence Dashboard:** Analitik *real-time* untuk memantau Aset Gudang, Potensi Profit, Profit Nyata (Sold), dan Total Kerugian Dihindari.
- **Automated Decision:** Keputusan otomatis "Must Buy", "Layak", atau "Reject" berdasarkan komparasi harga modal vs harga wajar.
- **Enterprise Reporting:** Fitur pencarian data (Live Search) dan satu klik ekspor laporan ke format Microsoft Excel (.xls).
- **Interactive Data Report:** Modal pop-up visualisasi diagram bar untuk rincian skor inspeksi barang.

## 🛠️ Teknologi yang Digunakan
- **Backend:** PHP 8+ (OOP Architecture)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, Vanilla CSS (Glassmorphism & CSS Variables), JavaScript (Vanilla)

## ⚙️ Cara Instalasi
1. *Clone* repositori ini ke dalam folder `htdocs` (XAMPP) atau `www` (Laragon) Anda.
2. Buat database baru di phpMyAdmin dengan nama `checkvaluethriftyippee`.
3. *Import* file `checkvaluethriftyippee.sql` yang tersedia di repositori ini ke dalam database tersebut.
4. Buka browser dan akses `localhost/nama-folder-repo`.
5. Login menggunakan kredensial standar:
   - **Username:** admin
   - **Password:** uas123

---
**Dibuat oleh:** Wisnu
*Program Studi Informatika - UIN Sunan Kalijaga*
*Proyek UAS Konsep Bahasa Pemrograman 2026*
