<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE `ocm_lnav_tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `ocm_lnav_obj` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tree_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `related_category_id` int(11) DEFAULT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `o_order` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tree_id` (`tree_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `ocm_lnav_obj_ibfk_1` FOREIGN KEY (`tree_id`) REFERENCES `ocm_lnav_tree` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
SQLTEXT;

$installer->run($sql);
$installer->endSetup();
