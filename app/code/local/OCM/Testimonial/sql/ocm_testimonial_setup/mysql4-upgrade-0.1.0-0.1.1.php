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
    ALTER TABLE `{$installer->getTable('testimonial')}` ADD COLUMN `public` int(11) default 0;
");
$installer->endSetup();