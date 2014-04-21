<?php
class OCM_Setup_Model_Observer
{
    public function catalog_product_save_before($observer)
    {	

		echo "OBSERVER";    	die();
    
        $product = $observer->getProduct();
        echo "<pre>"; print_r($product->getData()); 
        die();
        exit;
        // do something here
    }
    public function saveCustomData($event)
    {
    	Mage::log('SAVE',null,'test.log');
    	$data = Mage::app()->getRequest()->getPost('order');
        $order = $event->getEvent()->getOrder();
        $order->setData('purchase_order', $data['account']['purchase_order']);

        return $this;
    }
}