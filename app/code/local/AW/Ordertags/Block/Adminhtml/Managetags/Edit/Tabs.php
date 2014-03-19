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

class AW_Ordertags_Block_Adminhtml_Managetags_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_id');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ordertags')->__('Manage Tags'));
    }

    protected function _beforeToHtml()
    {
        $mainTabBlock = $this->getLayout()->createBlock('ordertags/adminhtml_managetags_edit_tab_main');
        $this->addTab(
            'main_section',
            array(
                 'label'   => Mage::helper('ordertags')->__('Tag Information'),
                 'title'   => Mage::helper('ordertags')->__('Tag Information'),
                 'content' => $mainTabBlock->toHtml(),
                 'active'  => true,
            )
        );

        $conditionsTabBlock = $this->getLayout()->createBlock('ordertags/adminhtml_managetags_edit_tab_conditions');
        $this->addTab(
            'conditions_section',
            array(
                 'label'   => Mage::helper('ordertags')->__('Conditions'),
                 'title'   => Mage::helper('ordertags')->__('Conditions'),
                 'content' => $conditionsTabBlock->toHtml(),
                 'active'  => false,
            )
        );
        return parent::_beforeToHtml();
    }
}