<?php

class Aschroder_SMTPPro_Helper_Mail {

	public function sendMailObject($mailObject, $websiteModelId = 0, $transport = null) {
		$mail = array();
		try {
			if (empty($transport)) {
				$transport = Mage::helper('smtppro')->getTransport($websiteModelId);
			} else {
				$transport = unserialize($transport);
			}

			$mail = unserialize($mailObject);
			$mail->send($transport);

			return true;
		} catch (Exception $e) {
			try {
				Mage::log('Error: ' . $e->getMessage(),NULL,'email.log');
				Mage::logException($e);
				Mage::log('About to resend email',NULL,'email.log');

				$helper = Mage::helper('smtppro');
				$transportNoOffice = $helper->getTransportNoOffice($websiteModelId);
				$configsNoOffice = $helper->getConfigs();

				//Clone mail to use default sender if sending w/ user Office 365 fails
				$cloneMail = clone $mail;

				if (!empty($configsNoOffice)) {
					$cloneMail->clearFrom();
					$cloneMail->setFrom($configsNoOffice['username'], 'Customer Service');
				}

				Mage::log('From: ' . $cloneMail->getFrom(),NULL,'email.log');

				$cloneMail->send($transportNoOffice); // Zend_Mail warning..
				Mage::log('Finished resending email',NULL,'email.log');
				//Mage::logException($er);
			} catch (Exception $er) {
				Mage::log('Could not send e-mail: ' . $er->getMessage(),NULL,'email.log');
				Mage::logException($er);
				return false;
			}
			return false;
		}
	}

}
