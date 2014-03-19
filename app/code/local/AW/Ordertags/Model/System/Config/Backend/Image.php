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


class AW_Ordertags_Model_System_Config_Backend_Image extends Mage_Adminhtml_Model_System_Config_Backend_Image
{
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (
            isset($_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'])
            && $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value']
        ) {
            try {
                Mage::helper('ordertags')->resizeToThumbnail(
                    array('path' => $this->_getAwUploadPath(), 'file' => $this->getValue())
                );
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    protected function _getAwUploadPath()
    {
        return Mage::getBaseDir('media') . '/' . (string)$this->getFieldConfig()->upload_dir . '/';
    }
}