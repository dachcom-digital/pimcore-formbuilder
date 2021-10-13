CREATE TABLE IF NOT EXISTS `formbuilder_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) DEFAULT NULL,
  `group` varchar(190) DEFAULT NULL,
  `creationDate` datetime NOT NULL,
  `modificationDate` datetime NOT NULL,
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `mailLayout` longtext COMMENT '(DC2Type:object)',
  `configuration` longtext COMMENT '(DC2Type:object)',
  `conditionalLogic` longtext COMMENT '(DC2Type:object)',
  `fields` longtext COMMENT '(DC2Type:form_builder_fields)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_29DA5346999517A` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `formbuilder_output_workflow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_definition` int(11) DEFAULT NULL,
  `name` varchar(190) DEFAULT NULL,
  `success_management` longtext COMMENT '(DC2Type:object)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_form` (`name`,`form_definition`),
  KEY `IDX_BCB7909761F7634C` (`form_definition`),
  CONSTRAINT `FK_BCB7909761F7634C` FOREIGN KEY (`form_definition`) REFERENCES `formbuilder_forms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `formbuilder_output_workflow_channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `output_workflow` int(11) DEFAULT NULL,
  `type` varchar(190) NOT NULL,
  `configuration` longtext COMMENT '(DC2Type:object)',
  PRIMARY KEY (`id`),
  KEY `IDX_CEC462362C75DDDC` (`output_workflow`),
  CONSTRAINT `FK_CEC462362C75DDDC` FOREIGN KEY (`output_workflow`) REFERENCES `formbuilder_output_workflow` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;