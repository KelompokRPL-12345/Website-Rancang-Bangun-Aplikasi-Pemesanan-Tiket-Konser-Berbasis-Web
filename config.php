<?php
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']);
session_start();
date_default_timezone_set('Asia/Jakarta');
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function db() {
 static $db;
 return $db ??= new PDO('mysql:host=127.0.0.1;dbname=tiket_konser;charset=utf8mb4','root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
function e($v) {return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function rp($v) {return 'Rp '.number_format($v,0,',','.');}

