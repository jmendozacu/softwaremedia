<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    2.0.0
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
class Magecon_CustomerNotes_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_ENABLED = 'customernotes/settings/enabled';

	public function getOptions() {
		
		return array('N/A','Phone - Hung Up','Phone - No Answer','Phone - Voicemail', 'Phone - Talked', 'E-Mail');
	}
	
	public function getCampaigns() {
		$collection = Mage::getModel('softwaremedia_campaign/campaign')->getCollection()->addFieldToFilter('status',1);
		
		
		return $collection;
	}
	
	public function getJSONSteps() {
		$campaigns = $this->getCampaigns();
		
		$stepList = array();
		foreach($campaigns as $campaign) {
			$stepList[$campaign->getId()] = array();
			$steps = Mage::getModel('softwaremedia_campaign/step')->getCollection()->addFieldToFilter('campaign_id',$campaign->getId())->addFieldToFilter('status',1);
			$steps->getSelect()->order('sort','ASC');	
			foreach($steps as $step) {
				$stepList[$campaign->getId()][]	= array('id' => $step->getId(), 'name' => $step->getName());
			}
			
		}
		
		return json_encode($stepList);
		
	}
    public function isEnabled() {
        return Mage::getStoreConfig(self::XML_PATH_ENABLED);
    }

}