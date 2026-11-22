-- Muga Kopi: skema + data awal (menu asli dari menu cetak Muga Kopi)
CREATE DATABASE IF NOT EXISTS muga_kopi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE muga_kopi;
SET NAMES utf8mb4;
DROP TABLE IF EXISTS order_items, orders, menu_items, categories, admins, cafe_tables, membership_feedback;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(60) NOT NULL UNIQUE,
  name VARCHAR(80) NOT NULL,
  image VARCHAR(120) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  price INT UNSIGNED NOT NULL,
  image VARCHAR(160) NOT NULL DEFAULT '',
  is_spicy TINYINT(1) NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(8) NOT NULL UNIQUE,
  customer_name VARCHAR(60) NOT NULL,
  table_no VARCHAR(10) NOT NULL,
  payment_method ENUM('tunai','qris') NOT NULL DEFAULT 'tunai',
  note VARCHAR(200) NOT NULL DEFAULT '',
  total INT UNSIGNED NOT NULL,
  status ENUM('baru','diproses','diantar','selesai','batal') NOT NULL DEFAULT 'baru',
  payment_status ENUM('belum','menunggu','lunas','gagal') NOT NULL DEFAULT 'belum',
  paid_at DATETIME NULL,
  gateway_order_id VARCHAR(40) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (status), INDEX (created_at)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED NULL,
  name VARCHAR(100) NOT NULL,
  price INT UNSIGNED NOT NULL,
  qty SMALLINT UNSIGNED NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cafe_tables (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  table_no VARCHAR(10) NOT NULL UNIQUE,
  token CHAR(10) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE membership_feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  interest ENUM('ya','mungkin','tidak') NOT NULL,
  benefits VARCHAR(120) NOT NULL DEFAULT '',
  comment VARCHAR(300) NOT NULL DEFAULT '',
  contact VARCHAR(80) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Akun kasir awal: username kasir / password muga2026 (GANTI sebelum dipakai sungguhan)
INSERT INTO admins (username,password_hash) VALUES ('kasir','$2y$10$tX5LyAqenLTl3ztIM0aOiewhnqSQlROP6MeiB7ULWrVpcwDqiixAi');

INSERT INTO categories (id,slug,name,image,sort_order) VALUES (1,'signature','Muga Signature','signature.webp',1);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (2,'americano','Americano Series','americano.webp',2);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (3,'mont-blanc','Mont Blanc Series','montblanc.webp',3);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (4,'coffee','Coffee Series','coffee.webp',4);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (5,'matcha','Matcha Series','matcha.webp',5);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (6,'non-coffee','Non Coffee','noncoffee.webp',6);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (7,'tea','Tea Series','tea.webp',7);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (8,'refreshment','Refreshment','refresh.webp',8);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (9,'main-course','Main Course','maincourse.webp',9);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (10,'rice-bowl','Rice Bowl','ricebowl.webp',10);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (11,'snack','Snack','snack.webp',11);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (12,'mie-series','Mie Series','pasta.webp',12);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (13,'cheesecake','Cheesecake','cheesecake-japanese.webp',13);
INSERT INTO categories (id,slug,name,image,sort_order) VALUES (14,'additional','Additional','',14);

INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (1,'Kopi Muga (Soft)','kopi-muga-soft',25000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (1,'Kopi Teman Muga (Strong)','kopi-teman-muga-strong',25000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (1,'Magic','magic',30000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (1,'Dirty Latte','dirty-latte',30000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (1,'Dirty Matcha','dirty-matcha',30000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Americano','americano',25000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Strawberry Brew','strawberry-brew',25000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Lemonade Brew','lemonade-brew',25000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Passion Brew','passion-brew',25000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Orange Brew','orange-brew',25000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (2,'Pineapple Brew','pineapple-brew',25000,'',0,6);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (3,'Strawberry Blanc','strawberry-blanc',28000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (3,'Lemonade Blanc','lemonade-blanc',28000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (3,'Passion Blanc','passion-blanc',28000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (3,'Orange Blanc','orange-blanc',28000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (3,'Pineapple Blanc','pineapple-blanc',28000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Creamy Latte','creamy-latte',25000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Butterscotch Sea Salt','butterscotch-sea-salt',28000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Pandan Latte','pandan-latte',28000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Caramel Latte','caramel-latte',28000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Cream Cheese Latte','cream-cheese-latte',28000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (4,'Martabak Manis Latte','martabak-manis-latte',28000,'',0,6);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (5,'Ube Matcha','ube-matcha',30000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (5,'Strawberry Matcha','strawberry-matcha',30000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (5,'Sea Salt Matcha','sea-salt-matcha',30000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (5,'Oat Matcha','oat-matcha',30000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (6,'Chocolate Milk','chocolate-milk',28000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (6,'Cookies & Cream','cookies-dan-cream',28000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (6,'Lotus Milk','lotus-milk',28000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (6,'Nutella Milk','nutella-milk',28000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (6,'Strawberry Milk','strawberry-milk',28000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (7,'Lychee Tea','lychee-tea',25000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (7,'Lemon Tea','lemon-tea',25000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (7,'Strawberry Tea','strawberry-tea',25000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (7,'Ollong Tea','ollong-tea',25000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (8,'Lychee Yakult','lychee-yakult',25000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (8,'Orange Yakult','orange-yakult',25000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (8,'Lemonade Yakult','lemonade-yakult',25000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (9,'Nasi Goreng Merah','nasi-goreng-merah',30000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (9,'Nasi Goreng Tradisional','nasi-goreng-tradisional',30000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (10,'Rice Bowl Salted Egg','rice-bowl-salted-egg',30000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (10,'Rice Bowl Buttermilk','rice-bowl-buttermilk',30000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (10,'Rice Bowl Sambal Matah','rice-bowl-sambal-matah',30000,'',1,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (10,'Rice Bowl Kecap Mayo','rice-bowl-kecap-mayo',30000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Korean Spicy Chicken Wings','korean-spicy-chicken-wings',35000,'wings.webp',1,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Sweet Garlic Chicken Wings','sweet-garlic-chicken-wings',35000,'wings.webp',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Mix Platter','mix-platter',25000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Cireng','cireng',25000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Pisang Cokelat Keju','pisang-cokelat-keju',25000,'',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Pisang Original','pisang-original',25000,'',0,6);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Kentang Goreng','kentang-goreng',25000,'',0,7);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Tahu Bakso Goreng','tahu-bakso-goreng',25000,'',0,8);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Ubi Goreng','ubi-goreng',25000,'',0,9);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (11,'Tempe Mendoan','tempe-mendoan',25000,'',0,10);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (12,'Spaghetti Chicken Alfredo','spaghetti-chicken-alfredo',35000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (12,'Spaghetti Chicken Bolognese','spaghetti-chicken-bolognese',35000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (12,'Spaghetti Chicken Aglio Olio','spaghetti-chicken-aglio-olio',35000,'',1,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (12,'Mie Goreng','mie-goreng',25000,'',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Strawberry Cheesecake','strawberry-cheesecake',30000,'cheesecake-strawberry.webp',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Blueberry Cheesecake','blueberry-cheesecake',30000,'cheesecake-blueberry.webp',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Japanese Cheesecake','japanese-cheesecake',30000,'cheesecake-japanese.webp',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Brownies Burnt Cheesecake','brownies-burnt-cheesecake',30000,'cheesecake-brownies.webp',0,4);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Matcha Burnt Cheesecake','matcha-burnt-cheesecake',30000,'cheesecake-matcha.webp',0,5);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (13,'Chocolate Burnt Cheesecake','chocolate-burnt-cheesecake',30000,'cheesecake-chocolate.webp',0,6);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (14,'Air Mineral','air-mineral',5000,'',0,1);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (14,'Oat Milk','oat-milk',5000,'',0,2);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (14,'Extra Shot','extra-shot',5000,'',0,3);
INSERT INTO menu_items (category_id,name,slug,price,image,is_spicy,sort_order) VALUES (14,'Nasi','nasi',5000,'',0,4);

INSERT INTO cafe_tables (table_no,token) VALUES ('1','3d4988203c');
INSERT INTO cafe_tables (table_no,token) VALUES ('2','df85887f56');
INSERT INTO cafe_tables (table_no,token) VALUES ('3','4c59621269');
INSERT INTO cafe_tables (table_no,token) VALUES ('4','62a11cf507');
INSERT INTO cafe_tables (table_no,token) VALUES ('5','cedd137452');
INSERT INTO cafe_tables (table_no,token) VALUES ('6','a585e3bfe8');
INSERT INTO cafe_tables (table_no,token) VALUES ('7','b9b5db5313');
INSERT INTO cafe_tables (table_no,token) VALUES ('8','39d0bf32c3');
INSERT INTO cafe_tables (table_no,token) VALUES ('9','39b000732b');
INSERT INTO cafe_tables (table_no,token) VALUES ('10','65ab26b96e');
INSERT INTO cafe_tables (table_no,token) VALUES ('11','3fb24dc1e3');
INSERT INTO cafe_tables (table_no,token) VALUES ('12','2fbed18cab');
