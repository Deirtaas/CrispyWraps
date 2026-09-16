SET NAMES utf8mb4;

-- --------------------------------------------------------
-- users: staff/admin accounts
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Staff',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- customers
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` varchar(32) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `desc` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category`),
  KEY `idx_products_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- ingredients
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ingredients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'g',
  `stock` int(11) NOT NULL DEFAULT 0,
  `reorder` int(11) NOT NULL DEFAULT 100,
  `expiry` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ingredients_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- recipes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recipes` (
  `product_id` varchar(32) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`,`ingredient_id`),
  KEY `idx_recipes_ingredient` (`ingredient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- cart
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(32) NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_cart_customer_product` (`customer_id`,`product_id`),
  KEY `idx_cart_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` varchar(32) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Dine-in',
  `payment` varchar(50) NOT NULL DEFAULT 'Cash',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'New',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_date` (`date`),
  KEY `idx_orders_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- order_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(32) NOT NULL,
  `product_id` varchar(32) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order` (`order_id`),
  KEY `idx_order_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- stock_log
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(3) NOT NULL,
  `ingredient_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock_log_ingredient` (`ingredient_id`),
  KEY `idx_stock_log_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- waste
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `waste` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ingredient_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `reason` varchar(100) DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_waste_ingredient` (`ingredient_id`),
  KEY `idx_waste_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- feedback
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `feedback` (
  `id` varchar(32) NOT NULL,
  `customer` varchar(100) NOT NULL,
  `product_id` varchar(32) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `comment` text NOT NULL,
  `reply` text DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_feedback_product` (`product_id`),
  KEY `idx_feedback_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Optional seed data
-- --------------------------------------------------------
INSERT IGNORE INTO `categories` (`name`) VALUES
('Wraps'),
('Rice Meals'),
('Sides'),
('Drinks');

INSERT IGNORE INTO `users` (`id`,`name`,`email`,`password`,`role`) VALUES
('usr-admin','Administrator','admin@crispywraps.local','admin123','Admin');

-- Optional sample ingredients
INSERT IGNORE INTO `ingredients` (`id`,`name`,`unit`,`stock`,`reorder`,`expiry`) VALUES
(1,'Chicken','g',1000,200,DATE_ADD(CURDATE(), INTERVAL 14 DAY)),
(2,'Rice','g',2000,500,DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(3,'Tortilla','pcs',200,50,DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
(4,'Lettuce','g',500,100,DATE_ADD(CURDATE(), INTERVAL 5 DAY));
