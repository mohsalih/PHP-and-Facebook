CREATE TABLE `fb_accounts` (
  `account_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datetimestamp` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_token` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_list_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_list_name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
