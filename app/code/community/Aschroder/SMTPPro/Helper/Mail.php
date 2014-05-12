<?php

class Aschroder_SMTPPro_Helper_Mail {

	public function sendMailObject($mailObject, $websiteModelId = 0) {
		$mail = array();
		try {
			$transport = Mage::helper('smtppro')->getTransport($websiteModelId);
			Mage::log(print_r($transport, true));

			$mail = unserialize($mailObject);
			$mail->send($transport);

			Mage::log('Returning true');
			return true;
		} catch (Exception $e) {
			try {
				Mage::log('Error: ' . $e->getMessage());
				Mage::logException($e);
				Mage::log('About to resend email');

				$helper = Mage::helper('smtppro');
				$transportNoOffice = $helper->getTransportNoOffice($websiteModelId);
				Mage::log('New Transport: ' . print_r($transportNoOffice, true));
				$configsNoOffice = $helper->getConfigs();
				Mage::log('New Configs: ' . print_r($configsNoOffice, true));

				//Clone mail to use default sender if sending w/ user Office 365 fails
				$cloneMail = clone $mail;

				if (!empty($configsNoOffice)) {
					$cloneMail->clearFrom();
					$cloneMail->setFrom($configsNoOffice['username'], 'Customer Service');
				}

				Mage::log('From: ' . $cloneMail->getFrom());

				$cloneMail->send($transportNoOffice); // Zend_Mail warning..
				Mage::log('Finished resending email');
				//Mage::logException($er);
			} catch (Exception $er) {
				Mage::log('Error: ' . $er->getMessage());
				Mage::logException($er);
				return false;
			}
			return false;
		}
	}

}
