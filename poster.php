<?php
function poster_url($name) {
 return is_string($name) && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/D',$name) && is_file(__DIR__.'/uploads/poster/'.$name) ? 'uploads/poster/'.$name : null;
}
function validate_poster_image($path) {
 $size=filesize($path);
 if(!$size || $size>2*1024*1024) throw new RuntimeException('Ukuran poster maksimal 2 MB.');
 $info=@getimagesize($path);
 if(!$info || !in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true)) throw new RuntimeException('Poster harus berupa gambar JPG, PNG, atau WebP yang valid.');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
 $expected=[IMAGETYPE_JPEG=>'image/jpeg',IMAGETYPE_PNG=>'image/png',IMAGETYPE_WEBP=>'image/webp'];
 if($mime!==$expected[$info[2]])throw new RuntimeException('Isi file poster tidak sesuai format gambar.');
 if($info[0]>6000 || $info[1]>6000 || $info[0]*$info[1]>12000000) throw new RuntimeException('Resolusi poster terlalu besar. Gunakan maksimal 12 megapiksel dan sisi maksimal 6000 piksel.');
 return $info;
}
function save_uploaded_poster() {
 if(!isset($_FILES['poster'])) return null;
 $file=$_FILES['poster'];
 if(!is_int($file['error']??null)) throw new RuntimeException('Upload poster tidak valid.');
 if($file['error']===UPLOAD_ERR_NO_FILE)return null;
 if($file['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Poster gagal diupload. Pilih gambar maksimal 2 MB dan coba lagi.');
 if(!is_string($file['tmp_name']??null)||!is_uploaded_file($file['tmp_name']))throw new RuntimeException('Upload poster tidak valid.');
 $info=validate_poster_image($file['tmp_name']);
 $extension=[IMAGETYPE_JPEG=>'jpg',IMAGETYPE_PNG=>'png',IMAGETYPE_WEBP=>'webp'][$info[2]];
 $folder=__DIR__.'/uploads/poster';
 if(!is_dir($folder)&&!mkdir($folder,0755,true))throw new RuntimeException('Folder poster tidak dapat dibuat.');
 // Nama dan ekstensi asli pengguna tidak digunakan.
 $name=bin2hex(random_bytes(16)).'.'.$extension;
 if(!move_uploaded_file($file['tmp_name'],$folder.'/'.$name))throw new RuntimeException('Poster gagal disimpan. Periksa izin folder uploads/poster.');
 return $name;
}
