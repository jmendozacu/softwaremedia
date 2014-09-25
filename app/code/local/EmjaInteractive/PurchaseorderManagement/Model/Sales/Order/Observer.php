<?php
class EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Observer
{
    protected $_incrementedOrdersId = array();

    /**
     * Enter description here ...
     * @param unknown_type $observer
     * @return EmjaInteractive_PurchaseorderManagement_Model_Sales_Order_Observer
     */
    public function saveNetTerms($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Mage_Sales_Model_Order) {
            try {
                $customerEmail = $order->getCustomerEmail();
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($order->getStore()->getWebsite()->getId())
                    ->loadByEmail($customerEmail);
                if ($customer instanceof Mage_Customer_Model_Customer) {
                    $order->setNetTerms($customer->getNetTerms());
                    foreach($order->getAllItems() as $item) {
                        $item->setNetTerms($customer->getNetTerms());
                    }
                }

                if (Mage::app()->getStore()->isAdmin()) {
                    $orderPostData = Mage::app()->getRequest()->getParam('order', array());
                    
                    if (isset($orderPostData['account']) && isset($orderPostData['account']['net_terms'])) {
                        $netTerms = $orderPostData['account']['net_terms'];
                        $order->setNetTerms($netTerms);
                        foreach($order->getAllItems() as $item) {
                            $item->setNetTerms($netTerms);
                        }
                    }
                    
                    $paymentPostData = Mage::app()->getRequest()->getParam('payment', array());
                    if ($paymentPostData['cod']) {
	                    $netTerms = 'COD ' . $paymentPostData['cod'];
                        $order->setNetTerms($netTerms);
                        foreach($order->getAllItems() as $item) {
                            $item->setNetTerms($netTerms);
                        }

                        $order->addStatusToHistory('processing', 'IMPORTANT: Order has NOT been paid and should be shipped COD.', false);
                    }
                    
                    
                }

            } catch (Exception $e) {
                Mage::log($e->getTraceAsString());
            }
        }
        return $this;
    }

    public function addColumnToResource($observer)
    {
        $resource = $observer->getEvent()->getResource();
        $resource->addVirtualGridColumn(
            'payment_method',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'method'
        );        

        $resource->addVirtualGridColumn(
            'po_number',
            'sales/order_payment',
            array('entity_id' => 'parent_id'),
            'po_number'
        );
    }

    public function isPOLimitExceeded($observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getIncreasedCredit()) {
            return;
        }
        if ($order->getId()) {
            return;
        }
        
        $customer = $order->getCustomer();

        if (Mage::app()->getStore()->isAdmin()) {
            $dataHasChanged = false;
            $orderPostData = Mage::app()->getRequest()->getParam('order', array());
            if (isset($orderPostData['account']) && isset($orderPostData['account']['po_limit'])) {
                if ($customer->getPoLimit() != $orderPostData['account']['po_limit']) {
                    $customer->setPoLimit($orderPostData['account']['po_limit']);
                    $dataHasChanged = true;
                }
            }
            if (isset($orderPostData['account']) && isset($orderPostData['account']['po_credit'])) {
                if ($customer->getPoCredit() != $orderPostData['account']['po_credit']) {
                    $customer->setPoCredit($orderPostData['account']['po_credit']);
                    $dataHasChanged = true;
                }
            }

            if ($dataHasChanged) {
                $customer->save();
            }
        }

        if ( $customer->getId() && $order->getPayment()->getMethod() == 'purchaseorder' ) {
            if (!$customer->getPoLimit()) { //set default limit to customer
                $defaultLimit = Mage::getStoreConfig('payment/purchaseorder/default_limit');
                $customer->setPoLimit($defaultLimit)->save();
            }
            $credit = (float) $customer->getPoCredit() + $order->getGrandTotal();

            if ($credit > (float) $customer->getPoLimit()) {
                Mage::throwException(Mage::getStoreConfig('payment/purchaseorder/exceeded_limit_message'));
            }
        }
    }

    public function incrementPOCredit($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (in_array($order->getId(), $this->_incrementedOrdersId)) {
            return false;
        }
        $customer = $order->getCustomer();
        if (!$customer) {
            return;
        }

        if ( $customer->getId() && $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $credit = (float) $customer->getPoCredit() + $order->getGrandTotal();
            $customer->setPoCredit($credit)->save();
            array_push($this->_incrementedOrdersId, $order->getId());
            $order->setIncreasedCredit(true);

			Mage::getResourceModel('ordertags/orderidtotagid')->addIntoDB($order->getId(), 37);
        }
    }

    public function decrementPOCreditInvoice($observer)
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();

        if ( $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $customerEmail = $order->getCustomerEmail();
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($order->getStore()->getWebsite()->getId())
                ->loadByEmail($customerEmail);

            if ($customer->getId()) {
                $credit = (float) $customer->getPoCredit() - $order->getGrandTotal();

                if ($credit < 0) {
                    $credit = 0;
                }
                $customer->setPoCredit($credit)->save();
            }
        }
    }

    public function decrementPOCredit($observer)
    {
        $order = $observer->getEvent()->getItem()->getOrder();

        if ( $order->getPayment()->getMethod() == 'purchaseorder' ) {
            $customerEmail = $order->getCustomerEmail();
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($order->getStore()->getWebsite()->getId())
                ->loadByEmail($customerEmail);

            if ($customer->getId()) {
                $credit = (float) $customer->getPoCredit() - $order->getGrandTotal();

                if ($credit < 0) {
                    $credit = 0;
                }
                $customer->setPoCredit($credit)->save();
            }
        }
    }

}
