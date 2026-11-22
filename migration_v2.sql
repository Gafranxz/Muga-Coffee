-- Migrasi ke versi 2 (QR meja, pembayaran, laporan, membership). Aman untuk database yang sudah ada.
USE muga_kopi;
ALTER TABLE orders
  ADD COLUMN payment_status ENUM('belum','menunggu','lunas','gagal') NOT NULL DEFAULT 'belum' AFTER status,
  ADD COLUMN paid_at DATETIME NULL AFTER payment_status,
  ADD COLUMN gateway_order_id VARCHAR(40) NULL AFTER paid_at;
CREATE TABLE IF NOT EXISTS cafe_tables (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  table_no VARCHAR(10) NOT NULL UNIQUE,
  token CHAR(10) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS membership_feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interest ENUM('ya','mungkin','tidak') NOT NULL,
  benefits VARCHAR(120) NOT NULL DEFAULT '',
  comment VARCHAR(300) NOT NULL DEFAULT '',
  contact VARCHAR(80) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- Pesanan lama yang sudah selesai dianggap lunas agar masuk laporan
UPDATE orders SET payment_status='lunas', paid_at=updated_at WHERE status='selesai';
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('1','08aa2530b1');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('2','51f9d37ded');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('3','bc609cd103');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('4','e86f2c6c83');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('5','016921c496');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('6','18290085c0');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('7','d13c84934f');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('8','f3b3fb8edc');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('9','4a4af04ed2');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('10','d7a5e06128');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('11','c225f32f65');
INSERT IGNORE INTO cafe_tables (table_no,token) VALUES ('12','4b0dc3d98e');
