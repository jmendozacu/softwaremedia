<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE `ocm_fulfillment_synnex`;

CREATE TABLE `ocm_fulfillment_synnex` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `synnex_sku` varchar(255) NOT NULL DEFAULT '',
  `qty_on_hand_total` varchar(255) NOT NULL DEFAULT '',
  `unit_cost` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$installer->endSetup();
