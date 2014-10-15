<?php

/**
 * Abcnet_CommentBox
 * www.abcnet.ch
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Abcnet
 * @package    Abcnet_CommentBox
 * @copyright  Copyright (c) 2011 Mogos Radu, radu.mogos@pixelplant.ro, radu.mogos@abcnet.ch
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE `sales_flat_order_status_history` 
ADD COLUMN `admin` VARCHAR(255) NULL DEFAULT NULL AFTER `entity_name`;
");
$installer->endSetup();
