<?php

/**
 * This class wraps the Template email sending functionality
 * If SMTP Pro is enabled it will send emails using the given
 * configuration.
 *
 * @author Ashley Schroder (aschroder.com)
 */
class SoftwareMedia_Account_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template {

	public function __construct() {
		parent::__construct();
		$this->setData('error');
		$this->setData('configs');
	}

	/*
	public function send($email, $name = null, array $variables = array()) {

		// If it's not enabled, just return the parent result.
		if (!Mage::helper('smtppro')->isEnabled()) {
			return parent::send($email, $name, $variables);
		}

		Mage::log('SMTPPro is enabled, sending email in SoftwareMedia_Account_Model_Email_Template');


		// The remainder of this function closely mirrors the parent
		// method except for providing the SMTP auth details from the
		// configuration. This is not good OO, but the parent class
		// leaves little room for useful subclassing. This will probably
		// become redundant sooner or later anyway.

		if (!$this->isValidForSend()) {
			Mage::log('SMTPPro: Email not valid for sending - check template, and smtp enabled/disabled setting');
			Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
			return false;
		}

		$emails = array_values((array) $email);
		$names = is_array($name) ? $name : (array) $name;
		$names = array_values($names);
		foreach ($emails as $key => $email) {
			if (!isset($names[$key])) {
				$names[$key] = substr($email, 0, strpos($email, '@'));
			}
		}

		$variables['email'] = reset($emails);
		$variables['name'] = reset($names);




		$mail = $this->getMail();

		$dev = Mage::helper('smtppro')->getDevelopmentMode();
		//Mage::log($variables);
		if ($dev == "contact") {

			$email = Mage::getStoreConfig('contacts/email/recipient_email', $this->getDesignConfig()->getStore());
			Mage::log("Development mode set to send all emails to contact form recipient: " . $email);
		} elseif ($dev == "supress") {

			Mage::log("Development mode set to supress all emails.");
			# we bail out, but report success
			return true;
		}

		// In Magento core they set the Return-Path here, for the sendmail command.
		// we assume our outbound SMTP server (or Gmail) will set that.

		foreach ($emails as $key => $email) {
			$mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
		}


		$this->setUseAbsoluteLinks(true);
		$text = $this->getProcessedTemplate($variables, true);

		if ($this->isPlain()) {
			$mail->setBodyText($text);
		} else {
			$mail->setBodyHTML($text);
		}
		if ($variables['order']) {
			$order = Mage::getModel('sales/order')->load($variables['order']->getIncrementId(), 'increment_id');
			$comment = "E-Mail Sent (<a href='#'>View E-Mail</a>)";
			$comment .= "<div style='display: none;'>";
			$comment .= $text;
			$comment .= "</div>";
			$historyEmail = Mage::getModel('emailhistory/email');
			$historyEmail->setOrderId($order->getId());
			$historyEmail->setText($text);
			$historyEmail->setEmail($email);
			$historyEmail->setEmailName($variables['name']);
			$historyEmail->setSubject($this->getProcessedTemplateSubject($variables));
			$historyEmail->setCreatedAt(now());
			$historyEmail->save();
		}
		try {
			$mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
		} catch (Exception $e) {

		}
		// If we are using store emails as reply-to's set the header
		// Check the header is not already set by the application.
		// The contact form, for example, set's it to the sender of
		// the contact. Thanks i960 for pointing this out.

		if (Mage::helper('smtppro')->isReplyToStoreEmail() && !array_key_exists('Reply-To', $mail->getHeaders())) {

			// Patch for Zend upgrade
			// Later versions of Zend have a method for this, and disallow direct header setting...
			if (method_exists($mail, "setReplyTo")) {
				$mail->setReplyTo($this->getSenderEmail(), $this->getSenderName());
			} else {
				$mail->addHeader('Reply-To', $this->getSenderEmail());
			}
			Mage::log('ReplyToStoreEmail is enabled, just set Reply-To header: ' . $this->getSenderEmail());
		}

		//Clone mail to use default sender if sending w/ user Office 365 fails
		$cloneMail = clone $mail;

		$helper = Mage::helper('smtppro');
		$transport = $helper->getTransport($this->getDesignConfig()->getStore());
		$configs = $helper->getConfigs();
		if (!empty($configs) && $this->getSenderEmail() != $configs['username']) {
			$mail->setFrom($configs['username'], $this->getSenderName());
		} else {
			$mail->setFrom($this->getSenderEmail(), $this->getSenderName());
		}

		$transportNoOffice = $helper->getTransportNoOffice($this->getDesignConfig()->getStore());
		$configsNoOffice = $helper->getConfigs();



		if (!empty($configsNoOffice) && $this->getSenderEmail() != $configsNoOffice['username']) {
			$cloneMail->setFrom($configsNoOffice['username'], $this->getSenderName());
		} else {
			$cloneMail->setFrom($this->getSenderEmail(), $this->getSenderName());
		}
		$this->setData('error', "");
		try {
			$mailObject = serialize($mail);

			Mage::log('About to send email');
			Mage::helper('smtppro')->asyncRequest(Mage::getBaseUrl() . 'smtppro/async/mail/', array('mail_object' => $mailObject, 'website_model_id' => $this->getDesignConfig()->getStore()));
			Mage::log('Finished sending email');

			// Record one email for each receipient
			foreach ($emails as $key => $email) {
				Mage::dispatchEvent('smtppro_email_after_send', array('to' => $email,
					'template' => $this->getTemplateId(),
					'subject' => $this->getProcessedTemplateSubject($variables),
					'html' => !$this->isPlain(),
					'email_body' => $text));
			}

			$this->_mail = null;
		} catch (Exception $e) {
			$this->setData('error', "cs");
			try {
				Mage::log('Error: ' . $e->getMessage());
				Mage::logException($e);
				Mage::log('About to resend email');
				$cloneMail->send($transportNoOffice); // Zend_Mail warning..
				Mage::log('Finished resending email');
				//Mage::logException($er);
			} catch (Exception $er) {
				Mage::log('Error: ' . $er->getMessage());
				Mage::logException($er);
				$this->setData('error', $er->getMessage());
				return false;
			}
			return false;
		}

		return true;
	}
	*/

}
