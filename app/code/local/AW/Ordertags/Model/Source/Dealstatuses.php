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

class AW_Ordertags_Model_Source_Dealstatuses
{
    public function toOptionArray()
    {
        $statuses = $this->_getStatuses();
        $optionsArray = array();
        foreach ($statuses as $status) {
            $optionsArray[$status] = Mage::helper('ordertags')->__(ucwords($status));
        }
        return $optionsArray;
    }

    private function _getStatuses()
    {
        return array('pending', 'successed', 'failed');
    }

    public function toMultiOptions()
    {
        $statuses = $this->_getStatuses();
        $optionsArray = array();

        $i = 0;
        foreach ($statuses as $status) {
            $optionsArray[$i]['value'] = $status;
            $optionsArray[$i]['label'] = Mage::helper('ordertags')->__(ucwords($status));
            $i++;
        }
        return $optionsArray;
    }
}