<?php

class Aschroder_SMTPPro_AsyncController extends Mage_Core_Controller_Front_Action {

	public function mailAction() {
		Mage::log('MAIL ACTION ',NULL,'email.log');
		$queueItemId = $this->getRequest()->getParam('queue_item_id');
		Mage::log('MAIL ACTION ID ' . $queueItemId,NULL,'email.log');
		$queueItem = Mage::getModel('smtppro/queue')->load($queueItemId);
		$params = json_decode($queueItem->getParams(), true);

		if (Mage::helper('smtppro/mail')->sendMailObject($params['mail_object'], $params['website_model_id'], $params['transport'])) {
			$queueItem->delete();
		} else {
			$queueItem->setStatus('failed')->save();
		}
		
		Mage::log('MAIL ID ' . $queueItemId,NULL,'email.log');
	}

}
