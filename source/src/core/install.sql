SET NAMES UTF8;

CREATE TABLE IF NOT EXISTS `%prefix%_settings` ( 
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `value` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `name` (name)
) ENGINE = MyISAM;

INSERT INTO `%prefix%_settings` (`name`, `value`) VALUES
('db_host', '%db_host%'),
('db_user', '%db_user%'),
('db_pass', '%db_pass%'),
('db_name', '%db_name%'),
('db_prefix', '%prefix%'),
('t_zone', '%t_zone%'),
('cron_pass', '%cron_pass%'),
('site_name', '%site_name%'),
('site_url', '%site_url%'),
('site_email', '%site_email%'),
('site_logo', '%site_logo%'),
('site_background', null),
('site_background_styles', null),
('site_color_accent', '#313946'),
('site_color_accent_2', '#fe3f99'),
('site_color_body', '#faf9fa'),
('site_color_text', '#000'),
('site_description', 'Сайт игрового сервера по игре CS 1.6'),
('site_keywords', 'сайт сервера, сервер кс, кс 1.6, cs 1.6, counter-strike 1.6'),
('site_analytics_gtag', null),
('site_chat_enabled', 'true'),
('site_chat_enabled_for_unregistereds', 'true'),
('server_ip', '%server_ip%'),
('ftp_host', '%ftp_host%'),
('ftp_login', '%ftp_login%'),
('ftp_pass', '%ftp_pass%'),
('ftp_users_path', '%ftp_users_path%'),
('ftp_bans_path', '%ftp_bans_path%'),
('ftp_stats_path', '%ftp_stats_path%'),
('payments', '%payments%'),
('socials', '[{"icon": "vk.svg", "url": "https://vk.com/szawesome", "blank": true},{"icon": "dev-cs.svg", "url": "https://dev-cs.ru/members/7458/", "blank": true},{"icon": "email.svg", "url": "mailto:szawesome95@gmail.com", "blank": true}]');

CREATE TABLE IF NOT EXISTS `%prefix%_users` ( 
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(32) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `email` VARCHAR(128) DEFAULT NULL,
  `nick` VARCHAR(64) DEFAULT NULL,
  `steam_id` VARCHAR(32) DEFAULT NULL,
  `name` VARCHAR(32) DEFAULT NULL,
  `service_id` INT DEFAULT NULL,
  `service_start` TIMESTAMP NULL DEFAULT NULL,
  `service_end` TIMESTAMP NULL DEFAULT NULL,
  `service_nolimit` BOOLEAN NOT NULL DEFAULT FALSE,
  `service_bind_type` INT DEFAULT NULL,
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
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `refference` INT DEFAULT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `user_id` INT DEFAULT NULL,
  `user_data` JSON DEFAULT NULL,
  `service_id` INT NOT NULL,
  `service_name` VARCHAR(128) NOT NULL,
  `days` INT NOT NULL,
  `price` INT NOT NULL,
  `currency` INT NOT NULL,
  `prolong` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` INT NOT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_pages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL,
  `url` VARCHAR(64) NOT NULL,
  `content` TEXT DEFAULT NULL,
  `template` VARCHAR(64) DEFAULT NULL,
  `enabled` BOOLEAN NOT NULL DEFAULT TRUE,
  `page_order` INT UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE `name` (`name`),
  UNIQUE `url` (`url`),
  UNIQUE `page_order` (`page_order`)
) ENGINE=MyISAM;

INSERT INTO `%prefix%_pages` (`name`, `url`, `template`, `page_order`) VALUES
('Информация', 'info', 'info', 1),
('Правила', 'rules', 'rules', 2);

CREATE TABLE IF NOT EXISTS `%prefix%_bans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(128) NOT NULL,
  `ip` VARCHAR(32) NOT NULL,
  `steam_id` VARCHAR(32) NOT NULL,
  `reason` VARCHAR(128) NOT NULL,
  `created` INT DEFAULT NULL,
  `length` INT DEFAULT NULL,
  `admin_nick` VARCHAR(128) NOT NULL,
  `unbanned` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `author` INT NOT NULL,
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
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` INT NOT NULL,
  `author` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `cdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `%prefix%_stats` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nick` VARCHAR(128) NOT NULL,
  `uniq` VARCHAR(32) NOT NULL,
  `teamkill` INT DEFAULT NULL,
  `damage` INT DEFAULT NULL,
  `deaths` INT DEFAULT NULL,
  `kills` INT DEFAULT NULL,
  `shots` INT DEFAULT NULL,
  `hits` INT DEFAULT NULL,
  `headshots` INT DEFAULT NULL,
  `defusions` INT DEFAULT NULL,
  `defused` INT DEFAULT NULL,
  `plants` INT DEFAULT NULL,
  `explosions` INT DEFAULT NULL,
  `head` INT DEFAULT NULL,
  `chest` INT DEFAULT NULL,
  `stomach` INT DEFAULT NULL,
  `leftarm` INT DEFAULT NULL,
  `rightarm` INT DEFAULT NULL,
  `leftleg` INT DEFAULT NULL,
  `rightleg` INT DEFAULT NULL,
  `rank` INT DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;