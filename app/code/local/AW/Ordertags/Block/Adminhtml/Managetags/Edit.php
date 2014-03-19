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

class AW_Ordertags_Block_Adminhtml_Managetags_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'tag_id';
        $this->_blockGroup = 'ordertags';
        $this->_controller = 'adminhtml_managetags';

        $this->_updateButton('save', 'label', Mage::helper('ordertags')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('ordertags')->__('Delete Item'));

        $this->_formScripts[] = "
            Event.observe(window, 'load', function() {
                var delete_image =  document.getElementsByClassName('delete-image');
                for(i=0; i<delete_image.length; i++)
                    delete_image[i].style.display = 'none';
                    if ($('filename_image')==null) {
                        $('filename').addClassName('required-entry');
                    }
            });
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('tag_data')) {
            $tagData = Mage::registry('tag_data');
            if ($tagData->getId() != null) {
                return Mage::helper('ordertags')->__("Edit Tag '%s'", htmlspecialchars($tagData['name']), ENT_QUOTES);
            }
        }
        return Mage::helper('ordertags')->__('Add Tag');
    }
}