CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `login` varchar(30) NOT NULL,
  `pass` varchar(32) NOT NULL,
  `balance` decimal(17,2) NOT NULL DEFAULT '0.00',
  `inserted` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `login` (`login`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `orders` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(17,2) unsigned NOT NULL DEFAULT '0.00',
  `executor_id` int(10) unsigned NOT NULL DEFAULT '0',
  `completed` int(10) unsigned NOT NULL DEFAULT '0',
  `inserted` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`,`inserted`) USING BTREE,
  KEY `executor_id` (`executor_id`,`completed`),
  KEY `allowed` (`executor_id`,`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` VALUES (1, 0, 'system', '', '0.00', 0);
INSERT INTO `users` VALUES (2, 1, 'admin', '378b854095cfd9633a6bfc02a03a2cad', '0.00', 0);
INSERT INTO `users` VALUES (3, 2, 'customer', '378b854095cfd9633a6bfc02a03a2cad', '0.00', 0);
INSERT INTO `users` VALUES (4, 3, 'executor', '378b854095cfd9633a6bfc02a03a2cad', '0.00', 0);
