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
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Admin user model
 *
 * @method Mage_Admin_Model_Resource_User _getResource()
 * @method Mage_Admin_Model_Resource_User getResource()
 * @method string getFirstname()
 * @method Mage_Admin_Model_User setFirstname(string $value)
 * @method string getLastname()
 * @method Mage_Admin_Model_User setLastname(string $value)
 * @method string getEmail()
 * @method Mage_Admin_Model_User setEmail(string $value)
 * @method string getUsername()
 * @method Mage_Admin_Model_User setUsername(string $value)
 * @method string getPassword()
 * @method Mage_Admin_Model_User setPassword(string $value)
 * @method string getCreated()
 * @method Mage_Admin_Model_User setCreated(string $value)
 * @method string getModified()
 * @method Mage_Admin_Model_User setModified(string $value)
 * @method string getLogdate()
 * @method Mage_Admin_Model_User setLogdate(string $value)
 * @method int getLognum()
 * @method Mage_Admin_Model_User setLognum(int $value)
 * @method int getReloadAclFlag()
 * @method Mage_Admin_Model_User setReloadAclFlag(int $value)
 * @method int getIsActive()
 * @method Mage_Admin_Model_User setIsActive(int $value)
 * @method string getExtra()
 * @method Mage_Admin_Model_User setExtra(string $value)
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SoftwareMedia_Account_Model_User extends Mage_Admin_Model_User {

	/**
	 * Processing data before model save
	 *
	 * @return Mage_Admin_Model_User
	 */
	protected function _beforeSave() {
		$data = array(
			'firstname' => $this->getFirstname(),
			'lastname' => $this->getLastname(),
			'email' => $this->getEmail(),
			'modified' => now(),
			'extra' => serialize($this->getExtra())
		);

		if ($this->getId() > 0) {
			$data['user_id'] = $this->getId();
		}

		if ($this->getUsername()) {
			$data['username'] = $this->getUsername();
		}

		if ($this->getNewPassword()) {
			// Change password
			$data['password'] = $this->_getEncodedPassword($this->getNewPassword());
		} elseif ($this->getPassword() && $this->getPassword() != $this->getOrigData('password')) {
			// New user password
			$data['password'] = $this->_getEncodedPassword($this->getPassword());
		}

		// TODO: Encrypt password
		if ($this->getNewOfficePassword()) {
			// Change password
			$data['office_password'] = $this->_getEncryptedPassword($this->getNewOfficePassword());
		} elseif ($this->getOfficePassword() && $this->getOfficePassword() != $this->getOrigData('office_password')) {
			// New user password
			$data['office_password'] = $$this->_getEncryptedPassword($this->getNewOfficePassword());
		}

		if (!is_null($this->getIsActive())) {
			$data['is_active'] = intval($this->getIsActive());
		}

		$this->addData($data);

		return parent::_beforeSave();
	}

	public function _getEncryptedPassword($password) {
		return Mage::helper('core')->encrypt(base64_encode($password));
	}

	public function _getDecryptedPassword($encrypt) {
		return base64_decode(Mage::helper('core')->decrypt($encrypt));
	}

	/**
	 * Validate user attribute values.
	 * Returns TRUE or array of errors.
	 *
	 * @return mixed
	 */
	public function validate() {
		$errors = array();

		if (!Zend_Validate::is($this->getUsername(), 'NotEmpty')) {
			$errors[] = Mage::helper('adminhtml')->__('User Name is required field.');
		}

		if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
			$errors[] = Mage::helper('adminhtml')->__('First Name is required field.');
		}

		if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
			$errors[] = Mage::helper('adminhtml')->__('Last Name is required field.');
		}

		if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
			$errors[] = Mage::helper('adminhtml')->__('Please enter a valid email.');
		}

		if ($this->hasNewPassword()) {
			if (Mage::helper('core/string')->strlen($this->getNewPassword()) < self::MIN_PASSWORD_LENGTH) {
				$errors[] = Mage::helper('adminhtml')->__('Password must be at least of %d characters.', self::MIN_PASSWORD_LENGTH);
			}

			if (!preg_match('/[a-z]/iu', $this->getNewPassword()) || !preg_match('/[0-9]/u', $this->getNewPassword())
			) {
				$errors[] = Mage::helper('adminhtml')->__('Password must include both numeric and alphabetic characters.');
			}

			if ($this->hasPasswordConfirmation() && $this->getNewPassword() != $this->getPasswordConfirmation()) {
				$errors[] = Mage::helper('adminhtml')->__('Password confirmation must be same as password.');
			}
		}

		
		if ($this->hasNewOfficePassword()) {
			/*
			if (Mage::helper('core/string')->strlen($this->getNewOfficePassword()) < self::MIN_PASSWORD_LENGTH) {
				$errors[] = Mage::helper('adminhtml')->__('Office365 Password must be at least of %d characters.', self::MIN_PASSWORD_LENGTH);
			}

			if (!preg_match('/[a-z]/iu', $this->getNewOfficePassword()) || !preg_match('/[0-9]/u', $this->getNewOfficePassword())
			) {
				$errors[] = Mage::helper('adminhtml')->__('Office365 Password must include both numeric and alphabetic characters.');
			}
			*/
			if (!$this->hasOfficePasswordConfirmation() || $this->getNewOfficePassword() != $this->getOfficePasswordConfirmation()) {
				$errors[] = Mage::helper('adminhtml')->__('Office365 Password confirmation must be same as password.');
			}
		}
		
		
		if ($this->userExists()) {
			$errors[] = Mage::helper('adminhtml')->__('A user with the same user name or email aleady exists.');
		}

		if (empty($errors)) {
			return true;
		}
		return $errors;
	}

}
