<?php
require_once('Enterprise/GiftCardAccount/controllers/CartController.php');
class OCM_Checkout_GiftCart_CartController extends Enterprise_GiftCardAccount_CartController
{
    public function addAction()
    {
        $data = Mage::app()->getRequest()->getParam('code');
        if (isset($data)) {
            $code = $data;
            try {
                Mage::getModel('enterprise_giftcardaccount/giftcardaccount')
                    ->loadByCode($code)
                    ->addToCart();
                Mage::getSingleton('checkout/session')->addSuccess(
                    $this->__('Gift Card "%s" was added.', Mage::helper('core')->htmlEscape($code))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('enterprise_giftcardaccount_add', array('status' => 'fail', 'code' => $code));

                Mage::getSingleton('checkout/session')->addError(
                    $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($code))
                );
                Mage::getSingleton('checkout/session')->addError(
                    $e->getMessage()
                );
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError(
                    $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($code))
                );
                Mage::getSingleton('checkout/session')->addException($e, $this->__('Cannot apply gift card.'));
            }
        }
        $this->_redirect('checkout/cart');
    }
}
