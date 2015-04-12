<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
* @copyright  Copyright (c) 2011 AITOC, Inc. 
*/

$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `aitoc_custom_attribute_cg` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST
");

$installer->endSetup();