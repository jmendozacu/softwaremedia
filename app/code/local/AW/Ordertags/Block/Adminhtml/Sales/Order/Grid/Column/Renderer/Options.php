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

class AW_Ordertags_Block_Adminhtml_Sales_Order_Grid_Column_Renderer_Options
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    protected static $_allTags;

    /**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $listOfCheckedTags = $row->getData('tags');
        $orderId = $row->getData('entity_id');
        $storeId = $row->getData('store_id');
        $incrementId = $row->getData('increment_id');
        $center = "a-center";

        $html = "<div  class = " . $center . ">
                                <a  onClick=\"showAwNotificator( new Array (";

        $html .= $listOfCheckedTags != "" ? "$listOfCheckedTags,0" : "0";

        $html .= "), " . $orderId . ", '" . $incrementId . "'); return false;\"  href = '#' >";

        $html .= $this->_getImagesAsHTML($listOfCheckedTags, $storeId);

        $html .= "</a></div>";
        return $html;
    }

    private function _getImagesAsHTML($listOfCheckedTags, $storeId)
    {
        $mediaBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $a = self::_getAllTags();

        $html = '';
        if ($listOfCheckedTags) {
            $tags = explode(',', $listOfCheckedTags);
            foreach ($tags as $tagId) {
                if (isset($a[$tagId])) {
                    $html .= '<img id="aw_tag_img" src="' . $mediaBaseUrl . $a[$tagId]['filename'] . '" title="'
                        . $a[$tagId]['name'] . '" />';
                }
            }
        } else {
            $skinBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
            $urlToBlankTag = $skinBaseUrl . AW_Ordertags_Helper_Config::URL_TO_WHITE;
            if (Mage::getStoreConfig('ordertags/configuration/blanktagimage', $storeId)) {
                $urlToBlankTag = $mediaBaseUrl . DS . 'aw_ordertag'
                    . DS . Mage::getStoreConfig('ordertags/configuration/blanktagimage', $storeId)
                ;
            }
            $html .= '<img class="aw_tag_img" src="' . $urlToBlankTag . '" title="'
                . Mage::helper('ordertags')->__('Default') . '"/>'
            ;
        }

        return $html;
    }

    protected function _getAllTags()
    {
        if (!self::$_allTags) {
            $collection = Mage::getModel('ordertags/managetags')->getCollection();
            $_tags = array();
            foreach ($collection as $tag) {

                $_tags[$tag->getId()] = $tag->getData();
            }
            self::$_allTags = $_tags;
        }
        return self::$_allTags;
    }
}