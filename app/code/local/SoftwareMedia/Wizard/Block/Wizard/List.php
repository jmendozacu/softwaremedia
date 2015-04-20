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
 * Wizard list block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Wizard_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $wizards = Mage::getResourceModel('softwaremedia_wizard/wizard_collection')
                         ->addFieldToFilter('status', 1);
        $wizards->setOrder('title', 'asc');
        $this->setWizards($wizards);
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Wizard_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'softwaremedia_wizard.wizard.html.pager'
        )
        ->setCollection($this->getWizards());
        $this->setChild('pager', $pager);
        $this->getWizards()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
