<?php
/**
 * Observer that logs emails after they have been sent
 *
 * @author Ashley Schroder (aschroder.com)
 * @copyright  Copyright (c) 2010 Ashley Schroder
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Aschroder_SMTPPro_Model_Observer {
	
	public function resendEmailQueue($observer) {
		$queueItems = Mage::getModel('smtppro/queue')->getCollection()->setOrder('id','ASC')->addFieldToFilter('status','');
		
		$queueItems->getSelect()->limit(10);
		
		foreach($queueItems as $queueItem) {
			Mage::log('Resending queue item ' . $queueItem->getId(),NULL,'queue.log');
			$params = json_decode($queueItem->getParams(), true);
			if (Mage::helper('smtppro/mail')->sendMailObject($params['mail_object'], $params['website_model_id'], $params['transport'])) {
				$queueItem->delete();
				Mage::log('Success',NULL,'queue.log');
			} else {
				$queueItem->setStatus('failed')->save();
				Mage::log('Failed',NULL,'queue.log');
			}
		}	
	}
	
	public function log($observer) {
		
		$event = $observer->getEvent();
		if (Mage::helper('smtppro')->isLogEnabled()) {
			
				Mage::helper('smtppro')->log(
				$event->getTo(),
				$event->getTemplate(),
				$event->getSubject(),
				$event->getEmailBody(),
				$event->getHtml());
		}
		
		// For the self test, if we're sending the contact form notify the self test class
		if($event->getTemplate() == Mage::getStoreConfig("contacts/email/email_template")){
			include_once Mage::getBaseDir() . "/app/code/community/Aschroder/SMTPPro/controllers/IndexController.php";
			Aschroder_SMTPPro_IndexController::$CONTACTFORM_SENT = true;
		}
		
	}
	
}
