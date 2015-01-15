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
class Magecon_CustomerNotes_Model_Notes extends Mage_Core_Model_Abstract {

	public function getOptions() {
		return array('','Phone - Hung Up','Phone - Voicemail', 'Phone - Talked', 'E-Mail');
	}
	
	public function getCampaign() {
		$campaign = Mage::getModel('softwaremedia_campaign/campaign')->load($this->getCampaignId());
		return $campaign;
	}
	
	public function getStep() {
		$step = Mage::getModel('softwaremedia_campaign/step')->load($this->getStepId());
		return $step;
	}
	
    public function _construct() {
        parent::_construct();
        $this->_init('customernotes/notes');
    }

}