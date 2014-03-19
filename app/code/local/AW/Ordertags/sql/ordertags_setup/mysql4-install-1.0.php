<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

try {
    $installer->run("
        DROP TABLE IF EXISTS {$this->getTable('ordertags/managetags')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('ordertags/managetags')} (
            `tag_id` int(11) unsigned NOT NULL auto_increment,
            `name` varchar(255) NOT NULL,
            `filename` varchar(255) NOT NULL,
            `sort_order` int(11) unsigned NOT NULL,
            `conditions_serialized` mediumtext NOT NULL,
            PRIMARY KEY  (`tag_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        DROP TABLE IF EXISTS {$this->getTable('ordertags/ordertotag')};
        CREATE TABLE IF NOT EXISTS {$this->getTable('ordertags/ordertotag')} (
            `tag_id` int(11) unsigned NOT NULL,
            `order_id` int(11) unsigned NOT NULL,
            PRIMARY KEY (`tag_id` , `order_id`),
            FOREIGN KEY (`tag_id`)
                REFERENCES {$this->getTable('ordertags/managetags')} (`tag_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
} catch (Exception $e) {
    Mage::logException($e);
}

try {
    $installer->run("
        INSERT INTO {$this->getTable('ordertags/managetags')} (`name`, filename, sort_order, conditions_serialized)
        VALUES ('Immediate', 'aw_ordertag/red.png', 1, 's:4:\"none\";'),
               ('Urgent', 'aw_ordertag/magenta.png', 2, 's:4:\"none\";'),
               ('High Priority', 'aw_ordertag/black.png', 3, 's:4:\"none\";'),
               ('Medium Priority', 'aw_ordertag/blue.png', 4, 's:4:\"none\";'),
               ('Normal', 'aw_ordertag/green.png', 5, 's:4:\"none\";');

    ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();