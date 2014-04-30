<?php
class OCM_Setup_Model_Observer
{
    public function catalog_product_save_before($observer)
    {	

    }
    public function saveCustomData($event)
    {
    	$data = Mage::app()->getRequest()->getPost('order');
        $order = $event->getEvent()->getOrder();
        $order->setData('purchase_order', $data['account']['purchase_order']);

        return $this;
    }
}