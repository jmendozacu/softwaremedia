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
 * Campaign admin edit tabs
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Campaign_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
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
        $this->setId('campaign_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('softwaremedia_campaign')->__('Campaign'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Campaign_Edit_Tabs
     * @author Ultimate Module Creator
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_campaign',
            array(
                'label'   => Mage::helper('softwaremedia_campaign')->__('Campaign'),
                'title'   => Mage::helper('softwaremedia_campaign')->__('Campaign'),
                'content' => $this->getLayout()->createBlock(
                    'softwaremedia_campaign/adminhtml_campaign_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve campaign entity
     *
     * @access public
     * @return SoftwareMedia_Campaign_Model_Campaign
     * @author Ultimate Module Creator
     */
    public function getCampaign()
    {
        return Mage::registry('current_campaign');
    }
}
