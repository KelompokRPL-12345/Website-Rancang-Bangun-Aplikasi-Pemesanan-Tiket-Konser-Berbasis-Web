<?php
require __DIR__.'/config.php';
require __DIR__.'/poster.php';
$error=''; $konser=[]; $booking=null;
try {
 if($_SERVER['REQUEST_METHOD']==='POST') {
  if(!is_string($_POST['csrf']??null)||!hash_equals($_SESSION['csrf'],$_POST['csrf'])) throw new RuntimeException('Muat ulang halaman lalu coba lagi.');
  $nama=is_string($_POST['nama']??null)?trim($_POST['nama']):'';
  $email=is_string($_POST['email']??null)?trim($_POST['email']):'';
  $jumlah=filter_var($_POST['jumlah']??'',FILTER_VALIDATE_INT);
  $id=filter_var($_POST['konser_id']??'',FILTER_VALIDATE_INT);
  if(strlen($nama)<2||strlen($nama)>100||strlen($email)>150||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$jumlah||$jumlah<1||$jumlah>5||!$id) throw new RuntimeException('Periksa nama, email, dan jumlah tiket (1–5).');
  $pdo=db(); $pdo->beginTransaction();
  $q=$pdo->prepare('SELECT * FROM konser WHERE id=? FOR UPDATE'); $q->execute([$id]); $k=$q->fetch();
  if(!$k||strtotime($k['tanggal'])<=time()) throw new RuntimeException('Konser tidak tersedia.');
  if($k['stok']<$jumlah) throw new RuntimeException('Stok tiket tidak cukup.');
  $kode='TK-'.strtoupper(bin2hex(random_bytes(12)));
  $pdo->prepare('INSERT INTO pemesanan (kode,konser_id,nama,email,jumlah,total) VALUES (?,?,?,?,?,?)')->execute([$kode,$id,$nama,$email,$jumlah,$k['harga']*$jumlah]);
  $pdo->prepare('UPDATE konser SET stok=stok-? WHERE id=?')->execute([$jumlah,$id]);
  $pdo->commit(); $_SESSION['booking']=$kode;
  header('Location: index.php?berhasil=1#booking'); exit;
 }
 $konser=db()->query('SELECT * FROM konser ORDER BY tanggal')->fetchAll();
 if(isset($_GET['berhasil'],$_SESSION['booking'])) {
  $q=db()->prepare('SELECT p.*,k.nama AS konser_nama,k.lokasi,k.tanggal FROM pemesanan p JOIN konser k ON p.konser_id=k.id WHERE p.kode=?'); $q->execute([$_SESSION['booking']]); $booking=$q->fetch();
 }
} catch(Throwable $ex) {
 if(isset($pdo)&&$pdo->inTransaction()) $pdo->rollBack();
 if($ex instanceof PDOException){error_log($ex->getMessage());$error='Database belum terhubung. Aktifkan MySQL, impor database.sql, lalu periksa config.php.';}else{$error=$ex->getMessage();}
 try{$konser=db()->query('SELECT * FROM konser ORDER BY tanggal')->fetchAll();}catch(Throwable $ignored){}
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>moonvenue — Tiket Konser</title><link rel="stylesheet" href="style.css"></head><body>
<header class="wrap nav"><a class="brand" href="index.php">moonvenue<span>●</span></a><nav><a href="#konser">Jelajahi konser</a><a href="#pesan">Pesan tiket ↗</a><a href="admin.php">Admin</a></nav><small>MUSIC.MOMENTS.YOURSELF</small></header>
<main class="wrap"><section class="hero"><div><p class="eyebrow">PANGGUNG BERIKUTNYA MENUNGGUMU</p><h1>Lebih dekat<br>dengan <em>musiknya.</em></h1><p class="intro">Temukan konser pilihan, amankan tiketmu, dan jadi bagian dari momen yang selalu kamu ingat.</p><a class="button" href="#konser">Temukan konser ↗</a><p class="muted">Pilih konser · Isi data · Dapatkan kode booking</p></div><div class="poster" aria-hidden="true"><small>LIVE MUSIC<br>GOOD TIMES ONLY</small><div class="disc"><b>moonvenue<br>↗</b></div><div class="poster-title">FEEL THE<br><i>LIVE.</i></div></div></section>
<?php if($error):?><div class="alert" role="alert"><?=e($error)?></div><?php endif;?>
<?php if($booking):?><section class="booking" id="booking"><p class="eyebrow">PESANAN BERHASIL DISIMPAN</p><h2>Tiketmu sudah dipesan!</h2><p><?=e($booking['nama'])?> · <?=e($booking['konser_nama'])?></p><p><?=e(date('d/m/Y H:i',strtotime($booking['tanggal'])))?> WIB · <?=e($booking['lokasi'])?></p><p><strong><?=e($booking['jumlah'])?> tiket · <?=e(rp($booking['total']))?></strong></p><p>Kode booking: <code><?=e($booking['kode'])?></code></p><p class="muted">Bukti pemesanan demo. Tidak ada pembayaran atau email otomatis.</p><button class="button" onclick="window.print()">Cetak bukti booking</button></section><?php endif;?>
<section class="events" id="konser"><div class="section-head"><div><p class="eyebrow">CHOOSE YOUR MOMENT</p><h2>UPCOMING</h2></div><p>Find Your Nextlive Experience.</p></div><div class="grid">
<?php foreach($konser as $i=>$k):$available=$k['stok']>0&&strtotime($k['tanggal'])>time();?>
<article class="card"><?php $poster=poster_url($k['poster']??null);if($poster):?><div class="concert-poster"><img src="<?=e($poster)?>" alt="Poster <?=e($k['nama'])?>" loading="lazy"></div><?php else:?><div class="art art-<?=$i%3?>"><span>LIVE MUSIC / <?=e(date('Y',strtotime($k['tanggal'])))?></span><div class="shape" aria-hidden="true"></div><h3><?=e($k['nama'])?></h3></div><?php endif;?><div class="card-body"><?php if($poster):?><h3 class="concert-name"><?=e($k['nama'])?></h3><?php endif;?><p class="date"><?=e(date('d/m/Y • H:i',strtotime($k['tanggal'])))?> WIB</p><p class="location"><?=e($k['lokasi'])?></p><div class="price"><div><small>Harga per tiket</small><strong><?=e(rp($k['harga']))?></strong></div><span><?=e($k['stok'])?> tersisa</span></div><button class="choose" data-id="<?=e($k['id'])?>" <?=$available?'':'disabled'?>><?=$available?'Pesan tiket ↗':'Tidak tersedia'?></button></div></article>
<?php endforeach;?><?php if(!$konser&&!$error):?><p>Belum ada konser tersedia.</p><?php endif;?></div></section>
<section id="pesan" class="order"><div><p class="eyebrow">TINGGAL SELANGKAH LAGI</p><h2>Safe<br>Your Seats.</h2><p>Isi data dengan benar dan simpan kode booking setelah pemesanan berhasil.</p><div class="steps"><p>01 &nbsp; Pilih konser favoritmu</p><p>02 &nbsp; Isi data dan jumlah tiket</p><p>03 &nbsp; Simpan bukti pemesanan</p></div></div><form method="post" action="index.php"><input type="hidden" name="csrf" value="<?=e($_SESSION['csrf'])?>"><label for="konser_id">Pilih konser</label><select name="konser_id" id="konser_id" required><option value="">Pilih konser yang ingin kamu datangi</option><?php foreach($konser as $k):if($k['stok']<1||strtotime($k['tanggal'])<=time())continue;?><option value="<?=e($k['id'])?>" data-price="<?=e($k['harga'])?>" data-stock="<?=e($k['stok'])?>"><?=e($k['nama'])?></option><?php endforeach;?></select><label for="nama">Nama lengkap</label><input name="nama" id="nama" placeholder="Nama sesuai identitas" autocomplete="name" minlength="2" maxlength="100" required><label for="email">Email</label><input name="email" id="email" type="email" placeholder="nama@email.com" autocomplete="email" maxlength="150" required><label for="jumlah">Jumlah tiket (maks. 5)</label><input name="jumlah" id="jumlah" type="number" min="1" max="5" value="1" required><div class="total"><span>Total pesanan</span><strong id="total" aria-live="polite">Rp 0</strong></div><button class="button submit">Konfirmasi pemesanan ↗</button><p class="muted">Aplikasi demo tugas. Tanpa transaksi pembayaran.</p></form></section></main><footer class="wrap">
  <a class="brand" href="index.php">moonvenue<span>●</span></a>

  <p>© <?= date('Y') ?> moonvenue. All Rights Reserved.</p>

  <small>
    Mengalami kendala?
    <a href="@gmail.com">Hubungi kami</a>
  </small>
</footer><script src="app.js"></script></body></html>

