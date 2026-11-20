# Parapatan Tailor

Sistem Informasi Manajemen **Parapatan Tailor** berbasis web untuk mengelola pesanan jahitan, pelanggan, pengukuran, dan pembayaran dalam satu sistem terintegrasi.

---

## ✨ Fitur

### 🛒 Fitur Pelanggan
- 🏠 **Beranda** — halaman utama dengan info layanan
- 📏 **Form Pesanan** — isi data pesanan jahitan
- 📐 **Ukuran Badan** — input ukuran badan untuk jahitan custom
- 🧵 **Pilih Model** — pilih model/jenis jahitan
- 📦 **Status Pesanan** — lacak status pesanan (proses, selesai, diambil)
- 💳 **Pembayaran** — lihat rincian biaya & status pembayaran
- 🧾 **Cetak Nota** — cetak bukti pesanan (TCPDF)

### 🛠️ Fitur Admin
- 📊 **Dashboard** — ringkasan pesanan & pendapatan
- 👥 **Manajemen Pelanggan** — data pelanggan & riwayat pesanan
- 📦 **Manajemen Pesanan** — kelola pesanan masuk
- 📐 **Manajemen Ukuran** — data ukuran badan pelanggan
- 🧵 **Manajemen Model** — kelola jenis/model jahitan
- 💰 **Manajemen Pembayaran** — kelola pembayaran (DP & pelunasan)
- 📈 **Laporan** — laporan pesanan & pendapatan
- 📄 **Cetak Laporan** — cetak laporan dengan TCPDF
- 👤 **Manajemen User** — hak akses admin & pelanggan

### ⚠️ Fitur Monitoring
- ⏰ **Pesanan Terlambat** — pesanan yang melewati tanggal selesai
- 📉 **Pesanan Belum Lunas** — daftar pesanan dengan pembayaran belum lunas
- 🔔 **Notifikasi Otomatis** — alert di dashboard admin untuk pesanan kritis
- 🚨 **Indikator Warna** — status pesanan dengan warna (merah/kuning/hijau)

---

## 🛠️ Teknologi

- 🐘 **Bahasa**: PHP
- 🗄️ **Database**: MySQL
- 🎨 **Frontend**: HTML, CSS, JavaScript, Bootstrap
- 📄 **Library**: TCPDF (cetak PDF)
- 🖥️ **Server**: XAMPP / Laragon

---

## 📋 Persyaratan

- 🐘 PHP >= 7.4
- 🗄️ MySQL / MariaDB
- 🖥️ XAMPP / Laragon
- 🌐 Browser modern (Chrome, Firefox, Edge)
1. **Clone repo**
   ```bash
   git clone https://github.com/tiazfebias/parapatan_tailor.git
