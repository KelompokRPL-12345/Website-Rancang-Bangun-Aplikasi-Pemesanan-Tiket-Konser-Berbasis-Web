# moonvenue — Aplikasi Pemesanan Tiket Konser

Proyek tugas Rancang Bangun Aplikasi Pemesanan Tiket Konser Berbasis Web, menggunakan PHP native dan MySQL.

## Fitur
- Informasi konser, harga, stok, dan poster.
- Pemesanan tiket dan cetak bukti booking.
- Login admin, tambah/edit/hapus konser, serta daftar pesanan.
- Upload poster JPG, PNG, atau WebP maksimal 2 MB.
- Tampilan responsif dan tautan bantuan pada footer.

## Instalasi lokal
1. Gunakan XAMPP dengan PHP 8.0+ dan MySQL/MariaDB.
2. Salin isi proyek ke C:\xampp\htdocs\tiket-konser.
3. Salin config.example.php menjadi config.php. Sesuaikan host, nama database, username, dan password.
4. Aktifkan Apache dan MySQL. Impor database.sql lewat phpMyAdmin.
5. Jalankan buat-admin.bat untuk membuat akun admin sendiri.
6. Buka http://localhost/tiket-konser/ dan http://localhost/tiket-konser/admin.php.

Untuk instalasi baru, database.sql sudah mencakup tabel admin dan kolom poster. Tidak perlu menjalankan upgrade-poster.sql lagi.

## Data yang disertakan
File PHP, CSS, JavaScript, serta gambar diambil dari KONSER/tiket-konser dalam ZIP proyek. Perubahan tampilan poster dan footer dipertahankan. File bawaan XAMPP tidak disertakan.

Database.sql berisi struktur lengkap dan tiga konser contoh. ZIP sumber tidak memuat ekspor database terbaru: konser yang diubah melalui admin, kaitan poster dengan konser, akun admin, dan pesanan pada komputer asal tidak tercakup dalam data contoh. Gambar poster tetap disertakan dalam uploads/poster; pilih ulang melalui admin bila diperlukan setelah membuat konser.

Config.php asli tidak disertakan. Gunakan config.example.php untuk mengatur koneksi pada komputer masing-masing. Jangan unggah password atau data pribadi pemesan.

## Catatan
Aplikasi merupakan simulasi tanpa pembayaran atau email otomatis. Bukti booking terakhir tersedia pada sesi browser pemesan. GitHub menyimpan kode sumber; aplikasi PHP + MySQL ini memerlukan XAMPP atau hosting PHP/MySQL dan tidak berjalan langsung di GitHub Pages.

## Pemeriksaan paket
Sintaks PHP diperiksa. Paket ini tidak mengubah file aplikasi atau database yang sedang dipakai di XAMPP. Pengujian langsung pada database baru masih perlu dilakukan setelah instalasi.
