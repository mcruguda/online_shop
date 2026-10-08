-- üK295 LB1 — import in phpMyAdmin (select database lb1_uek295, then SQL or Import tab).
-- Tables per ER diagram, API user, and sample category.

CREATE DATABASE IF NOT EXISTS `lb1_uek295`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `lb1_uek295`;

DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `category` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `name` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product` (
  `product_id` INT NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `id_category` INT NULL,
  `name` VARCHAR(500) NOT NULL,
  `image` VARCHAR(1000) NULL,
  `description` TEXT NULL,
  `price` DECIMAL(65,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `uk_product_sku` (`sku`),
  KEY `idx_product_category` (`id_category`),
  CONSTRAINT `fk_product_category`
    FOREIGN KEY (`id_category`)
    REFERENCES `category` (`category_id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `category` (`category_id`, `active`, `name`) VALUES
  (1, 1, 'Firmen-Logos');

INSERT INTO `users` (`username`, `password_hash`) VALUES
  ('admin', '$2y$12$mfQk/UQK/QCJ0y2j1NWsDeHe6DtM2ltoQR5Y9etHdEfV.29zppaB6');
