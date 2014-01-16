<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('ocm_quotedispatch_note')};
CREATE TABLE {$this->getTable('ocm_quotedispatch_note')} (
  `quotedispatch_note_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `quotedispatch_id` int(10) unsigned DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`quotedispatch_note_id`),
  KEY `IDX_OCM_QDP_NOTE_QDP_ID` (`quotedispatch_id`),
  CONSTRAINT `FK_OCM_QDP_NOTE_QSP_ID_OCM_QDP_QDP_ID` FOREIGN KEY (`quotedispatch_id`) REFERENCES `ocm_quotedispatch` (`quotedispatch_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;


    ");

$installer->endSetup(); 