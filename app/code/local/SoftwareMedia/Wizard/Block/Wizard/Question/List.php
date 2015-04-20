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
 * Wizard Questions list block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Wizard_Question_List extends SoftwareMedia_Wizard_Block_Question_List
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
        $wizard = $this->getWizard();
        if ($wizard) {
            $this->getQuestions()->addFieldToFilter('wizard_id', $wizard->getId());
        }
    }

    /**
     * prepare the layout - actually do nothing
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Wizard_Question_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * get the current wizard
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
