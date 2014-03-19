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
    $imageFiles = array('black.png', 'blue.png', 'green.png', 'magenta.png', 'red.png');

    $mediaBaseDirOrderTagsDefault = Mage::getBaseDir('media') . DS . 'aw_ordertag' . DS . 'default';
    $mediaBaseDirOrderTags = Mage::getBaseDir('media') . DS . 'aw_ordertag';
    $sourceImageFolder = Mage::getBaseDir('skin')
        . DS . 'adminhtml'
        . DS . 'default'
        . DS . 'default'
        . DS . 'aw_ordertags'
        . DS . 'images'
    ;

    $sourceImageFile = $sourceImageFolder . DS . 'white.png';
    $pathToImageFile = $mediaBaseDirOrderTagsDefault . DS . 'white.png';

    mkdir($mediaBaseDirOrderTagsDefault, 0777, true);
    copy($sourceImageFile, $pathToImageFile);

    foreach ($imageFiles as $imageFile) {
        copy($sourceImageFolder . DS . $imageFile, $mediaBaseDirOrderTags . DS . $imageFile);
    }

} catch (Exception $e) {
    Mage::logException($e);
}

try {
    $stmt = $installer->getConnection()
        ->select()
        ->from($installer->getTable('core_config_data'))
        ->where("path = 'ordertags/configuration/blanktagimage'");

    $result = $installer->getConnection()->fetchAll($stmt);

    if (empty($result)) {
        $installer->setConfigData('ordertags/configuration/blanktagimage', 'default/white.png');
    }
} catch (Exception $e) {
    Mage::logException($e);
}
$installer->endSetup();