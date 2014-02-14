<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nguyen Sy Doan
 * Date: 2/8/13
 * Time: 1:56 PM
 * To change this template use File | Settings | File Templates.
 */ 
class OCM_Checkout_Model_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage {
    public $_newCustomer = array();
    public function saveBilling($data, $customerAddressId)
    {
        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
            
        $dataForm = Mage::app()->getRequest()->getPost();
        
        //Redirect Canadian Orders
		if ($dataForm['billing']['country_id'] == 'CA') {
                $model = Mage::getModel('quotedispatch/quotedispatch');
                $item_model = Mage::getModel('quotedispatch/quotedispatch_items');
                //die(var_dump($post));
                
                $model->setData($dataForm['billing']);
                $model->setNotes('Canadian Order Auto Quote');
                $model->setPhone($dataForm['billing']['telephone']);
                $now = new DateTime('now', new DateTimeZone('America/Denver'));
                $now->add(new DateInterval('P1M'));
                $expire_time = $now->format('Y-m-d H:i:s');
                //$model->setExpireTime($expire_time);
                
                $model->setStatus(0);
                $model->save();
                
                $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
                
                foreach($items as $item) {
                    if (!$item->getParentId()){
                        $item_model->setData($item->getData());
                        $item_model->setQuotedispatchId($model->getId());
                        $item_model->save();
                    }
                }
                
              
            return array('redirect' => '/canada-quote');
        }
        
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors  = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $dataForm = Mage::app()->getRequest()->getPost();
            if($dataForm['create_new_account']==1){
                $email = $dataForm['billing']['email'];
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($email);
                //Zend_Debug::dump($customer->debug()); exit;
                if(!$customer->getId()) {
                    $customer->setEmail($email);
                    $customer->setFirstname($dataForm['billing']['firstname']);
                    $customer->setLastname($dataForm['billing']['lastname']);
                    $customer->setPassword($customer->generatePassword(6));
                    try{
                        //the save the data and send the new account email.
                        $customer->save();
                        $customer->setConfirmation(null);
                        $customer->save();
                        $customer->sendNewAccountEmail();
                        Mage::getSingleton('customer/session')->loginById($customer->getId());
                    }
                    catch(Exception $ex){
                    }
                }
            }
            $addressForm->setEntity($address);
            // emulate request object
            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => array_values($addressErrors));
            }
            $addressForm->compactData($addressData);
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }

        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = array('customer_address_id');

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setSaveInAddressBook(0)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
            //Recollect Shipping rates for shipping methods
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);

        return array();
    }

}