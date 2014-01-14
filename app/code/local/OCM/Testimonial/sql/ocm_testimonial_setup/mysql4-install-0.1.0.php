<?php
/**
 * Created by JetBrains PhpStorm.
 * User: NguyenSon
 * Date: 2/25/13
 * Time: 11:23 PM
 * To change this template use File | Settings | File Templates.
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
    CREATE TABLE `{$installer->getTable('testimonial')}` (
    `id` int(11) NOT NULL auto_increment,
    `user_name` varchar(255) NOT NULL ,
    `company` varchar(255) not NULL ,
    `message` text,
    `date_post` datetime default NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();