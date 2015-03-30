<?php

require_once('Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php');

class SoftwareMedia_Licensing_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController {

	protected $licenses = array();
	
    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        $license = $this->getRequest()->getPost('license');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
        		
        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

			
            $shipment->register();

			//Load End User custom information
            $order = $shipment->getOrder();
	        $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
	        $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($order->getId(), $order->getStoreId(), true, true); 


			foreach($license['items'] as $key => $item) {
				if ($item) {
					$orderItem = Mage::getModel('sales/order_item')->load($key);
					$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($orderItem->getId(), 'order_item_id');
					$invMult = 1;
						
					if ($orderItem->getQtyInvoiced() > 0) 
						$invMult = $invoiceItem->getQty() / $orderItem->getQtyOrdered();
						 
					$qty = $data['items'][$key] * $invMult;
					
					$this->_sendLicense($key,$qty,$item,$shipment,$aCustomAtrrList);
				}
			}
			$this->sendLicense();

            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

	protected function addLicense($vars) {
		if (!array_key_exists($vars['email'], $this->licenses)) 
			$this->licenses[$vars['email']] = array();
		
		$this->licenses[$vars['email']][] = $vars;
	}
	
	protected function sendLicense() {
		foreach($this->licenses as $email => $license) {
			$vars = null;
			$qtyHTML = null;
			
			foreach($license as $item) {
				if (!$vars) {
					$vars = $item;
					$vars['qty'] = array();
					$vars['sku'] = array();
				} 
				
				if (!$item['sku']) {
					Mage::getSingleton ( 'adminhtml/session' )->addError ( "SKU " . $item['real_sku'] . " is missing manufacturer part number - not ordered" );
					continue;
				}
					
				$qtyHTML .= "Qty: " . $item['qty'] . "<br />";
				$qtyHTML .= "SKU: " . $item['sku'] . "<br /><br />";

				
			}
			
			if (!$qtyHTML)
				continue;
				
			$vars['qty'] = $qtyHTML;
			$template = Mage::getModel('core/email_template');
			
	        $template->loadDefault($vars['template']);
	        $template->setSenderName('Software Media Licensing');
	        $template->setSenderEmail('licensing@softwaremedia.com');
	        $template->setTemplateSubject($vars['subject']);
	        $template->addBcc("licensing@softwaremedia.com");
	        $template->send($email, $email, $vars);
        
		}
	}
	protected function _sendLicense($itemId,$qty,$dist,$shipment,$endUser) {
		if (!$qty)
			return;
			
		$subjectDist = array(
			'Ingram' => array('email' => 'manufacturer@ingrammicro.com', 'subject'=>'Acct: 50-208-360 License Order', 'template'=>'ingram_license'),
			'TechData' => array('email' => 'wbd3@techdata.com', 'subject'=>'License Order Account #38024479', 'template'=>'techdata_license'),
			'Synnex' => array('email' => 'team_6540@synnex.com', 'subject'=>'License Order Account #520985', 'template'=>'synnex_license'));
		
		$endUserFields = array('company_name','contact_name','address','city','zip','company_email_address','country','state','country','phone_number','prior_license_authorization','prior_license_agreement');
		$endUserHtml = "";
		
		foreach($endUser as $attr) {
			//var_dump($attr);
			if ($attr['value'] && in_array($attr['code'], $endUserFields))
				$endUserHtml .= $attr['label'] . ": " . $attr['value'] . "<br />";
			
		}
		
		$orderItem = Mage::getModel('sales/order_item')->load($itemId);
		$sku = $orderItem->getProductId();
		
		//echo $orderItem->getProductId();
		echo $this->_getIngramEmail($orderItem->getProductId());
		
		$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($orderItem->getId(), 'order_item_id');
		if ($invoiceItem->getSku())
			$sku = $invoiceItem->getProductId();
			
		$product = Mage::getModel('catalog/product')->load($sku);
		$sku = $product->getData('manufacturer_pn_2');
		
		$order = $shipment->getOrder();
		$order = Mage::getModel('sales/order')->load($order->getId());
		$order->addStatusHistoryComment("<strong>License Ordered - </strong>" . $dist . "<br />SKU: " . $sku . "<br />" . " QTY: " . $qty)
            ->setIsVisibleOnFront(false)
            ->setIsCustomerNotified(false);
           
        $order->save();
        
        $vars = array();
		$template = Mage::getModel('core/email_template');
		 
		if ($dist == 'Ingram')
			$email = $this->_getIngramEmail($orderItem->getProductId()) . "@ingrammicro.com";
		else 
			$email = $subjectDist[$dist]['email'];
		
		$vars['order'] = $order;	
		$vars['order_id'] = $order->getId();
		$vars['increment_id'] = $order->getIncrementId();
		$vars['enduser'] = $endUserHtml;
		$vars['qty'] = $qty;
		$vars['sku'] = $sku;
		$vars['real_sku'] = $product->getSku();
		$vars['template'] = $subjectDist[$dist]['template'];
		$vars['subject'] = $subjectDist[$dist]['subject'];
		$vars['email'] = $email;
		
		
		if($product->getTypeId() == 'grouped' ) {
			$multQty = $qty;
			$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
			$hasPrice = false;
			
			foreach($associatedProducts as $groupSubProd) {
				$qty = 1;
				
				if ($groupSubProd->getQty() > 0)
					$qty = $groupSubProd->getQty();
					
				$vars['sku'] = $groupSubProd->getData('manufacturer_pn_2');
				$vars['qty'] = $qty * $multQty;
				$vars['real_sku'] = $groupSubProd->getSku();
				$this->addLicense($vars);
			}
		} else {
			 $this->addLicense($vars);
		}
		
        $template->loadDefault($subjectDist[$dist]['template']);
        $template->setSenderName('Software Media Licensing');
        $template->setSenderEmail('licensing@softwaremedia.com');
        $template->setTemplateSubject($subjectDist[$dist]['subject']);
        $template->addBcc("licensing@softwaremedia.com");
       // $template->send($email, $email, $vars);
	}
	
	protected function _getIngramEmail($productId) {
		$manLookup = array('Microsoft Open Value' => 'Open.Value','Microsoft' => 'microsoft-licensing','Microsoft Open Government' => 'microsoft-licensing','VMware Academic' => 'VMware'); 
		
		$product = Mage::getModel('catalog/product')->load($productId);
		$cats = $product->getCategoryIds();
		foreach ($cats as $category_id) {
			
		    $_cat = Mage::getModel('catalog/category')->load($category_id) ;
		    //echo $_cat->getParentCategory()->getId();
		    if ($_cat->getParentCategory()->getId() == 52) {
		    	if (array_key_exists($_cat->getName(), $manLookup))
		    		return $manLookup[$_cat->getName()];
		    		
		    	return str_replace(" ","",$_cat->getName());
		    }
		} 
		//return $category->getPath();
	}

}