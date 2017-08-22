DROP TABLE IF EXISTS `formbuilder_forms`;
CREATE TABLE IF NOT EXISTS `formbuilder_forms` (
`id` INT NOT NULL AUTO_INCREMENT,
`name` varchar(255) DEFAULT NULL ,
`date` INT NULL ,
PRIMARY KEY  (`id`),
UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `users_permission_definitions` (`key`)
VALUES ('formbuilder_permission_settings');