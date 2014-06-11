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
 * Adminhtml sales orders controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once('Mage/Adminhtml/controllers/Sales/OrderController.php');

class SoftwareMedia_Sales_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController {

	/**
	 * Cancel order
	 */
	public function cancelAction() {
		if ($order = $this->_initOrder()) {
			try {
				$can_void = $order->canVoidPayment();
				$has_invoices = $order->hasInvoices();
				if ($can_void && $has_invoices) {
					// Void the order when canceled
					$order->getPayment()->void(
						new Varien_Object() // workaround for backwards compatibility
					);
					$order->save();
					$this->_getSession()->addSuccess($this->__('The payment has been voided.'));
				}

				$order->cancel()
					->save();
				$order->sendOrderUpdateEmail();
				$this->_getSession()->addSuccess(
					$this->__('The order has been cancelled.')
				);
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			} catch (Exception $e) {
				$this->_getSession()->addError($this->__('The order has not been cancelled.'));
				Mage::logException($e);
			}
			$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		}
	}

}
