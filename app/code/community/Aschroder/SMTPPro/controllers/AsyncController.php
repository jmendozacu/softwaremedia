<?php

class Aschroder_SMTPPro_AsyncController extends Mage_Core_Controller_Front_Action {

	public function mailAction() {
		sleep(2);
		Mage::log('MAIL ACTION',NULL,'email.log');
		$queueItemId = $this->getRequest()->getParam('queue_item_id');
		Mage::log('MAIL ACTION ID ' . $queueItemId,NULL,'email.log');
		$queueItem = Mage::getModel('smtppro/queue')->load($queueItemId);
		$params = json_decode($queueItem->getParams(), true);
		
		Mage::log('Mail Obj Id' . $queueItem->getId(),NULL,'email.log');
		Mage::log('Mail Obj Params' . $queueItem->getParams(),NULL,'email.log');
		Mage::log('Mail Object' . $params['mail_object'],NULL,'email.log');
		Mage::log('MAIL Step 2' . $queueItemId,NULL,'email.log');
		if (Mage::helper('smtppro/mail')->sendMailObject($params['mail_object'], $params['website_model_id'], $params['transport'])) {
			$queueItem->delete();
			Mage::log('MAIL Step 3' . $queueItemId,NULL,'email.log');
		} else {
			$queueItem->setStatus('failed')->save();
			Mage::log('MAIL Step 4' . $queueItemId,NULL,'email.log');
		}
		Mage::log('MAIL Step 5' . $queueItemId,NULL,'email.log');
		Mage::log('MAIL ID ' . $queueItemId,NULL,'email.log');
	}

}
