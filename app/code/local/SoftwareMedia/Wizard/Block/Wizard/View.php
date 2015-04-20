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
 * Wizard view block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Wizard_View extends Mage_Core_Block_Template
{
    /**
     * get the current wizard
     *
     * @access public
     * @return mixed (SoftwareMedia_Wizard_Model_Wizard|null)
     * @author Ultimate Module Creator
     */
    public function getCurrentWizard()
    {
        return Mage::registry('current_wizard');
    }
    
    public function getWizardJSON() {
	    $wizard = $this->getCurrentWizard();
	    $questions = array();
	    $rootQuestion = Mage::getModel('softwaremedia_wizard/question')->getCollection();
	    $rootQuestion->addFieldToFilter('wizard_id',$wizard->getId());
	    //$rootQuestion->addFieldToFilter('parent_id',1);
		//$rootQuestion = $rootQuestion->getFirstItem();
		
	    //$questions[] = array('id'=>$rootQuestion->getId(),'title' => $rootQuestion->getTitle());
	    
	    //var_dump($questions);
    }
}
