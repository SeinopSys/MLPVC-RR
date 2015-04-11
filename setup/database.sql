SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `episodes` (
  `season` tinyint(2) unsigned zerofill NOT NULL,
  `episode` tinyint(2) unsigned zerofill NOT NULL,
  `title` tinytext NOT NULL,
  `posted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(11) NOT NULL,
  `type` enum('chr','bg','obj') NOT NULL DEFAULT 'chr',
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `season` tinyint(2) unsigned zerofill NOT NULL,
  `episode` tinyint(2) unsigned zerofill NOT NULL,
  `image_url` tinytext NOT NULL,
  `label` tinytext NOT NULL,
  `reserved_by` varchar(36) DEFAULT NULL,
  `deviation_id` varchar(7) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

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

ALTER TABLE `episodes`
  ADD PRIMARY KEY (`season`,`episode`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`), ADD KEY `season` (`season`,`episode`), ADD KEY `reserved_by` (`reserved_by`);

ALTER TABLE `roles`
  ADD PRIMARY KEY (`value`), ADD KEY `name` (`name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`), ADD KEY `role` (`role`);


ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;

ALTER TABLE `requests`
ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`season`, `episode`) REFERENCES `episodes` (`season`, `episode`),
ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`reserved_by`) REFERENCES `users` (`id`);

ALTER TABLE `users`
ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role`) REFERENCES `roles` (`name`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
