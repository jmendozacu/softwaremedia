<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */


/**
 * This class wraps the Template email sending functionality
 * If SMTP Pro is enabled it will send emails using the given
 * configuration.
 *
 * @author Ashley Schroder (aschroder.com)
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_CoreEmailTemplate extends Mage_Core_Model_Email_Template {

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



/**
 * Mage_Core_Model_Email_Template rewrite class
 *
 * @category   Ebizmarts
 * @package    Ebizmarts_Mandrill
 * @author     Ebizmarts Team <info@ebizmarts.com>
 * @license    http://opensource.org/licenses/osl-3.0.php
 */

class Ebizmarts_Mandrill_Model_Email_Template extends Aitoc_Aitcheckoutfields_Model_Rewrite_CoreEmailTemplate {

	protected $_mandrill = null;
    protected $replyTo = null;
	protected $_bcc = array();

	public function getMail() {

		//Check if should use Mandrill Transactional Email Service
        if(FALSE === Mage::helper('mandrill')->useTransactionalService()){
            return parent::getMail();
        }

		if(is_null($this->_mandrill)){
			$this->_mandrill = Mage::helper('mandrill')->api();
			$this->_mandrill->setApiKey(Mage::helper('mandrill')->getApiKey());
		}
		return $this->_mandrill;
	}

	/**
	 * Add BCC emails to list to send.
	 *
	 * @return Ebizmarts_Mandrill_Model_Email_Template
	 */
    public function addBcc($bcc) {
		$helper = Mage::helper('mandrill');
    	if(FALSE === $helper->useTransactionalService()){
            return parent::addBcc($bcc);
        }
        if (is_array($bcc)) {
            foreach ($bcc as $email) {
                $this->_bcc[] = $email;
            }
        }
        elseif ($bcc) {
            $this->_bcc[] = $bcc;
        }
        return $this;

    }

    /**
     * Send mail to recipient
     *
     * @param   array|string       $email        E-mail(s)
     * @param   array|string|null  $name         receiver name(s)
     * @param   array              $variables    template variables
     * @return  boolean
     **/
    public function send($email, $name = null, array $variables = array()) {

        $helper = Mage::helper('mandrill');

		//Check if should use Mandrill Transactional Email Service
        if(FALSE === $helper->useTransactionalService()){
            return parent::send($email, $name, $variables);
        }

        if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
        }

        $emails = array_values((array)$email);

		if(count($this->_bcc) > 0){
//			$bccEmail = $this->_bcc[0];
            $bccEmail = $this->_bcc;
		}else{
			$bccEmail = '';
		}

        $names = is_array($name) ? $name : (array)$name;
        $names = array_values($names);
        foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
                $names[$key] = substr($email, 0, strpos($email, '@'));
            }
        }

        $variables['email'] = reset($emails);
        $variables['name'] = reset($names);

        $mail = $this->getMail();

        $this->setUseAbsoluteLinks(true);
        $text = $this->getProcessedTemplate($variables, true);

        try {

            $message = array (
					        'subject'     => $this->getProcessedTemplateSubject($variables),
					        'from_name'   => $this->getSenderName(),
					        'from_email'  => $this->getSenderEmail(),
					        'to_email'    => $emails,
					        'to_name'     => $names,
					        'bcc_address' => $bccEmail,
					        'headers'	  => array('Reply-To' => $this->replyTo)
				        );

			if($this->isPlain()) {
		 		$message['text'] = $text;
			} else {
				$message['html'] = $text;
			}
            if(isset($variables['tags']) && count($variables['tags'])) {
                $message ['tags'] = $variables['tags'];
            }
            else {
                $templateId = (string)$this->getId();
                $templates = parent::getDefaultTemplates();
                if (isset($templates[$templateId])) {
                	$message ['tags'] =  array(substr($templates[$templateId]['label'], 0, 50));
				} else {
				        if($this->getTemplateCode()){
				        	$message ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
				        } else {
				        	$message ['tags'] = array(substr($templateId, 0, 50));
				        }
				}
            }

            $sent = $mail->sendEmail($message);
            if($mail->errorCode){
				return false;
			}

        }catch (Exception $e) {
            Mage::logException($e);
            return false;
        }

        return true;
    }

    public function setReplyTo($email) {
        if(FALSE === Mage::helper('mandrill')->useTransactionalService()) {
            return parent::setReplyTo($email);
        }

		$this->replyTo = $email;
        return $this;
    }

	public function createAttachment($body,
                                     $mimeType    = Zend_Mime::TYPE_OCTETSTREAM,
                                     $disposition = Zend_Mime::DISPOSITION_ATTACHMENT,
                                     $encoding    = Zend_Mime::ENCODING_BASE64,
                                     $filename    = null)
    {

    }
    public function addAttachment(Zend_Mime_Part $att)
    {

    }

    public function addTo($email, $name = null)
    {
    	if(FALSE === Mage::helper('mandrill')->useTransactionalService()) {
	        array_push($this->_bcc, $email);
	        return $this;
    	}
    }

}



