<?php

class EmjaInteractive_PurchaseorderManagement_Model_Adminhtml_Sales_Order_Grid_Observer
{
    public function beforeHtml($observer)
    {
        $block = $observer->getBlock();

		if ($block instanceof Mage_Adminhtml_Block_Sales_Order) {
			$block->addButton('attributes_button', array(
        	'label' => 'Grid Columns',
	        'onclick' => 'pAttribute.showConfig();',
			));
		}
		
        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) {
            if ($paymentMethodColumn = $block->getColumn('payment_method')) {
                $paymentMethodColumn->setData(
                    'options',
                    Mage::getSingleton('emjainteractive_purchaseordermanagement/adminhtml_system_config_source_order_payment_method')->getPaymentMethods()
                );
            }
			//$block->removeColumn('payment_method');
			//$block->removeColumn('po_number');
			//$block->removeColumn('net_terms');
			foreach(Mage::helper('ampgrid')->getOrderGridAttributes() as $key => $remove) {
				$block->removeColumn($remove);
			}
		
            $block->getMassactionBlock()->addItem('pdforders_order', array(
                 'label'=> Mage::helper('sales')->__('Print PO Invoice'),
                 'url'  => $block->getUrl('*/po_sales_order/pdforders'),
            ));
        }
    }
}