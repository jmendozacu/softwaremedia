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
 * Campaign default helper
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * convert array to options
     *
     * @access public
     * @param $options
     * @return array
     * @author Ultimate Module Creator
     */
    public function convertOptions($options)
    {
        $converted = array();
        foreach ($options as $option) {
            if (isset($option['value']) && !is_array($option['value']) &&
                isset($option['label']) && !is_array($option['label'])) {
                $converted[$option['value']] = $option['label'];
            }
        }
        return $converted;
    }
    
    public function getStepOptions() {
    	$campaigns = array();
    	$collection = Mage::getModel('softwaremedia_campaign/step')->getCollection();
    	foreach ($collection as $campaign) {
	    	$campaigns[$campaign->getId()] = $campaign->getName();
    	}
    	
    	return $campaigns;
    }
    
    public function getCampaignOptions() {
    	$campaigns = array();
    	$collection = Mage::getModel('softwaremedia_campaign/campaign')->getCollection();
    	foreach ($collection as $campaign) {
	    	$campaigns[$campaign->getId()] = $campaign->getName();
    	}
    	
    	return $campaigns;
    }
}
