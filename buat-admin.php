<?php
// Hanya dapat dijalankan dari komputer/server, bukan melalui browser.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/config.php';
try {
 db()->exec(file_get_contents(__DIR__.'/admin-table.sql'));
 echo "Buat akun admin (password diketik terlihat di jendela ini).\nUsername: ";
 $username=trim(fgets(STDIN));
 echo 'Password (minimal 8, maksimal 72 karakter): ';
 $password=rtrim(fgets(STDIN),"\r\n");
 if(!preg_match('/^[a-zA-Z0-9_]{3,50}$/',$username)||strlen($password)<8||strlen($password)>72) throw new RuntimeException('Username 3-50 huruf/angka/underscore; password 8-72 karakter.');
 echo 'Ulangi password: ';
 if($password!==rtrim(fgets(STDIN),"\r\n")) throw new RuntimeException('Konfirmasi password tidak sama.');
 db()->prepare('INSERT INTO admin (username,password_hash) VALUES (?,?)')->execute([$username,password_hash($password,PASSWORD_DEFAULT)]);
 echo "Akun berhasil dibuat. Buka http://localhost/tiket-konser/admin.php untuk login.\n";
} catch(Throwable $ex) {
 if($ex instanceof PDOException) echo "Gagal: periksa MySQL, impor database.sql, dan pastikan username belum digunakan.\n";
 else echo $ex->getMessage()."\n";
 exit(1);
}
