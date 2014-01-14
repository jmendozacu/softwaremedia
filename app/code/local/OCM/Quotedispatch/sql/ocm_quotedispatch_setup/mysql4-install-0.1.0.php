<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('ocm_quotedispatch')};

CREATE TABLE {$this->getTable('ocm_quotedispatch')} (
  `quotedispatch_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `email` text NOT NULL,
  `created_time` datetime DEFAULT NULL,
  `expire_time` datetime DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`quotedispatch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('ocm_quotedispatch_item')};
CREATE TABLE {$this->getTable('ocm_quotedispatch_item')} (
  `quotedispatch_item_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `quotedispatch_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `price` decimal(12,4) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  PRIMARY KEY (`quotedispatch_item_id`),
  KEY `IDX_OCM_QDP_ITEM_QDP_ID` (`quotedispatch_id`),
  KEY `IDX_OCM_QDP_ITEM_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_OCM_QDP_ITEM_PRODCUT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_OCM_QDP_ITEM_QSP_ID_OCM_QDP_QDP_ID` FOREIGN KEY (`quotedispatch_id`) REFERENCES `ocm_quotedispatch` (`quotedispatch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;


    ");

$installer->endSetup(); 