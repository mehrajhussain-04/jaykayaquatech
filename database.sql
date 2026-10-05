-- ============================================================================
-- JAY KAY AQUA TECH — DATABASE SCHEMA (MySQL / MariaDB)
-- ============================================================================
-- Import this file via phpMyAdmin (or `mysql -u USER -p DBNAME < database.sql`)
-- into a database you've already created on your hosting control panel
-- (e.g. Hostinger's hPanel > Databases). After importing, edit
-- admin/includes/db-config.php with that database's host/name/user/password.
--
-- Includes the one required admin account with a securely hashed password
-- (never plaintext) and the same demo categories/products already shown on
-- the Phase 1 public site, so the admin panel is populated and testable
-- immediately after import.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Table: admins
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: categories
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: products
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `subcategory` VARCHAR(150) NULL,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `short_description` VARCHAR(500) NULL,
  `full_description` TEXT NULL,
  `features` TEXT NULL COMMENT 'JSON array of strings',
  `specifications` TEXT NULL COMMENT 'JSON array of [label, value] pairs',
  `applications` TEXT NULL COMMENT 'JSON array of strings',
  `main_image` VARCHAR(500) NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `is_demo` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_subcategory` (`subcategory`),
  KEY `idx_products_status` (`status`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: product_images  (supports multiple additional images per product)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(500) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product` (`product_id`),
  CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: enquiries  (from the public product enquiry form)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `product` VARCHAR(200) NULL,
  `quantity` VARCHAR(50) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','contacted','completed') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enquiries_status` (`status`),
  KEY `idx_enquiries_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: contact_messages  (from the public general contact form)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `email` VARCHAR(190) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_read` (`is_read`),
  KEY `idx_messages_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: events
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `event_date` DATE NULL,
  `image_path` VARCHAR(500) NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: catalogs  (downloadable PDF catalog files)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `catalogs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: settings  (small editable key/value site settings)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `skey` VARCHAR(100) NOT NULL,
  `svalue` TEXT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- INITIAL ADMIN ACCOUNT
-- ============================================================================
-- Email:    admin@jkaquatech.com
-- Password: JkAquaTech11@   (stored below as a bcrypt hash — NEVER plaintext)
-- Change this password immediately after your first login, from
-- /admin/settings.php.
-- ============================================================================
INSERT INTO `admins` (`id`, `email`, `password_hash`) VALUES
  (1, 'admin@jkaquatech.com', '$2y$10$8G3gfUk8SVBZDiybQ4o4YOPjMWkLg4VRuGbOwDlkcsMWBEro/BimO');

-- ============================================================================
-- BASELINE SETTINGS
-- ============================================================================
INSERT INTO `settings` (`skey`, `svalue`) VALUES
  ('company_email', ''),
  ('company_phone', ''),
  ('whatsapp_number', ''),
  ('company_address', 'S. NO-7, KASHIBAG, SOCIETY, OPP-AGRI COLLEGE GATE, VIJALPORE ROAD, NAVSARI – 396450, GUJARAT INDIA.'),
  ('facebook_url', ''),
  ('instagram_url', ''),
  ('youtube_url', ''),
  ('linkedin_url', '');

-- ----------------------------------------------------------------------------
-- Seed data: categories (matches the live Phase 1 public site exactly)
-- ----------------------------------------------------------------------------
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (1, 'aerators', 'Aerators', 'Paddle-wheel and surface aerators built to circulate and oxygenate pond water efficiently.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (2, 'floating-products', 'Floating Products', 'Floating jetties, docks and cage-aquaculture platforms for pond and farm infrastructure.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (3, 'long-arm-aerator', 'Long Arm Aerator', 'Long-reach aeration arms designed to cover larger water surfaces from a single mount point.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (4, 'probiotics', 'Probiotics', 'Probiotic formulations used by farmers as part of routine pond water management programs.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (5, 'aqua-chemicals', 'Aqua Chemicals', 'Chemical solutions used across pond preparation, water treatment and maintenance routines.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (6, 'aqua-medicine', 'Aqua Medicine', 'A range of aquaculture health products intended to support routine farm management.');
INSERT INTO `categories` (`id`, `slug`, `name`, `description`) VALUES (7, 'aerator-spares', 'Aerator Spares', 'Spare parts and components for maintaining and servicing aeration equipment.');

-- ----------------------------------------------------------------------------
-- Seed data: demo products (identical to the Phase 1 public catalog, flagged
-- is_demo = 1 so nothing new is fabricated — replace/edit freely from admin).
-- ----------------------------------------------------------------------------
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (1,1,'Paddle Wheel Aerator','3HP 8 Paddle Aerator','3hp-8-paddle-aerator','8-paddle aerator shown in the supplied Jay Kay Aqua Tech product images.','The supplied product images show a 3HP 8 Paddle Aerator with a floating paddle-wheel assembly. No additional technical specifications were supplied with the product images.','["3HP model indicated in the supplied filename","8-paddle configuration indicated in the supplied filename","Floating paddle-wheel aerator assembly shown in supplied images"]','[["Motor Power","3 HP (from supplied product filename)"],["Paddle Count","8 paddles (from supplied product filename)"]]','["Aquaculture pond aeration"]','assets/images/products/3hp-8-paddle-aerator.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (2,7,'Motor','2HP Motor','2hp-motor','2HP motor shown in the supplied product images for the aerator spare-parts catalogue.','A 2HP motor is shown in the supplied Jay Kay Aqua Tech product images. The supplied materials do not provide further motor specifications.','["2HP rating indicated in the supplied filenames","Flange-mounted motor shown in the supplied images"]','[["Power","2 HP (from supplied product filename)"]]','["Aerator motor replacement"]','assets/images/products/2hp-motor.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (3,7,'Worm Gear Box','2HP Worm Gear Box','2hp-worm-gear-box','Worm gear box shown in the supplied Jay Kay Aqua Tech product image.','The supplied image shows a worm gear box associated with the aerator spare-parts range. No further ratio, material or dimensional data was supplied.','["Worm gear box shown in supplied image","2HP reference included in the supplied filename"]','[["Reference","2HP (from supplied product filename)"]]','["Aerator gearbox replacement"]','assets/images/products/2hp-worm-gear-box.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (4,7,'Bevel Gear','Bevel Gear Spares','bevel-gear-spares','Bevel gear components shown in the supplied spare-parts images.','The supplied images show bevel gear components for the Jay Kay Aqua Tech aerator spare-parts catalogue.','["Bevel gear components shown in supplied images","Multiple gear-component views supplied"]','[]','["Aerator gear maintenance and replacement"]','assets/images/products/bevel-gear-spares.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (5,7,'Flange','Cast Iron Flange','cast-iron-flange','Cast iron flange shown in the supplied spare-part image.','The supplied product image identifies this component as a cast iron flange. No additional dimensional or compatibility data was supplied.','["Cast iron flange identified by supplied filename"]','[]','["Aerator assembly or spare-part use"]','assets/images/products/cast-iron-flange.webp','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (6,5,'Gear Oil','PetroMax EP-320 Gear Oil — 2L','petromax-ep320-2l','PetroMax EP-320 gear oil in the supplied 2L pack image.','The supplied package image identifies PetroMax EP-320 and states that it is formulated for aerator worm and bevel gears. The pack shown is 2L.','["PetroMax EP-320 product shown in supplied image","Package states use for aerator worm and bevel gears"]','[["Pack Size","2L (from supplied package image)"],["Product","PetroMax EP-320 (from supplied package image)"],["Stated Use","Aerator worm & bevel gears (from supplied package image)"]]','["Aerator worm and bevel gear lubrication"]','assets/images/products/petromax-ep320-2l.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (7,7,'Bevel Gear Box','Bevel Gear Box','bevel-gear-box','Bevel Gear Box shown in the supplied Jay Kay Aqua Tech spare-parts poster.','Bevel Gear Box shown in the supplied Jay Kay Aqua Tech spare-parts poster. No additional technical specifications were supplied with the poster.','["Bevel Gear Box shown in the supplied Jay Kay Aqua Tech spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/bevel-gear-box.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (8,7,'Gear Box Bearing / Oil Seal / Worm Gear Box','Gear Box Bearing / Oil Seal / Worm Gear Box','gear-box-bearing-oil-seal-worm-gear-box','Gear box bearing, oil seal and worm gear box components shown together in the supplied spare-parts poster.','Gear box bearing, oil seal and worm gear box components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Gear box bearing, oil seal and worm gear box components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/gear-box-bearing-oil-seal-worm-gear-box.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (9,7,'Spiral Gear Box','Spiral Gear Box','spiral-gear-box','Spiral gear box shown in the supplied spare-parts poster.','Spiral gear box shown in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Spiral gear box shown in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/spiral-gear-box.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (10,7,'Stopper / Spiral Spigot','Stopper / Spiral Spigot','stopper-spiral-spigot','Stopper and spiral spigot components shown together in the supplied spare-parts poster.','Stopper and spiral spigot components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Stopper and spiral spigot components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/stopper-spiral-spigot.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (11,7,'Plastic Shaft / S.S. Frame','Plastic Shaft / S.S. Frame','plastic-shaft-ss-frame','Plastic shaft and S.S. frame components shown together in the supplied spare-parts poster.','Plastic shaft and S.S. frame components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Plastic shaft and S.S. frame components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/plastic-shaft-ss-frame.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (12,7,'Worm Shaft / Motor Fan / Worm Gear Wheel','Worm Shaft / Motor Fan / Worm Gear Wheel','worm-shaft-motor-fan-worm-gear-wheel','Worm shaft, motor fan and worm gear wheel components shown together in the supplied spare-parts poster.','Worm shaft, motor fan and worm gear wheel components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Worm shaft, motor fan and worm gear wheel components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/worm-shaft-motor-fan-worm-gear-wheel.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (13,7,'HDPE Cover / Screw Packet','HDPE Cover / Screw Packet','hdpe-cover-screw-packet','HDPE cover and screw packet components shown together in the supplied spare-parts poster.','HDPE cover and screw packet components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["HDPE cover and screw packet components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/hdpe-cover-screw-packet.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (14,7,'Paddle Wheel / Plastic Nut','Paddle Wheel / Plastic Nut','paddle-wheel-plastic-nut','Paddle wheel and plastic nut components shown together in the supplied spare-parts poster.','Paddle wheel and plastic nut components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Paddle wheel and plastic nut components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/paddle-wheel-plastic-nut.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (15,7,'Bearing Stand / Bearing Cover Bushing','Bearing Stand / Bearing Cover Bushing','bearing-stand-bearing-cover-bushing','Bearing stand and bearing cover bushing components shown together in the supplied spare-parts poster.','Bearing stand and bearing cover bushing components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Bearing stand and bearing cover bushing components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/bearing-stand-bearing-cover-bushing.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (16,7,'U-Bush Stand','U-Bush Stand','u-bush-stand','U-Bush stand shown in the supplied spare-parts poster.','U-Bush stand shown in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["U-Bush stand shown in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/u-bush-stand.jpg','active',0);
INSERT INTO `products` (`id`,`category_id`,`subcategory`,`name`,`slug`,`short_description`,`full_description`,`features`,`specifications`,`applications`,`main_image`,`status`,`is_demo`) VALUES (17,7,'Moveable Joint / S.S. Shaft','Moveable Joint / S.S. Shaft','moveable-joint-ss-shaft','Moveable joint and S.S. shaft components shown together in the supplied spare-parts poster.','Moveable joint and S.S. shaft components shown together in the supplied spare-parts poster. No additional technical specifications were supplied with the poster.','["Moveable joint and S.S. shaft components shown together in the supplied spare-parts poster."]','[]','["Aerator spare-parts maintenance and replacement"]','assets/images/products/moveable-joint-ss-shaft.jpg','active',0);


