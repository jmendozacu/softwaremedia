<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */
/**
 * Adminhtml account controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once 'Mage/Customer/controllers/AccountController.php';

class SoftwareMedia_Account_Adminhtml_System_AccountController extends Mage_Adminhtml_Controller_Action {
	
	public function updateEmailAction() {
		$order_id = Mage::app()->getRequest()->getParam('orderid');
		$email_address =  Mage::app()->getRequest()->getParam('email');
		
		Mage::log($order_id . " - " . $email_address);
		
		$order = Mage::getModel('sales/order')->load($order_id);
		$order->setCustomerEmail($email_address)->save();
	}
	
	public function indexAction() {
		$this->_title($this->__('System'))->_title($this->__('My Account'));

		$this->loadLayout();
		$this->_setActiveMenu('system/account');
		$this->_addContent($this->getLayout()->createBlock('adminhtml/system_account_edit'));
		$this->renderLayout();
	}

	/**
	 * Saving edited user information
	 */
	public function saveAction() {
		$userId = Mage::getSingleton('admin/session')->getUser()->getId();
		$pwd = null;

		$user = Mage::getModel("admin/user")->load($userId);

		$user->setId($userId)
			->setUsername($this->getRequest()->getParam('username', false))
			->setFirstname($this->getRequest()->getParam('firstname', false))
			->setLastname($this->getRequest()->getParam('lastname', false))
			->setEmail(strtolower($this->getRequest()->getParam('email', false)));
		if ($this->getRequest()->getParam('new_password', false)) {
			$new_pass = $this->getRequest()->getParam('new_password', false);
			$user->setNewPassword($new_pass);
		}

		if ($this->getRequest()->getParam('password_confirmation', false)) {
			$user->setPasswordConfirmation($this->getRequest()->getParam('password_confirmation', false));
		}
		if ($this->getRequest()->getParam('new_office_password', false)) {
			$user->setNewOfficePassword($this->getRequest()->getParam('new_office_password', false));
		}

		if ($this->getRequest()->getParam('office_password_confirmation', false)) {
			$user->setOfficePasswordConfirmation($this->getRequest()->getParam('office_password_confirmation', false));
		}

		$result = $user->validate();
		if (is_array($result)) {
			foreach ($result as $error) {
				Mage::getSingleton('adminhtml/session')->addError($error);
			}
			$this->getResponse()->setRedirect($this->getUrl("*/*/"));
			return;
		}

		try {
			$user->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The account has been saved.'));
		} catch (Mage_Core_Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('An error occurred while saving account.'));
		}
		$this->getResponse()->setRedirect($this->getUrl("*/*/"));
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('system/myaccount');
	}

}
