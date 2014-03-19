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


class AW_Ordertags_Model_Source_Positions
{
    public function toOptionArray()
    {
        $arrayForSelect[] = array('value' => 'none', 'label' => Mage::helper('ordertags')->__('None'));
        $listOfElements = Mage::getModel('ordertags/managetags')->getCollection()->getData();

        if (!Mage::getStoreConfig('ordertags/configuration/defaulttag')) {
            self::setToNone(true);
        }

        if (!empty($listOfElements)) {
            foreach ($listOfElements as $key => $innerArray) {
                $name[$key] = $innerArray['name'];
                $sortOrder[$key] = $innerArray['sort_order'];
            }

            array_multisort($sortOrder, SORT_ASC, $name, $listOfElements);

            foreach ($listOfElements as $element) {
                $arrayForSelect[] = array('value' => $element['tag_id'], 'label' => $element['name']);
            }
        }

        return $arrayForSelect;
    }

    public static function setToNone($_init = false)
    {
        if (!$_init) {
            Mage::app()->getConfig()->saveConfig('ordertags/configuration/defaulttag', 'none');
        } else {
            try {
                $configData = Mage::getModel('core/config_data');
                $configData
                    ->setScope('default')
                    ->setPath('ordertags/configuration/defaulttag')
                    ->setValue('none')
                    ->save()
                ;
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }
}