<?php

class TBT_RewardsReferral_Model_Observer_Refer extends Varien_Object {

    public function recordPointsUponRegistration($observer) {
        try {
            $model = Mage::getModel('rewardsref/referral_signup');
            $newCustomer = $observer->getEvent()->getCustomer();
            $model->triggerEvent($newCustomer);
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function recordPointsForOrderEvent($observer)
    {
        $orderObj = $observer->getEvent()->getOrder();
        $orderId = $orderObj->getId();
        $order = Mage::getModel('rewards/sales_order')->load($orderId);

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            // link to referral any order rule only
            $this->recordPointsGuestOrder($order);
            return $this;
        }

        $this->recordPointsUponFirstOrder($order);
        $this->recordPointsOrder($order);
        return $this;
    }

    /**
     *
     *
     * @param Mage_Sales_Model_Order $order
     * @return TBT_RewardsReferral_Model_Observer_Refer
     */
    public function recordPointsUponFirstOrder($order) {
        try {
            $model = Mage::getModel('rewardsref/referral_firstorder');
            $model->setOrder($order);
            if ($model->isSubscribed($order->getCustomerEmail()) && false == $model->isConfirmed($order->getCustomerEmail())) {
                $customer = Mage::getModel('rewards/customer')->load($order->getCustomerId());
                $model->triggerEvent($customer, $order->getId());
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     *
     * @param TBT_Rewards_Model_Sales_Order $order
     * @return TBT_RewardsReferral_Model_Observer_Refer
     */
    public function recordPointsOrder($order) {
        try {
            $model = Mage::getModel('rewardsref/referral_order');
            $model->setOrder($order);
            $child = Mage::getModel('rewards/customer')->load($order->getCustomerId());
            $affiliate = Mage::getModel('rewards/customer')->load($model->getReferralParentId());
            $model->triggerEvent($child, $order->getId());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Apply only for referral "Any order" rule only
     *
     * @param TBT_Rewards_Model_Sales_Order $order
     * @return TBT_RewardsReferral_Model_Observer_Refer
     */
    public function recordPointsGuestOrder($order)
    {
        try {
            $model = Mage::getModel('rewardsref/referral_guestorder');
            $model->setOrder($order);
            $emptyCustomerObj = Mage::getModel("customer/customer");
            $model->triggerEvent($emptyCustomerObj, $order->getId());
        } catch (Exception $e) {
            Mage::helper("rewards")->logException($e);
        }
        return $this;
    }

}
