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


class AW_Ordertags_Helper_Data extends Mage_Core_Helper_Abstract
{
    const DEFAULT_THUMBNAIL_WIDTH = 30;
    const DEFAULT_THUMBNAIL_HEIGHT = 30;
    const MSS_VERSION_MIN = '2.0';

    public function magentoLess14()
    {
        return version_compare(Mage::getVersion(), '1.4', '<');
    }

    public function extensionEnabled($extensionName, $configPath = null)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (
            !isset($modules[$extensionName])
            || $modules[$extensionName]->descend('active')->asArray() == 'false'
            || Mage::getStoreConfig('advanced/modules_disable_output/' . $extensionName)
            || ($configPath && !Mage::getStoreConfig($configPath))
        ) {
            return false;
        }
        return true;
    }

    public function extensionInstalled($extensionName)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (
            !isset($modules[$extensionName])
            || ($modules[$extensionName]->descend('active')->asArray() == 'false')
        ) {
            return false;
        }
        return true;
    }

    public function resizeToThumbnail(array $data)
    {
        if (!isset($data['path']) || !isset($data['file'])) {
            throw new Exception('Invalid params for resize');
        }

        $img = $data['path'] . $data['file'];

        if (!file_exists($img)) {
            throw new Exception("File {$img} does not exist");
        }

        $image = new Varien_Image($img);
        $image->keepAspectRatio(false);
        $image->keepTransparency(true);
        $image->keepFrame(false);
        $image->quality(100);
        $image->resize(self::DEFAULT_THUMBNAIL_WIDTH, self::DEFAULT_THUMBNAIL_HEIGHT);
        $image->save($data['path'], $data['file']);
    }

    public function extensionVersion($extensionName)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        if (isset($modules[$extensionName])) {
            return (string)$modules[$extensionName]->version;
        }
        return '';
    }

    public function isMssEnabled()
    {
        if (
            $this->extensionEnabled('AW_Marketsuite')
            && (version_compare($this->extensionVersion('AW_Marketsuite'), self::MSS_VERSION_MIN, '>='))
        ) {
            return true;
        }
        return false;
    }

    public function getMssRulesToOptionArray()
    {
        $options = Mage::getModel('marketsuite/api')->getRuleCollection()->toOptionArray();
        array_unshift($options, array('value' => 0, 'label' => $this->__('None')));
        return $options;
    }

    public function validateMss($tagRule, Mage_Sales_Model_Order $order)
    {
        /*
         *  MSS rule is disabled / not exists - return true (skip MSS validation)
         *  MSS rule is active - return MSS validation result
         */
        $mssRuleId = $tagRule->getMssRuleId();
        if ($mssRuleId && $this->isMssEnabled()) {
            return Mage::getModel('marketsuite/api')->checkRule($order, $mssRuleId);
        }
        return false;
    }
}