/**
 * This class wraps the Template email sending functionality
 * If SMTP Pro is enabled it will send emails using the given
 * configuration.
 *
 * @author Ashley Schroder (aschroder.com)
 */
class Aschroder_SMTPPro_Model_Email_Template extends Ebizmarts_Mandrill_Model_Email_Template {

	public function send($email, $name = null, array $variables = array()) {

		// If it's not enabled, just return the parent result.
		if (!Mage::helper('smtppro')->isEnabled()) {
			return parent::send($email, $name, $variables);
		}

		//Mage::log('SMTPPro is enabled, sending email in Aschroder_SMTPPro_Model_Email_Template', null, 'emailtest.log');
		// The remainder of this function closely mirrors the parent
		// method except for providing the SMTP auth details from the
		// configuration. This is not good OO, but the parent class
		// leaves little room for useful subclassing. This will probably
		// become redundant sooner or later anyway.

		if (!$this->isValidForSend()) {
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

		$mail = Mage_Core_Model_Email_Template::getMail();

		$dev = Mage::helper('smtppro')->getDevelopmentMode();

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
			$historyEmail->setIsRead(0);
			$historyEmail->save();

			if (!$this->isPlain()) {
				$text .= '<img src="' . Mage::helper('core/url')->getHomeUrl() . '/emailread/index/index/image/' . $historyEmail->getId() . '.gif" />';
			}
		}

		if ($this->isPlain()) {
			$mail->setBodyText($text);
		} else {
			$mail->setBodyHTML($text);
		}

		$mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
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

		$helper = Mage::helper('smtppro');
		$transport = $helper->getTransport($this->getDesignConfig()->getStore());
		$configs = $helper->getConfigs();
		if (!empty($configs) && $this->getSenderEmail() != $configs['username']) {
			$mail->setFrom($configs['username'], $this->getSenderName());
		} else {
			$mail->setFrom($this->getSenderEmail(), $this->getSenderName());
		}

		try {
			$mailObject = serialize($mail);
			$transportObject = serialize($transport);

			Mage::log('About to send email through async',NULL,'email.log');
			Mage::helper('smtppro')->asyncRequest(Mage::getBaseUrl() . 'smtppro/async/mail/', array('mail_object' => $mailObject, 'website_model_id' => $this->getDesignConfig()->getStore(), 'transport' => $transportObject));
			Mage::log('Finished sending email',NULL,'email.log');

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
			Mage::log($e->getMessage(),NULL,'email.log');
			Mage::logException($e);
			return false;
		}

		return true;
	}

}


