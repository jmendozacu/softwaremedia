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
		$queueItems = Mage::getModel('smtppro/queue')->getCollection()->setOrder('id','ASC')->addFieldToFilter('status', array('neq' => 'resend_failed'))->addFieldToFilter('datetime', array('lt' => date('Y-m-d h:i:s', time() - 60 * 60 * 1)));

		$queueItems->getSelect()->limit(20);
		
		foreach($queueItems as $queueItem) {
			if (!$queueItem->getParams())
				continue;
			//Mage::log('Resending queue item ' . $queueItem->getId(),NULL,'queue.log');
			
			//Check if e-mail recipient has additional e-mails associated with it
			$params = json_decode($queueItem->getParams(), true);
			$mail = unserialize($params['mail_object']);
			
			$recip = $mail->getRecipients();
			$customer = Mage::getModel('customer/customer')->loadByEmail($recip[0]);
			if ($customer->getData('additional_email')) {
				foreach(explode(',',$customer->getData('additional_email')) as $toCC) {
					//Add all additional e-mails to CC
					$mail->addCc(trim($toCC));
				}
			}
			$mail = serialize($mail);
			if (Mage::helper('smtppro/mail')->sendMailObject($mail, $params['website_model_id'], $params['transport'])) {
				$queueItem->delete();
			} else {
				$queueItem->setStatus('resend_failed')->save();
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
