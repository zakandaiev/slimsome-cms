SET NAMES UTF8;

CREATE TABLE IF NOT EXISTS `%prefix%_settings` ( 
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL,
	`value` TEXT DEFAULT NULL,
	`json` JSON DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE `name` (name)
) ENGINE = MyISAM;

INSERT INTO `%prefix%_settings` (`name`, `value`, `json`) VALUES
('db_host', '%db_host%', null),
('db_user', '%db_user%', null),
('db_pass', '%db_pass%', null),
('db_name', '%db_name%', null),
('db_prefix', '%prefix%', null),
('t_zone', '%t_zone%', null),
('cron_pass', '%cron_pass%', null),
('site_name', '%site_name%', null),
('site_url', '%site_url%', null),
('site_email', '%site_email%', null),
('site_logo', '%site_logo%', null),
('site_background', null, null),
('site_background_styles', null, null),
('site_color_accent', '#313946', null),
('site_color_accent_2', '#fe3f99', null),
('site_color_body', '#faf9fa', null),
('site_color_text', '#000', null),
('site_description', 'Сайт игрового сервера по игре CS 1.6', null),
('site_keywords', 'сайт сервера, сервер кс, кс 1.6, cs 1.6, counter-strike 1.6', null),
('site_analytics_gtag', null, null),
('site_chat_enabled', 'true', null),
('site_chat_enabled_for_unregistereds', 'true', null),
('server_ip', '%server_ip%', null),
('ftp_host', '%ftp_host%', null),
('ftp_login', '%ftp_login%', null),
('ftp_pass', '%ftp_pass%', null),
('ftp_users_path', '%ftp_users_path%', null),
('ftp_bans_path', '%ftp_bans_path%', null),
('ftp_stats_path', '%ftp_stats_path%', null),
('payments', null, '%payments%'),
('socials', null, '[{"icon": "vk", "url": "https://vk.com/szawesome", "blank": true},{"icon": "dev-cs", "url": "https://dev-cs.ru/members/7458/", "blank": true},{"icon": "email", "url": "mailto:szawesome95@gmail.com", "blank": true}]');

CREATE TABLE IF NOT EXISTS `%prefix%_users` ( 
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`login` VARCHAR(32) NOT NULL,
	`password` VARCHAR(32) NOT NULL,
	`email` VARCHAR(128) DEFAULT NULL,
	`nick` VARCHAR(64) DEFAULT NULL,
	`steam_id` VARCHAR(32) DEFAULT NULL,
	`name` VARCHAR(32) DEFAULT NULL,
	`service_id` INT(11) DEFAULT NULL,
	`service_start` TIMESTAMP NULL DEFAULT NULL,
	`service_end` TIMESTAMP NULL DEFAULT NULL,
	`service_nolimit` BOOLEAN NOT NULL DEFAULT FALSE,
	`service_bind_type` INT(1) DEFAULT NULL,
	`isadmin` BOOLEAN NOT NULL DEFAULT FALSE,
	`ismoder` BOOLEAN NOT NULL DEFAULT FALSE,
	`ip` VARCHAR(32) DEFAULT NULL,
	`last_sign` TIMESTAMP NULL DEFAULT NULL,
	`cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`),
	UNIQUE `login` (`login`),
	UNIQUE `email` (`email`),
	UNIQUE `nick` (`nick`),
	UNIQUE `steam_id` (`steam_id`)
) ENGINE = MyISAM;

INSERT INTO `%prefix%_users` (`login`, `password`, `email`, `name`, `isadmin`, `ismoder`) VALUES
('%adm_login%', '%adm_pass%', '%adm_email%', '%adm_name%', true, true);

CREATE TABLE IF NOT EXISTS `%prefix%_services` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `flags` VARCHAR(32) NOT NULL,
  `days` JSON NOT NULL COMMENT 'days,price_ua,price_rub',
  `images` JSON DEFAULT NULL,
  `user_avatar` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `buyable` BOOLEAN NOT NULL DEFAULT TRUE,
  `enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`id`),
  UNIQUE `name` (`name`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_chat` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `message` TEXT NOT NULL,
  `refference` INT(11) DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `user_data` JSON DEFAULT NULL,
  `service_id` INT(11) NOT NULL,
  `service_name` VARCHAR(128) NOT NULL,
  `days` INT(11) NOT NULL,
  `price` INT(11) NOT NULL,
  `currency` INT(1) NOT NULL,
  `prolong` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` INT(1) NOT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_pages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `url` VARCHAR(64) NOT NULL,
  `content` TEXT DEFAULT NULL,
  `template` VARCHAR(64) DEFAULT NULL,
  `enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `page_order` INT(11) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE `name` (`name`),
  UNIQUE `url` (`url`),
  UNIQUE `page_order` (`page_order`)
) ENGINE=MyISAM;

INSERT INTO `%prefix%_pages` (`name`, `url`, `template`, `page_order`) VALUES
('Информация', 'info', 'info', 1),
('Правила', 'rules', 'rules', 2);

CREATE TABLE IF NOT EXISTS `%prefix%_bans` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(128) NOT NULL,
  `ip` VARCHAR(32) NOT NULL,
  `steam_id` VARCHAR(32) NOT NULL,
  `reason` VARCHAR(128) NOT NULL,
  `created` INT(11) DEFAULT NULL,
  `length` INT(11) DEFAULT NULL,
  `admin_nick` VARCHAR(128) NOT NULL,
  `unbanned` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_news` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `author` INT(11) NOT NULL,
  `title` VARCHAR(64) NOT NULL,
  `url` VARCHAR(128) NOT NULL,
  `body` TEXT DEFAULT NULL,
  `image` TEXT DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `meta_keywords` TEXT DEFAULT NULL,
  `enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE `url` (`url`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_comments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` INT(11) NOT NULL,
  `author` INT(11) NOT NULL,
  `comment` TEXT NOT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_stats` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(128) NOT NULL,
  `uniq` VARCHAR(32) NOT NULL,
  `teamkill` INT(11) DEFAULT NULL,
  `damage` INT(11) DEFAULT NULL,
  `deaths` INT(11) DEFAULT NULL,
  `kills` INT(11) DEFAULT NULL,
  `shots` INT(11) DEFAULT NULL,
  `hits` INT(11) DEFAULT NULL,
  `headshots` INT(11) DEFAULT NULL,
  `defusions` INT(11) DEFAULT NULL,
  `defused` INT(11) DEFAULT NULL,
  `plants` INT(11) DEFAULT NULL,
  `explosions` INT(11) DEFAULT NULL,
  `head` INT(11) DEFAULT NULL,
  `chest` INT(11) DEFAULT NULL,
  `stomach` INT(11) DEFAULT NULL,
  `leftarm` INT(11) DEFAULT NULL,
  `rightarm` INT(11) DEFAULT NULL,
  `leftleg` INT(11) DEFAULT NULL,
  `rightleg` INT(11) DEFAULT NULL,
  `rank` INT(11) DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;