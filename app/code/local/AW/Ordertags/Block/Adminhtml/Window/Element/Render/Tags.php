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


class AW_Ordertags_Block_Adminhtml_Window_Element_Render_Tags extends Mage_Adminhtml_Block_Abstract
{
    public function getCloseText()
    {
        $hlpr = Mage::helper('ordertags');
        return $hlpr->__('Cancel');
    }

    public function getTags()
    {
        $arrayOfTags = array();
        $mediaBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $tagsCollection = Mage::getModel('ordertags/managetags')->getCollection()->setOrder('sort_order', 'ASC');

        foreach ($tagsCollection as $tag) {
            $tagFromCollection = $tag->getData();
            $tagForForm['label'] = htmlspecialchars($tagFromCollection['name'], ENT_QUOTES);
            $tagForForm['value'] = $tagFromCollection['tag_id'];
            $tagForForm['imageurl'] = $mediaBaseUrl . $tagFromCollection['filename'];
            $arrayOfTags[] = $tagForForm;
        }
        return $arrayOfTags;
    }
}