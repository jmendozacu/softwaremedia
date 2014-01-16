<?php

class TBT_Rewards_Model_Customer_Observer extends Varien_Object
{
	
	/**
	 * @var int
	 */
	protected $oldId = -1;
	
	/**
	 * AfterLoad for customer
	 * @param Varien_Event_Observer $observer
	 */
	public function customerAfterLoad(Varien_Event_Observer $observer)
	{
	    $customer = $observer->getEvent()->getCustomer();
	    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer);
	    return $this;
	}

    /**
     * AfterSave for customer
     * @param Varien_Event_Observer $observer
     */
    public function customerAfterSave(Varien_Event_Observer $observer) {
        $customer_obj = $observer->getEvent()->getCustomer();
        $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($customer_obj);
        
        //If the customer is new (hence not having an id before) get applicable rules,
        //and create a transfer for each one
        $isNew = false;
        
        $newId = $customer->getId();
        
        if ( $this->oldId != $newId ) {
            $isNew = true;
            $this->oldId = $customer->getId(); //This stops multiple triggers of this function
            $customer->createTransferForNewCustomer(); //@TODO Change to separate transfer model
        }
        
        Mage::getSingleton('rewards/session')->setCustomer($customer);
        
        if ( $isNew ) {
            $this->setRequiresDispatchAfterOrder(false);
            if ($this->getIsOrderBeingPlaced()) {
                $this->setRequiresDispatchAfterOrder(true);
                return $this;
            }
            
            $this->_dispatchCustomerCreation($customer);
        }
        
        return $this;
    }
	
    /**
        * BeforeSave for customer
        * @param Varien_Event_Observer $observer
        */
    public function customerBeforeSave($observer)
    {
        
        //this method is executed 2x in 1.4.0.1 set registry var to indicate already ran
        if(Mage::registry('customer_before_save_observer_executed')){
            return $this; 
        }
        
        Mage::register('customer_before_save_observer_executed',true);
        
        $customer = Mage::getModel('rewards/customer')->getRewardsCustomer(  $observer->getEvent()->getCustomer()  );
        $oldId = $customer->getId();
        if (!empty($oldId)) {
            $this->oldId = $oldId;
        }
        
        return $this;
    }
    
    public function orderIsBeingPlaced($observer)
    {
        $this->setIsOrderBeingPlaced(true);
        return $this;
    }
    
    public function submitOrderSuccess($observer)
    {
        $this->setIsOrderBeingPlaced(false);
        
        if (!$observer->getEvent()) {
            return $this;
        }
        
        if (!$observer->getEvent()->getOrder()) {
            return $this;
        }
        
        $customer = $observer->getEvent()->getOrder()->getCustomer();
        if (!$customer) {
            return $this;
        }
        
        if ($this->getRequiresDispatchAfterOrder()) {
            $this->_dispatchCustomerCreation($customer);
        }
        return $this;
    }
    
    protected function _dispatchCustomerCreation($customer)
    {
        
        if ( Mage::helper('rewards/dispatch')->smartDispatch('rewards_customer_signup', array(
                'customer' => $customer
        )) ) {
            Mage::getSingleton('rewards/session')->triggerNewCustomerCreate($customer);
            Mage::dispatchEvent('rewards_new_customer_create', array(
                    'customer' => &$customer
            ));
        }
        
        return $this;
    }
	
	/**
     * True if the id specified is new to this customer model after a SAVE event.
     *
     * @param integer $checkId
     * @return boolean
     */
    public function isNewCustomer($checkId)
    {
        return $this->oldId != $checkId;
    }
	
	/**
	 * Loads the customer wrapper
	 * @param Mage_Customer_Model_Customer $customer
	 * @return TBT_Rewards_Model_Customer_Wrapper
	 */
	private function _loadCustomer(Mage_Customer_Model_Customer $customer)
	{
	    return Mage::getModel('rewards/customer')->load($customer->getId());
	}
	
	/**
	 * Sets up save for any rewards specific customer fields
	 * 
	 * @return TBT_Rewards_Model_Customer_Observer
	 */
    public function adminhtmlCustomerPrepareSave($observer)
	{
        if (!$observer->getEvent()) {
            return $this;
        }
        
        $request = $observer->getEvent()->getRequest();
        if (!$request) {
            return $this;
        }
        
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer) {
            return $this;
        }
        
        $data = $request->getPost();
        
        if (isset($data['rewards_points_notification_save'])) {
            $rewardsPointsNotification = 0;
            if (isset($data['rewards_points_notification'])) {
                $rewardsPointsNotification = $data['rewards_points_notification'];
            }
            $customer->setData('rewards_points_notification', $rewardsPointsNotification);
        }
        
        return $this;
	}
	
}