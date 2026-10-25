# Update poster konser

## Untuk aplikasi yang sudah dipasang
1. Salin isi folder tiket-konser dari ZIP update-poster ke C:\xampp\htdocs\tiket-konser, pilih Replace. Paket update tidak berisi config.php, sehingga pengaturan koneksi tetap dipertahankan.
2. Buka phpMyAdmin, pilih database tiket_konser, klik Import, lalu pilih upgrade-poster.sql. Jalankan sekali saja. Jika muncul Duplicate column name 'poster', kolom sudah ada dan langkah ini tidak perlu diulang.
3. Buka halaman admin dan login. Pada Kelola konser, pilih Edit.
4. Pilih foto pada Poster konser lalu klik Simpan konser.
5. Buka halaman utama. Jika tampilan belum berubah, tekan Ctrl+F5.

Tidak perlu mengimpor ulang database.sql, membuat akun admin lagi, atau menghapus database. Data konser dan pemesanan lama tetap ada. Pastikan nama database dalam upgrade-poster.sql cocok jika kamu pernah mengganti nama database.

JPG, PNG, dan WebP diterima, maksimal 2 MB. Resolusi maksimal 12 megapiksel dan panjang sisi maksimal 6000 piksel. Poster tampil utuh (tidak dipotong), dengan ruang kosong jika rasio gambar berbeda. Nama konser tetap tampil di bawah poster. Pilihan tanpa gambar mempertahankan desain bawaan.

Edit tanpa memilih file baru mempertahankan poster lama. Centang Hapus poster untuk kembali ke desain bawaan. Jika mengunggah file baru sekaligus mencentang Hapus, file baru yang dipakai.

Gambar disimpan ke uploads/poster yang otomatis dibuat saat upload pertama. Folder harus dapat ditulis oleh PHP. Format dan ukuran file divalidasi. File disimpan dengan nama acak dan ekstensi sesuai isi gambar; tidak membutuhkan ekstensi GD. Password dan pengaturan database tidak berubah.

Poster yang diganti/dilepas tetap ada sebagai file lama di folder uploads/poster, tetapi tidak ditampilkan. Ini menghindari penghapusan file saat ada proses bersamaan. Backup database beserta folder uploads untuk mempertahankan poster.

Pemeriksaan: sintaks PHP dan validasi format/ukuran gambar diuji. Upload melalui browser serta penyimpanan pada MySQL belum diuji langsung dalam lingkungan ini. Setelah update, coba upload pada satu konser, edit tanpa memilih gambar, ganti gambar, dan hapus poster; pastikan konser lain tetap menggunakan posternya masing-masing.

