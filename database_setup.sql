-- Inventory Management System Database Setup
-- This creates the database and all required tables

CREATE DATABASE IF NOT EXISTS `inventory_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `inventory_management`;

-- Accounts table (Chart of Accounts for double-entry bookkeeping)
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Asset','Liability','Equity','Revenue','Expense') NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_code_unique` (`code`),
  KEY `accounts_parent_id_foreign` (`parent_id`),
  CONSTRAINT `accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `purchase_price` decimal(15,2) NOT NULL,
  `sell_price` decimal(15,2) NOT NULL,
  `opening_stock` int(11) NOT NULL DEFAULT 0,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Entries table
CREATE TABLE IF NOT EXISTS `journal_entries` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `description` text NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entries_date_index` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Journal Lines table
CREATE TABLE IF NOT EXISTS `journal_lines` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_lines_journal_entry_id_foreign` (`journal_entry_id`),
  KEY `journal_lines_account_id_foreign` (`account_id`),
  CONSTRAINT `journal_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `journal_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales table
CREATE TABLE IF NOT EXISTS `sales` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due` decimal(15,2) NOT NULL DEFAULT 0.00,
  `journal_entry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_number_unique` (`invoice_number`),
  KEY `sales_date_index` (`date`),
  KEY `sales_journal_entry_id_foreign` (`journal_entry_id`),
  CONSTRAINT `sales_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sale Items table
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements table
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('Opening','Purchase','Sale','Adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `date` date NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_date_index` (`date`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Chart of Accounts
INSERT INTO `accounts` (`id`, `code`, `name`, `type`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, '1000', 'Assets', 'Asset', NULL, NOW(), NOW()),
(2, '1100', 'Current Assets', 'Asset', 1, NOW(), NOW()),
(3, '1110', 'Cash', 'Asset', 2, NOW(), NOW()),
(4, '1120', 'Accounts Receivable', 'Asset', 2, NOW(), NOW()),
(5, '1130', 'Inventory', 'Asset', 2, NOW(), NOW()),

(6, '2000', 'Liabilities', 'Liability', NULL, NOW(), NOW()),
(7, '2100', 'Current Liabilities', 'Liability', 6, NOW(), NOW()),
(8, '2110', 'VAT Payable', 'Liability', 7, NOW(), NOW()),
(9, '2120', 'Accounts Payable', 'Liability', 7, NOW(), NOW()),

(10, '3000', 'Equity', 'Equity', NULL, NOW(), NOW()),
(11, '3100', 'Retained Earnings', 'Equity', 10, NOW(), NOW()),

(12, '4000', 'Revenue', 'Revenue', NULL, NOW(), NOW()),
(13, '4100', 'Sales Revenue', 'Revenue', 12, NOW(), NOW()),

(14, '5000', 'Expenses', 'Expense', NULL, NOW(), NOW()),
(15, '5100', 'Cost of Goods Sold', 'Expense', 14, NOW(), NOW()),
(16, '5200', 'Discount Given', 'Expense', 14, NOW(), NOW());

-- Insert Sample Product (from business scenario)
INSERT INTO `products` (`id`, `name`, `sku`, `purchase_price`, `sell_price`, `opening_stock`, `current_stock`, `created_at`, `updated_at`) VALUES
(1, 'Sample Product', 'PRD001', 100.00, 200.00, 50, 50, NOW(), NOW());

-- Record opening stock movement
INSERT INTO `stock_movements` (`product_id`, `type`, `quantity`, `date`, `reference`, `created_at`, `updated_at`) VALUES
(1, 'Opening', 50, CURDATE(), 'Opening Stock', NOW(), NOW());

-- Create opening stock journal entry
INSERT INTO `journal_entries` (`id`, `date`, `description`, `reference`, `created_at`, `updated_at`) VALUES
(1, CURDATE(), 'Opening Stock - Sample Product', 'OPENING-001', NOW(), NOW());

-- Journal lines for opening stock (DR Inventory, CR Equity)
INSERT INTO `journal_lines` (`journal_entry_id`, `account_id`, `debit`, `credit`, `created_at`, `updated_at`) VALUES
(1, 5, 5000.00, 0.00, NOW(), NOW()),  -- DR Inventory (50 units @ 100 TK = 5000)
(1, 11, 0.00, 5000.00, NOW(), NOW()); -- CR Retained Earnings
