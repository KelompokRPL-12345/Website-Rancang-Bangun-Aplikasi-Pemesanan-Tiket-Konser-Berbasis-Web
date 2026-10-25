<?php
require __DIR__.'/config.php';
header('Cache-Control: no-store');
function field($key) { return is_string($_POST[$key]??null)?trim($_POST[$key]):''; }
function csrf_check() {
 if(!is_string($_POST['csrf']??null)||!hash_equals($_SESSION['csrf'],$_POST['csrf'])) throw new RuntimeException('Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.');
}
function redirect_admin($view='konser') { header('Location: admin.php?view='.$view); exit; }
function flash($message) { $_SESSION['admin_flash']=$message; }
$error=''; $admin=null; $items=[]; $orders=[]; $stats=[]; $edit=null;
$view=($_GET['view']??'')==='pesanan'?'pesanan':'konser';
try {
 if(isset($_SESSION['admin_id'])) {
  $q=db()->prepare('SELECT id,username FROM admin WHERE id=?'); $q->execute([$_SESSION['admin_id']]); $admin=$q->fetch();
  if(!$admin) unset($_SESSION['admin_id']);
 }
 if($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check(); $action=field('action');
  if($action==='login') {
   $q=db()->prepare('SELECT * FROM admin WHERE username=?'); $q->execute([field('username')]); $user=$q->fetch();
   $password=is_string($_POST['password']??null)?$_POST['password']:'';
   if(!$user||!password_verify($password,$user['password_hash'])) throw new RuntimeException('Username atau password salah.');
   session_regenerate_id(true); $_SESSION['admin_id']=$user['id']; $_SESSION['csrf']=bin2hex(random_bytes(32));
   redirect_admin();
  }
  if(!$admin) { http_response_code(403); throw new RuntimeException('Silakan login sebagai admin terlebih dahulu.'); }
  if($action==='logout') {
   unset($_SESSION['admin_id']); session_regenerate_id(true); $_SESSION['csrf']=bin2hex(random_bytes(32)); redirect_admin();
  }
  if($action==='save') {
   $id=filter_var($_POST['id']??'',FILTER_VALIDATE_INT);
   $harga=filter_var($_POST['harga']??'',FILTER_VALIDATE_INT);
   $stok=filter_var($_POST['stok']??'',FILTER_VALIDATE_INT);
   $nama=field('nama');$lokasi=field('lokasi');$tanggal=field('tanggal');
   $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$tanggal);
   if($id===false||$id<0||strlen($nama)<2||strlen($nama)>120||strlen($lokasi)<2||strlen($lokasi)>160||!$date||$date->format('Y-m-d\TH:i')!==$tanggal||$harga===false||$harga<1||$harga>100000000||$stok===false||$stok<0||$stok>1000000) throw new RuntimeException('Isi nama/lokasi, tanggal valid, harga Rp 1–100.000.000, dan stok 0–1.000.000.');
   $pdo=db();$pdo->beginTransaction();
   if($id>0) {
    $q=$pdo->prepare('SELECT * FROM konser WHERE id=? FOR UPDATE');$q->execute([$id]);$current=$q->fetch();
    if(!$current) throw new RuntimeException('Konser tidak ditemukan.');
    // Hindari menimpa stok jika pesanan masuk sejak formulir dibuka.
    $snapshot=hash('sha256',json_encode($current));
    if(!hash_equals($snapshot,field('snapshot'))) throw new RuntimeException('Data atau stok berubah sejak formulir dibuka. Buka ulang Edit lalu coba lagi.');
    $pdo->prepare('UPDATE konser SET nama=?,lokasi=?,tanggal=?,harga=?,stok=? WHERE id=?')->execute([$nama,$lokasi,$date->format('Y-m-d H:i:s'),$harga,$stok,$id]);
   } else {
    $pdo->prepare('INSERT INTO konser (nama,lokasi,tanggal,harga,stok) VALUES (?,?,?,?,?)')->execute([$nama,$lokasi,$date->format('Y-m-d H:i:s'),$harga,$stok]);
   }
   $pdo->commit();flash('Data konser berhasil disimpan.');redirect_admin();
  }
  if($action==='delete') {
   $id=filter_var($_POST['id']??'',FILTER_VALIDATE_INT);
   if(!$id) throw new RuntimeException('Konser tidak valid.');
   $pdo=db();$pdo->beginTransaction();
   $q=$pdo->prepare('SELECT id FROM konser WHERE id=? FOR UPDATE');$q->execute([$id]);
   if(!$q->fetch()) throw new RuntimeException('Konser tidak ditemukan.');
   $q=$pdo->prepare('SELECT COUNT(*) FROM pemesanan WHERE konser_id=?');$q->execute([$id]);
   if($q->fetchColumn()>0) throw new RuntimeException('Konser sudah memiliki pemesanan dan tidak dapat dihapus. Atur stok menjadi 0 untuk menghentikan penjualan.');
   $pdo->prepare('DELETE FROM konser WHERE id=?')->execute([$id]);$pdo->commit();flash('Konser berhasil dihapus.');redirect_admin();
  }
  throw new RuntimeException('Tindakan tidak dikenali.');
 }
} catch(Throwable $ex) {
 if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();
 if($ex instanceof PDOException){error_log($ex->getMessage());$error='Database belum siap. Aktifkan MySQL, impor database.sql, dan jalankan buat-admin.bat.';}else{$error=$ex->getMessage();}
}
if($admin) {
 try {
  $items=db()->query('SELECT * FROM konser ORDER BY tanggal')->fetchAll();
  $stats=db()->query('SELECT COUNT(*) AS pesanan,COALESCE(SUM(jumlah),0) AS tiket,COALESCE(SUM(total),0) AS nilai FROM pemesanan')->fetch();
  if($view==='pesanan') $orders=db()->query('SELECT p.*,k.nama AS konser_nama FROM pemesanan p JOIN konser k ON k.id=p.konser_id ORDER BY p.id DESC')->fetchAll();
  $editId=filter_var($_GET['edit']??'',FILTER_VALIDATE_INT);
  if($editId){foreach($items as $item){if($item['id']==$editId){$edit=$item;break;}}if(!$edit)$error='Konser tidak ditemukan.';}
 }catch(Throwable $ex){error_log($ex->getMessage());$error='Data tidak dapat dimuat. Periksa koneksi database.';}
}
$message=$_SESSION['admin_flash']??'';unset($_SESSION['admin_flash']);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin — Nada</title><link rel="stylesheet" href="style.css"><link rel="stylesheet" href="admin.css"></head><body>
<header class="wrap nav"><a class="brand" href="index.php">nada<span>●</span></a><nav><a href="index.php">Lihat website ↗</a><?php if($admin):?><form class="inline" method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="logout"><button class="logout">Logout</button></form><?php endif;?></nav></header>
<main class="wrap admin-main">
<?php if($error):?><div class="alert" role="alert"><?=e($error)?></div><?php endif;?>
<?php if($message):?><div class="notice" role="status"><?=e($message)?></div><?php endif;?>
<?php if(!$admin):?>
<section class="login"><p class="eyebrow">RUANG PENGELOLA</p><h1>Login admin.</h1><p>Kelola konser dan pantau pemesanan tiket.</p><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="login"><label for="username">Username</label><input id="username" name="username" autocomplete="username" maxlength="50" required><label for="password">Password</label><input type="password" id="password" name="password" autocomplete="current-password" maxlength="72" required><button class="button submit">Masuk ke dashboard ↗</button></form><p class="muted">Belum punya akun? Jalankan file buat-admin.bat dari folder aplikasi di komputer server.</p></section>
<?php else:?>
<p class="eyebrow">DASHBOARD ADMIN</p><h1 class="admin-title">Halo, <?=e($admin['username'])?>.</h1><p>Kelola panggung berikutnya dari sini.</p>
<div class="stats"><article><small>Konser</small><strong><?=count($items)?></strong></article><article><small>Pesanan</small><strong><?=e($stats['pesanan']??0)?></strong></article><article><small>Tiket dipesan</small><strong><?=e($stats['tiket']??0)?></strong></article><article><small>Nilai pemesanan (bukan pembayaran)</small><strong><?=e(rp($stats['nilai']??0))?></strong></article></div>
<nav class="tabs"><a class="<?=$view==='konser'?'active':''?>" href="admin.php">Kelola konser</a><a class="<?=$view==='pesanan'?'active':''?>" href="admin.php?view=pesanan">Daftar pemesanan</a></nav>
<?php if($view==='konser'):?>
<section class="panel"><div class="section-head"><h2>Daftar konser</h2><a class="button" href="admin.php#editor">+ Tambah konser</a></div><div class="table-scroll"><table><thead><tr><th>Konser / lokasi</th><th>Jadwal (WIB)</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($items as $k):?><tr><td><strong><?=e($k['nama'])?></strong><br><small><?=e($k['lokasi'])?></small></td><td><?=e(date('d/m/Y H:i',strtotime($k['tanggal'])))?></td><td><?=e(rp($k['harga']))?></td><td><?=e($k['stok'])?></td><td><div class="actions"><a href="admin.php?edit=<?=e($k['id'])?>#editor">Edit</a><form class="inline" method="post" onsubmit="return confirm('Hapus konser ini? Konser yang memiliki pesanan tidak dapat dihapus.')"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($k['id'])?>"><button class="danger">Hapus</button></form></div></td></tr><?php endforeach;?>
<?php if(!$items):?><tr><td colspan="5">Belum ada konser. Tambahkan konser pertama di bawah.</td></tr><?php endif;?></tbody></table></div></section>
<section class="panel" id="editor"><h2><?=$edit?'Edit konser':'Tambah konser'?></h2><p class="muted">Stok adalah sisa tiket yang tersedia. Stok 0 menghentikan pemesanan.</p><form method="post"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>"><input type="hidden" name="snapshot" value="<?=e($edit?hash('sha256',json_encode($edit)):'')?>"><div class="admin-fields"><div><label for="nama">Nama konser</label><input id="nama" name="nama" minlength="2" maxlength="120" value="<?=e($edit['nama']??'')?>" required></div><div><label for="lokasi">Lokasi</label><input id="lokasi" name="lokasi" minlength="2" maxlength="160" value="<?=e($edit['lokasi']??'')?>" required></div><div><label for="tanggal">Tanggal dan jam (WIB)</label><input id="tanggal" type="datetime-local" name="tanggal" value="<?=e($edit?date('Y-m-d\TH:i',strtotime($edit['tanggal'])):'')?>" required></div><div><label for="harga">Harga per tiket (Rp)</label><input id="harga" type="number" name="harga" min="1" max="100000000" value="<?=e($edit['harga']??'')?>" required></div><div><label for="stok">Sisa stok tiket</label><input id="stok" type="number" name="stok" min="0" max="1000000" value="<?=e($edit['stok']??'')?>" required></div></div><button class="button">Simpan konser</button> <a class="cancel" href="admin.php">Batal / kosongkan</a></form></section>
<?php else:?>
<section class="panel"><h2>Daftar pemesanan</h2><p class="muted">Pesanan simulasi, belum mencakup pembayaran. Total pesanan tetap mengikuti harga saat pemesanan.</p><div class="table-scroll"><table><thead><tr><th>Kode / waktu pesan</th><th>Pemesan</th><th>Konser</th><th>Tiket</th><th>Total</th></tr></thead><tbody><?php foreach($orders as $o):?><tr><td><code><?=e($o['kode'])?></code><br><small><?=e($o['dibuat_pada'])?></small></td><td><?=e($o['nama'])?><br><small><?=e($o['email'])?></small></td><td><?=e($o['konser_nama'])?></td><td><?=e($o['jumlah'])?></td><td><?=e(rp($o['total']))?></td></tr><?php endforeach;?><?php if(!$orders):?><tr><td colspan="5">Belum ada pemesanan.</td></tr><?php endif;?></tbody></table></div></section>
<?php endif;?><?php endif;?></main></body></html>