/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class SoftwareMedia_Account_Model_Email_Template extends Aschroder_SMTPPro_Model_Email_Template
{
    /**
     * Send transactional email to recipient
     *
     * @param   int $templateId
     * @param   string|array $sender sneder informatio, can be declared as part of config path
     * @param   string $email recipient email
     * @param   string $name recipient name
     * @param   array $vars varianles which can be used in template
     * @param   int|null $storeId
     * @return  Mage_Core_Model_Email_Template
     */
    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {	
        if(isset($vars['order']))
        {
            $aCustomAtrrList = $this->_getCustomAttributesList($vars);
            
            $cfm = new Varien_Object;
            foreach($aCustomAtrrList as $attr)
            {   
                if(!isset($attr['attribute_code']) && isset($attr['code']))
                {
                    $attr['attribute_code'] = $attr['code'];
                }
                
                $cfm->setData($attr['attribute_code'], $attr['value']);
                if($attr['value'] && isset($attr['frontend_label']))
                {
                    $cfm->setData($attr['attribute_code'].'_label', $attr['frontend_label']);
                }
            }

            $vars['cfm'] = $cfm;

        }

        return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
    }
    
    protected function _getCustomAttributesList($vars)
	{
		$aCustomAtrrList = array(); 
		
		$request = Mage::app()->getFrontController()->getRequest();
		$oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
		
		$iOrderId = 0;
		
		if ($vars['order'] instanceof Varien_Object)
		{
			$iOrderId = $vars['order']->getId();
		}
		
		if (!$iOrderId)
		{
			$iOrderId = $request->getParam('order_id');
		}
		
		if ($iOrderId) // sent order from admin area 
		{
			$oOrder = Mage::getModel('sales/order')->load($iOrderId);
			$iStoreId = $oOrder->getStoreId();
			
			$aCustomAtrrList = $oAitcheckoutfields->getEmailOrderCustomData($iOrderId, $iStoreId);
		}
		
		if(empty($aCustomAtrrList)) 
		{
			$oOrder = $vars['order'];
			
			if (!$oOrder)
			{
				return false;
			}
			
			$iStoreId = $oOrder->getStoreId();
			$sPathInfo = $request->getPathInfo();
			
			$aCustomAtrrList = array();
			
			$aSessionAttrList = $this->_getSessionAttributeList($sPathInfo);
			
			if( !empty($aSessionAttrList) )
			{
				$oAttribute  = Mage::getModel('eav/entity_attribute');
				foreach($aSessionAttrList as $attributeId => $sValue)
				{
					$oAttribute->load($attributeId);
					$data = $oAttribute->getData();                    
					
					switch ($data['frontend_input'])
					{
						case 'text':
						case 'date': // to check?
						case 'textarea':
							$sValue = $sValue;
						break;
							
						case 'boolean':
							
							if ($sValue == 1)
							{
								$sValue = Mage::helper('catalog')->__('Yes');
							}
							elseif ($sValue) 
							{
								$sValue = '';
							}
							else 
							{
								$sValue = Mage::helper('catalog')->__('No');
							}
							
						break;
							
						case 'select':
						case 'radio':
							
							$aValueList = $oAitcheckoutfields->getAttributeOptionValues($attributeId, $iStoreId, $sValue);
							if ($aValueList)
							{
								$sValue = $aValueList[0];
							}
						break;    
						
						case 'multiselect':
							if(version_compare(Mage::getVersion(), '1.6.0.0', '>='))
							{
								if(is_array($sValue))
								{
									$tempArray = array();
									foreach ($sValue as $val)
									{
										$explodedArr = explode(',', $val);
										
										foreach($explodedArr as $expVal)
										{
											array_push($tempArray, $expVal);
										}
									}
								$aValueList = $oAitcheckoutfields->getAttributeOptionValues($attributeId, $iStoreId, $tempArray);
								}
								else
								{
									$aValueList = $oAitcheckoutfields->getAttributeOptionValues($attributeId, $iStoreId, explode(',', $sValue));
								}
							}
							else
								$aValueList = $oAitcheckoutfields->getAttributeOptionValues($attributeId, $iStoreId, $sValue);
							if ($aValueList)
							{
								$sValue = implode(', ', $aValueList);
							}
						break;
						
						case 'checkbox':
								$aValueList = $oAitcheckoutfields->getAttributeOptionValues($attributeId, $iStoreId, $sValue);
							if ($aValueList)
							{
								$sValue = implode(', ', $aValueList);
							}
						break;                            
					}
					
					$data['value'] = $sValue;
					$aCustomAtrrList[] = $data;
				}
			}
		}
		return $aCustomAtrrList;
    }
	
	protected function _getSessionAttributeList($sPathInfo)
	{
		if (isset($_SESSION['aitoc_checkout_used']['adminorderfields']))
		{
			$sPageType = 'adminorderfields';
		}
		elseif ($sPathInfo AND strpos($sPathInfo, '/multishipping/'))
		{
			$sPageType = 'multishipping';
		}
		else 
		{
			$sPageType = 'onepage';
		}
        
	    $aSessionAttrList = ( isset($_SESSION['aitoc_checkout_used'][$sPageType]) && is_array($_SESSION['aitoc_checkout_used'][$sPageType]) ) ?  $_SESSION['aitoc_checkout_used'][$sPageType] : array();
		
	    return $aSessionAttrList;
	}

}

