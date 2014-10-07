<?php

class OCM_Peachtree_Model_Csv extends Mage_Core_Model_Abstract {

	const SHIP_VIA = 'Fe-Ex';
	const DISPLAYED_TERMS = 'Prepaid';
	const ACCOUNT_RECEIVABLE = '11000';
	const SALES_TAX_ID = 'SLC';
	const GL_ACCOUNT_ITEM = 40000;
	const GL_ACCOUNT_TAX = 21550;
	const GL_ACCOUNT_FRIEGHT = 41050;
	const GL_ACCOUNT_PROMO = 40100;
	const TAX_TYPE_ITEM = 1;
	const TAX_TYPE_TAX = 0;
	const TAX_TYPE_FRIEGHT = 26;
	const TAX_TYPE_PROMO = 1;

	public function getCsv() {

		$codeMap = array(
			'fedex' => 'Fed-Ex',
			'dhl' => 'DHL',
			'dhlint' => 'DHL',
			'usps' => 'United States Postal Service',
			'ups' => 'United Parcel Service');

		if (!$this->getFrom() || !$this->getTo()) {
			return false;
		}

		$csv = '';
		
		//Load all orders that were shipped within date range
		$orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('shipment.created_at', 				array(
			'from' => $this->getFrom(),
			'to' => date('Y-m-d', strtotime($this->getTo()) + 60 * 60 * 24),
			'date' => true, // specifies conversion of comparison values
			));

		/*
		$orders->getSelect()->joinLeft(
				array('shistory' => new Zend_Db_Expr('(SELECT created_at as history_complete, parent_id as parent_id FROM sales_flat_order_status_history AS ohist WHERE ohist.status = "processing"  GROUP BY parent_id ORDER BY created_at DESC)')), 'shistory.parent_id = main_table.entity_id'
		);
		*/
		
		//Join PT referer
		$orders->getSelect()->joinLeft(
			'ocm_peachtree_referer as referer', 'referer.order_id = main_table.entity_id', array('referer_id')
		);
		
		//Join shipping address for ship region
		$orders->getSelect()->joinLeft(
			'sales_flat_order_address as shippingaddress', 'shippingaddress.entity_id = main_table.shipping_address_id', array(
			'ship_region' => 'region')
		);
		
		//Join order invoice
		$orders->getSelect()->joinLeft(
			'sales_flat_invoice as invoice', 'invoice.order_id = main_table.entity_id', array(
			'invoice_id' => 'entity_id')
		);
		
		//Join order shipment to make sure order has already been shipped. 
		$orders->getSelect()->joinInner(
					'sales_flat_shipment as shipment', 'main_table.entity_id = shipment.order_id', 
					array('item_ship_date' => 'created_at'));
					
		$orders->getSelect()->group('main_table.entity_id');
		
		foreach($orders as $order) {
			$invoiceItems = false;
			if ($order->getInvoiceId()) {
				$invoice = Mage::getModel('sales/order_invoice')->load($order->getInvoiceId());
				$invoiceItems = Mage::getResourceModel('sales/order_invoice_item_collection')
				->addFieldToFilter('main_table.parent_id', $invoice->getId());
				
				$invoiceItems->getSelect()
				->joinLeft(
					'sales_flat_shipment_item as shipment_item', 'shipment_item.order_item_id = main_table.order_item_id', array('shipment_id' => 'parent_id')
				)
				->joinLeft(
					'catalog_product_entity',
					'catalog_product_entity.entity_id = main_table.product_id', 
					array('product_type' => 'type_id')
				)
				->joinLeft(
					'sales_flat_shipment as shipment', 'shipment_item.parent_id = shipment.entity_id', array('item_ship_date' => 'created_at')
				)->group('main_table.entity_id');
			
			}
			
			$shipTime = false;
			//$items = $invoice->getAllItems();
			
			//->setInvoiceFilter($invoice->getId())
			;

			$track = Mage::getModel('sales/order_shipment_track')->getCollection();
			$track->addAttributeToFilter('order_id', $order->getId());
			$tracking = $track->getFirstItem();
			
			$items = Mage::getResourceModel('sales/order_item_collection')
				->addFieldToFilter('main_table.order_id', $order->getId());
				
			$items->getSelect()
				->joinLeft(
					'sales_flat_shipment_item as shipment_item', 'shipment_item.order_item_id = main_table.item_id', array('shipment_id' => 'parent_id')
				)
				->joinLeft(
					'catalog_product_entity',
					'catalog_product_entity.entity_id = main_table.product_id', 
					array('product_type' => 'type_id')
				)
				->joinLeft(
					'sales_flat_shipment as shipment', 'shipment_item.parent_id = shipment.entity_id', array('item_ship_date' => 'created_at')
				)->group('main_table.item_id')
			;
				
			//$order = Mage::getModel('sales/order')->load($invoice->getOrderId());
			$has_points_line = false;
			$points = $order->getAssociatedTransfers();

			$points->selectFullCustomerName('fullcustomername');
			$points->selectPointsCaption('points');
			$points->addRules();
			
			// include any transfers that have revoked the original transfers
			$points_discount = 0;
			foreach ($points as $point) {
				if ($point->getPoints() < 0) {
					$has_points_line = true;
					$points_discount = floor($point->getPoints() / 100);
					break;
				}
			}
			
			$has_tax_line = ($order->getData('tax_amount') > 0) ? 1 : 0;
			$has_ship_line = ($order->getData('shipping_amount') > 0) ? 1 : 0;
			$has_promo_line = ($order->getData('discount_amount') != 0) ? 1 : 0;
			
			if ($has_promo_line && ($invoice->getData('discount_amount')) * -1 + $points_discount > 0) {
				$has_promo_line = 1;
			} else {
				$has_promo_line = 0;
			}

			$shipVia = $tracking->getData('carrier_code');

			if (array_key_exists($shipVia, $codeMap))
				$shipVia = $codeMap[$shipVia];

			$itemCount = 0;
			$grouped = array();
			
			//Skip products with parents (configurbales would be counted twice otherwise)
			foreach ($items as $item) {
				//$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				if ($item->getParentItemId())
					continue;
				
					
				//Count up grouped products
				if( $item->getProductType() == 'grouped' ) {
					$product = Mage::getModel('catalog/product')->load($item->getProductId());
					$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
					foreach($associatedProducts as $groupSubProd) {
						$itemCount++;
					}
					continue;
				}
				
				$itemCount++;
			}
			if ($itemCount == 0)
				continue;
				
			$orderId = $order->getIncrementId();
			
			$payment = $order->getPayment()->getMethodInstance()->getCode();
			if ($payment == 'purchaseorder') {
				$terms = 'NET';
				if ($order->getNetTerms()) {
					$t = str_replace('Net ' ,'', $order->getNetTerms());
					$t = str_replace('NET ' ,'', $t);
					$terms .= $t;
				} else {
					$terms .= '30';
				}
			} else {
				$terms = self::DISPLAYED_TERMS;
			}
			
			if ($payment == 'checkmo') {
				$terms = 'Prepaid Wire';
			}
			if ($payment == 'purchaseorder' && substr($order->getNetTerms(),0,3) == 'COD')
				$terms = 'COD';
				
			$common_values = array(
				'customer_id' => 'O' . date('my', strtotime($order->getData('created_at'))),
				'invoice_id' => $orderId,
				'date' => date('m/d/Y', strtotime($order->getData('created_at'))),
				'ship_via' => $shipVia,
				'ship_date' => '', //item, tax, frieght
				'due_date' => date('m/d/Y', strtotime($order->getData('created_at'))),
				'displayed_terms' => $terms,
				'sales_rep_id' => OCM_Peachtree_Model_Referer::getNameByCode($order->getData('referer_id')),
				'account_receivable' => self::ACCOUNT_RECEIVABLE,
				'sales_tax_id' => ($has_tax_line) ? self::SALES_TAX_ID : '',
				'number_of_distributions' => $itemCount + $has_tax_line + $has_ship_line + $has_promo_line + $has_points_line,
				'invoice_cm_distributions' => '', //item, tax, frieght
				'qty' => 0, //item
				'item_id' => '', //item 'sku'
				'description' => '', //item, tax, frieght - title
				'gl_account' => '', //item, tax, frieght - get from constant
				'unit_price' => 0, //item
				'tax_type' => '', //item, tax, frieght - get from constant
				'amount' => '', //item, tax, frieght - price x qty
				'sales_tax_agency_id' => ''
			);
			
			if (OCM_Peachtree_Model_Referer::checkForUser($order->getData('referer_id')))
				$common_values['customer_id'] = $common_values['customer_id'] . 'W';

			//Manually set referrer for Buy.com customers
			if ($order->getCustomerId() == 1117) {
				$common_values['customer_id'] = 'BUY.COM';
				$common_values['sales_rep_id'] = 'Buy.com';
			}
			if ($order->getCustomerId() == 1120) {
				$common_values['customer_id'] = 'BESTBUY.COM';
				$common_values['sales_rep_id'] = 'Best Buy';
			}
			if ($order->getCustomerId() == 1121) {
				$common_values['customer_id'] = 'AMAZONSWM';
				$common_values['sales_rep_id'] = 'Amazon';

				$payment = $order->getPayment();
				$additionalData = @unserialize($payment->getAdditionalData());
				$common_values['invoice_id'] = $additionalData['channel_order_id'];
			}
			
			//If PO, don't put under wholesale customer
			if ($payment == 'purchaseorder') {
				$common_values['customer_id'] = '';	
			}
			
			//If Peachtree ID exists, assign it
			if ($order->getCustomerId()) {
				$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
				if ($customer->getPeachtreeId()) {
					$common_values['customer_id'] = $customer->getPeachtreeId();
				}
			}
			$i = 1;
			
			$hasInvoice = false;
			if ($invoiceItems) {
				$hasInvoice = true;
				$items = $invoiceItems;
			}
			foreach ($items as $item) {
				
				if ($hasInvoice) 
					$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				else 
					$orderItem = $item;
				
				if ($orderItem->getParentItemId())
					continue;

				if ($orderItem->getQtyInvoiced() > 0)
					$itemQty = $orderItem->getQtyInvoiced();
				else
					$itemQty = $orderItem->getQtyOrdered();
					
				if ($item->getQty())
					$itemQty = $item->getQty();
					

				$unitPrice = $item->getRowTotal() / $itemQty;
				//$itemQty = $itemQty - $orderItem->getQtyRefunded();
				
				if (!$shipTime)
					$shipTime = date('m/d/Y', strtotime($item->getData('item_ship_date')));
				
				$rowTotal = $item->getRowTotal();
				
				
				//Subtract any refunded items from row total
				//if ($orderItem->getAmountRefunded())
				//	$rowTotal -= $orderItem->getAmountRefunded();
				
				//Update customer id for FBA orders
				if (substr($item->getSku(), -3) == 'FBA' && $order->getCustomerId() == 1121)
					$common_values['customer_id'] = 'AMAZONFBA';

				$item_values = array(
					'ship_date' => date('m/d/Y', strtotime($item->getData('item_ship_date'))),
					'invoice_cm_distributions' => $i++,
					'qty' => $itemQty,
					'item_id' => $item->getSku(),
					'description' => $item->getName(),
					'gl_account' => self::GL_ACCOUNT_ITEM,
					'unit_price' =>number_format($unitPrice * -1,2,'.',''),
					'tax_type' => self::TAX_TYPE_ITEM,
					'amount' => $rowTotal * -1,
				);
				
					
				//Split up grouped products into their associated products
				if( $item->getProductType() == 'grouped' ) {
					$product = Mage::getModel('catalog/product')->load($item->getProductId());
					$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
					$hasPrice = false;
					foreach($associatedProducts as $groupSubProd) {
						$qty = 1;
						if ($groupSubProd->getQty() > 0)
							$qty = $groupSubProd->getQty();
							
						$item_values['item_id'] = $groupSubProd->getSku();
						$item_values['description'] = $groupSubProd->getName();
						$item_values['qty'] = $qty * $orderItem->getQtyOrdered();
						$item_values['unit_price'] = number_format($item_values['amount'] / $item_values['qty'],2,'.','');
						
						if ($hasPrice) {
							$item_values['unit_price'] = 0;
							$item_values['amount'] = 0;
						}
						$line_values = array_merge($common_values, $item_values);
						$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
						
						$hasPrice = true;
					}
					continue;
				}
				
				$line_values = array_merge($common_values, $item_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}

			if ($has_tax_line) {

				$tax_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => 0,
					'description' => 'Salt Lake County Sales Tax',
					'gl_account' => self::GL_ACCOUNT_TAX,
					'tax_type' => self::TAX_TYPE_TAX,
					'amount' => ($order->getData('tax_amount')) * -1,
					'sales_tax_agency_id' => self::SALES_TAX_ID,
				);
				$line_values = array_merge($common_values, $tax_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}

			if ($has_ship_line) {

				$ship_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => 0,
					'description' => 'Freight Amount',
					'gl_account' => self::GL_ACCOUNT_FRIEGHT,
					'tax_type' => self::TAX_TYPE_FRIEGHT,
					'amount' => ($order->getData('shipping_amount')) * -1,
				);
				$line_values = array_merge($common_values, $ship_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}
			

			if ($has_promo_line && ($order->getData('discount_amount')) * -1 + $points_discount > 0) {

				$promo_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => $i++,
					'description' => 'Promo: ' . $order->getData('coupon_rule_name'),
					'gl_account' => self::GL_ACCOUNT_PROMO,
					'tax_type' => self::TAX_TYPE_PROMO,
					'amount' => ($order->getData('discount_amount')) * -1 + $points_discount,
					'item_id' => 'SM-PROMOUSED'
				);
				$line_values = array_merge($common_values, $promo_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}
			if ($has_points_line) {

				$promo_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => $i++,
					'description' => 'Loyalty Discount',
					'gl_account' => self::GL_ACCOUNT_PROMO,
					'tax_type' => self::TAX_TYPE_PROMO,
					'amount' => $points_discount * -1,
					'item_id' => 'LOYALTY'
				);
				$line_values = array_merge($common_values, $promo_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}
		
		}
		
		$creditMemos = Mage::getModel('sales/order_creditmemo')->getCollection()->addFieldToFilter('created_at', 				array(
			'from' => $this->getFrom(),
			'to' => date('Y-m-d', strtotime($this->getTo()) + 60 * 60 * 24),
			'date' => true, // specifies conversion of comparison values
			));
			
		foreach($creditMemos as $creditMemo) {
			$items = $creditMemo->getAllItems();
			$order = Mage::getModel('sales/order')->load($creditMemo->getOrderId());
			
			
			$has_tax_line = ($creditMemo->getData('tax_amount') > 0) ? 1 : 0;
			$has_ship_line = ($creditMemo->getData('shipping_amount') > 0) ? 1 : 0;
			
			$terms = 'Refund';
			
			//Skip products with parents (configurbales would be counted twice otherwise)
			foreach ($items as $item) {
				$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				
				if ($orderItem->getParentItemId())
					continue;
				
					
				//Count up grouped products
				/*
				if( $orderItem->getProductType() == 'grouped' ) {
					$product = Mage::getModel('catalog/product')->load($item->getProductId());
					$associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
					foreach($associatedProducts as $groupSubProd) {
						$itemCount++;
					}
					continue;
				}
				*/
				$itemCount++;
			}
					
			
			$orderId = $order->getIncrementId() . "-CM";
			
			$i = 0;
			$add = "";
			$orderCreditMemos = Mage::getModel('sales/order_creditmemo')->getCollection()->addFieldToFilter('order_id',$order->getId());
			
			foreach ($orderCreditMemos as $inv) {
				if ($inv->getId() == $creditMemo->getId()) {
					$add = $i;
				}
				$i++;
			}	
			if ($add)
				$orderId .= "-" . $add;
				
			$common_values = array(
				'customer_id' => 'O' . date('my', strtotime($creditMemo->getData('created_at'))),
				'invoice_id' => $orderId,
				'date' => date('m/d/Y', strtotime($creditMemo->getData('created_at'))),
				'ship_via' => $shipVia,
				'ship_date' => '', //item, tax, frieght
				'due_date' => date('m/d/Y', strtotime($creditMemo->getData('created_at'))),
				'displayed_terms' => $terms,
				'sales_rep_id' => '',
				'account_receivable' => self::ACCOUNT_RECEIVABLE,
				'sales_tax_id' => ($has_tax_line) ? self::SALES_TAX_ID : '',
				'number_of_distributions' => $itemCount + $has_tax_line + $has_ship_line,
				'invoice_cm_distributions' => '', //item, tax, frieght
				'qty' => 0, //item
				'item_id' => '', //item 'sku'
				'description' => '', //item, tax, frieght - title
				'gl_account' => '', //item, tax, frieght - get from constant
				'unit_price' => 0, //item
				'tax_type' => '', //item, tax, frieght - get from constant
				'amount' => '', //item, tax, frieght - price x qty
				'sales_tax_agency_id' => ''
			);
			
			
			
			//Manually set referrer for Buy.com customers
			if ($order->getCustomerId() == 1117) {
				$common_values['customer_id'] = 'BUY.COM';
				$common_values['sales_rep_id'] = 'Buy.com';
			}
			if ($order->getCustomerId() == 1120) {
				$common_values['customer_id'] = 'BESTBUY.COM';
				$common_values['sales_rep_id'] = 'Best Buy';
			}
			if ($order->getCustomerId() == 1121) {
				$common_values['customer_id'] = 'AMAZONSWM';
				$common_values['sales_rep_id'] = 'Amazon';

				$payment = $order->getPayment();
				$additionalData = @unserialize($payment->getAdditionalData());
				$common_values['invoice_id'] = $additionalData['channel_order_id'];
			}

			//If Peachtree ID exists, assign it
			if ($order->getCustomerId()) {
				$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
				if ($customer->getPeachtreeId()) {
					$common_values['customer_id'] = $customer->getPeachtreeId();
				}
			}
			
			$i = 1;
			foreach ($items as $item) {
				
				$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
				$invoiceItems = Mage::getModel('sales/order_invoice_item')->getCollection();
				$invoiceItems->addAttributeToFilter('order_item_id',$item->getOrderItemId());
				$invoiceItem = $invoiceItems->getFirstItem();

				if ($orderItem->getParentItemId())
					continue;
				$invMult = $invoiceItem->getQty() / $orderItem->getQtyOrdered();
				$itemQty = $item->getQty() * $invMult;
				
				$rowTotal = $item->getRowTotal();
				
				//Update customer id for FBA orders
				if (substr($item->getSku(), -3) == 'FBA' && $order->getCustomerId() == 1121)
					$common_values['customer_id'] = 'AMAZONFBA';

				$item_values = array(
					'ship_date' => date('m/d/Y'),
					'invoice_cm_distributions' => $i++,
					'qty' => $itemQty * -1,
					'item_id' => $invoiceItem->getSku(),
					'description' => $invoiceItem->getName(),
					'gl_account' => self::GL_ACCOUNT_ITEM,
					'unit_price' =>number_format($unitPrice,2,'.',''),
					'tax_type' => self::TAX_TYPE_ITEM,
					'amount' => $rowTotal,
				);
				
				
				$line_values = array_merge($common_values, $item_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}
			
			if ($has_tax_line) {
				$tax_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => 0,
					'description' => 'Salt Lake County Sales Tax',
					'gl_account' => self::GL_ACCOUNT_TAX,
					'tax_type' => self::TAX_TYPE_TAX,
					'amount' => ($order->getData('tax_amount')),
					'sales_tax_agency_id' => self::SALES_TAX_ID,
				);
				$line_values = array_merge($common_values, $tax_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}

			if ($has_ship_line) {
				$ship_values = array(
					'ship_date' => $shipTime, //use last item ship date
					'invoice_cm_distributions' => 0,
					'description' => 'Freight Amount',
					'gl_account' => self::GL_ACCOUNT_FRIEGHT,
					'tax_type' => self::TAX_TYPE_FRIEGHT,
					'amount' => ($order->getData('shipping_amount')),
				);
				$line_values = array_merge($common_values, $ship_values);
				$csv .= '"' . implode('","', $line_values) . '"' . "\r\n";
			}

			
		}

		return $csv;
	}

}
