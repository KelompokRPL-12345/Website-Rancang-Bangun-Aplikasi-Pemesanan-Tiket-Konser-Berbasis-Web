USE tiket_konser;
-- Jalankan SEKALI untuk database versi sebelumnya. Tidak menghapus data.
ALTER TABLE konser ADD COLUMN poster VARCHAR(64) NULL DEFAULT NULL;
