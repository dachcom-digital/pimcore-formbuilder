CREATE TABLE IF NOT EXISTS `formbuilder_forms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;