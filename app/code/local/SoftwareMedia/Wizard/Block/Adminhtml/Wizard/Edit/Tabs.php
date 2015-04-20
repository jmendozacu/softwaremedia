<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Wizard admin edit tabs
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('wizard_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('softwaremedia_wizard')->__('Wizard'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_wizard',
            array(
                'label'   => Mage::helper('softwaremedia_wizard')->__('Wizard'),
                'title'   => Mage::helper('softwaremedia_wizard')->__('Wizard'),
                'content' => $this->getLayout()->createBlock(
                    'softwaremedia_wizard/adminhtml_wizard_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve wizard entity
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Wizard
     * @author Ultimate Module Creator
     */
    public function getWizard()
    {
        return Mage::registry('current_wizard');
    }
}
