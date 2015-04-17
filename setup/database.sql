SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `deviation_cache` (
  `provider` set('fav.me','sta.sh') NOT NULL DEFAULT 'fav.me',
  `id` varchar(7) NOT NULL,
  `title` tinytext NOT NULL,
  `preview` tinytext NOT NULL,
  `fullsize` tinytext NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `episodes` (
  `season` tinyint(2) unsigned NOT NULL,
  `episode` tinyint(2) unsigned NOT NULL,
  `title` tinytext NOT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `posted_by` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `permissions` (
  `action` varchar(30) NOT NULL,
  `minrole` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `permissions` (`action`, `minrole`) VALUES
('episodes.manage', 'inspector'),
('reservations.create', 'member');

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(11) NOT NULL,
  `type` set('chr','bg','obj') NOT NULL DEFAULT 'chr',
  `season` tinyint(2) unsigned NOT NULL,
  `episode` tinyint(2) unsigned NOT NULL,
  `preview` tinytext NOT NULL,
  `fullsize` tinytext NOT NULL,
  `label` tinytext NOT NULL,
  `requested_by` varchar(36) NOT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reserved_by` varchar(36) DEFAULT NULL,
  `deviation_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int(11) NOT NULL,
  `season` tinyint(2) unsigned NOT NULL,
  `episode` tinyint(2) unsigned NOT NULL,
  `preview` tinytext NOT NULL,
  `fullsize` tinytext NOT NULL,
  `label` tinytext NOT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reserved_by` varchar(36) DEFAULT NULL,
  `deviation_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roles` (
  `value` tinyint(3) unsigned NOT NULL,
  `name` varchar(10) NOT NULL,
  `label` tinytext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`value`, `name`, `label`) VALUES
(0, 'ban', 'Banned User'),
(1, 'user', 'deviantArt User'),
(2, 'member', 'Group Member'),
(3, 'inspector', 'Vector Inspector'),
(4, 'manager', 'Group Manager'),
(5, 'founder', 'Group Founder'),
(255, 'developer', 'Site Developer');

CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(36) NOT NULL,
  `username` tinytext NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `avatar_url` tinytext NOT NULL,
  `access_token` tinytext NOT NULL,
  `refresh_token` tinytext NOT NULL,
  `token_expires` timestamp NULL DEFAULT NULL,
  `signup_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `deviation_cache`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `episodes`
  ADD PRIMARY KEY (`season`,`episode`), ADD KEY `posted_by` (`posted_by`);

ALTER TABLE `permissions`
  ADD PRIMARY KEY (`action`), ADD KEY `minrole` (`minrole`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`), ADD KEY `season` (`season`,`episode`), ADD KEY `reserved_by` (`reserved_by`), ADD KEY `requested_by` (`requested_by`);

ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`), ADD KEY `season` (`season`), ADD KEY `episode` (`episode`), ADD KEY `reservations_ibfk_1` (`season`,`episode`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`value`), ADD KEY `name` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`), ADD KEY `role` (`role`);

ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `episodes`
ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `permissions`
ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`minrole`) REFERENCES `roles` (`name`);

ALTER TABLE `requests`
ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`reserved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`season`, `episode`) REFERENCES `episodes` (`season`, `episode`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `reservations`
ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`season`, `episode`) REFERENCES `episodes` (`season`, `episode`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role`) REFERENCES `roles` (`name`) ON DELETE NO ACTION ON UPDATE CASCADE;