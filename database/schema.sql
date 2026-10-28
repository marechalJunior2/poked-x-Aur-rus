-- Pokédex Database Schema
CREATE DATABASE IF NOT EXISTS `pokedex_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pokedex_db`;

DROP TABLE IF EXISTS `pokemons`;
CREATE TABLE `pokemons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `national_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `type1` VARCHAR(50) NOT NULL,
    `type2` VARCHAR(50) DEFAULT NULL,
    `total` INT NOT NULL DEFAULT 0,
    `hp` INT NOT NULL DEFAULT 0,
    `attack` INT NOT NULL DEFAULT 0,
    `defense` INT NOT NULL DEFAULT 0,
    `sp_atk` INT NOT NULL DEFAULT 0,
    `sp_def` INT NOT NULL DEFAULT 0,
    `speed` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255) NOT NULL,
    `is_secret` TINYINT(1) DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_national_id` (`national_id`),
    INDEX `idx_name` (`name`),
    INDEX `idx_type1` (`type1`),
    INDEX `idx_type2` (`type2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `scan_history`;
CREATE TABLE `scan_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pokemon_id` INT NOT NULL,
    `session_id` VARCHAR(128) NOT NULL,
    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`pokemon_id`) REFERENCES `pokemons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
