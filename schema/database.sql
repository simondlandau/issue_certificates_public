-- company.registration definition

CREATE TABLE `registration` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `principal_name` varchar(250) NOT NULL,
  `email` varchar(250) NOT NULL,
  `broker_name` varchar(250) NOT NULL,
  `address` varchar(205) NOT NULL,
  `building_name` varchar(250) DEFAULT NULL,
  `street_name` varchar(250) DEFAULT NULL,
  `town` varchar(250) DEFAULT NULL,
  `country` varchar(250) DEFAULT NULL,
  `postcode` varchar(250) DEFAULT NULL,
  `contact_number` varchar(250) NOT NULL,
  `identification` longblob DEFAULT NULL,
  `accounts` longblob DEFAULT NULL,
  `financials` longblob DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `permit_type` varchar(100) DEFAULT NULL,
  `invoiced` timestamp NULL DEFAULT NULL,
  `payment_received` timestamp NULL DEFAULT NULL,
  `permit_created` timestamp NULL DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL DEFAULT 200.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `broker_name` (`broker_name`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- company.set_company definition

CREATE TABLE `set_company` (
  `id` int(10) NOT NULL DEFAULT 0,
  `cost` decimal(10,0) NOT NULL DEFAULT 200,
  `update_by` varchar(250) DEFAULT NULL,
  `update_when` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- samibla.transactions definition

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `registration_id` int(11) NOT NULL,
  `provider` enum('stripe','paypal') NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `registration_id` (`registration_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`registration_id`) REFERENCES `registration` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
