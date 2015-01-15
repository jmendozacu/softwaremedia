<?php
/**
 * SoftwareMedia_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Campaign
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Step admin edit tabs
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Step_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('step_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('softwaremedia_campaign')->__('Step'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_step',
            array(
                'label'   => Mage::helper('softwaremedia_campaign')->__('Info'),
                'title'   => Mage::helper('softwaremedia_campaign')->__('Info'),
                'content' => $this->getLayout()->createBlock(
                    'softwaremedia_campaign/adminhtml_step_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve step entity
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Step
     * @author Ultimate Module Creator
     */
    public function getStep()
    {
        return Mage::registry('current_step');
    }
}
