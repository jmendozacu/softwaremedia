<?php

class OCM_Peachtree_Model_Csv extends Mage_Core_Model_Abstract
{

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
    
        if (!$this->getFrom() || !$this->getTo()) {
            return false;
        }
        
        $csv = '';

        $invoices = Mage::getModel('sales/order_invoice')->getCollection()
            ->addFieldToFilter('order.updated_at', array(
                'from' => $this->getFrom(),
                'to' => date('Y-m-d',strtotime($this->getTo()) + 60 * 60 * 24),
                'date' => true, // specifies conversion of comparison values
            )
            )
            ->addFieldToFilter('order.status', 'complete')
        ;
        
        $invoices->getSelect()

            ->joinLeft(
                'sales_flat_order as order',
                'order.entity_id = main_table.order_id',
                array(
                    'order_created_at'    => 'created_at',
                    'shipping_address_id' => 'shipping_address_id',
                    'is_virtual'          => 'is_virtual',
                    'discount_amount'     => 'discount_amount',
                    'applied_rule_ids'    => 'applied_rule_ids',
                    'coupon_rule_name'    => 'coupon_rule_name'
                )
            )
    
            ->joinLeft(
                'ocm_peachtree_referer as referer',
                'referer.order_id = main_table.order_id',
                array('referer_id')
            )
    
            ->joinLeft(
                'sales_flat_order_address as shippingaddress',
                'shippingaddress.entity_id = order.shipping_address_id',
                array(
                    'ship_region' => 'region')
            )
                       
                ->joinLeft(
                    'sales_flat_shipment_track as shipment_track',
                    'shipment_track.order_id = main_table.order_id',
                    array('ship_via' => 'title')
                    )
        ;        

        foreach ($invoices as $invoice) {
            
            $shipTime = false;
            
            //$items = $invoice->getAllItems();
            $items = Mage::getResourceModel('sales/order_invoice_item_collection')
                ->addFieldToFilter('main_table.parent_id',$invoice->getId())
                //->setInvoiceFilter($invoice->getId())
                ;
                
            $items->getSelect()
                ->joinLeft(
                    'sales_flat_shipment_item as shipment_item',
                    'shipment_item.order_item_id = main_table.order_item_id',
                    array('shipment_id' => 'parent_id')
                )
                ->joinLeft(
                    'sales_flat_shipment as shipment',
                    'shipment_item.parent_id = shipment.entity_id',
                    array('item_ship_date' => 'created_at')
                
                )
            ;
                
            $has_tax_line = ($invoice->getData('tax_amount')>0) ? 1 : 0;
            $has_ship_line = ($invoice->getData('shipping_amount')>0) ? 1 : 0;
            $has_promo_line = ($invoice->getData('discount_amount')!=0) ? 1 : 0;
            
            $shipVia = $invoice->getData('ship_via');
            $shipVia = str_replace("Federal Express", "Fed-Ex", $shipVia);
            $itemCount = 0;
            foreach($items as $item) {
            	$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            	if ($orderItem->getParentItemId())
            		continue;
				
				$itemCount++;
            }
            $common_values = array(
                'customer_id' => 'O'.date('my',strtotime( $invoice->getData('order_created_at') ) ),
                'invoice_id'  => $invoice->getData('increment_id'),
                'date'        => date('m/d/Y',strtotime( $invoice->getData('order_created_at') ) ),
                'ship_via'    => $shipVia,
                'ship_date'   => '', //item, tax, frieght
                'displayed_terms' => self::DISPLAYED_TERMS,
                'sales_rep_id'    => OCM_Peachtree_Model_Referer::getNameByCode( $invoice->getData('referer_id') ),
                'account_receivable' => self::ACCOUNT_RECEIVABLE,
                'sales_tax_id'  => ($has_tax_line) ? self::SALES_TAX_ID : '',
                'number_of_distributions' => $itemCount + $has_tax_line + $has_ship_line + $has_promo_line,
                'invoice_cm_distributions' => '', //item, tax, frieght
                'qty' => 0, //item
                'item_id' => '',//item 'sku'
                'description' => '', //item, tax, frieght - title
                'gl_account' => '', //item, tax, frieght - get from constant
                'unit_price' => 0, //item
                'tax_type' => '', //item, tax, frieght - get from constant
                'amount' => '', //item, tax, frieght - price x qty
                'sales_tax_agency_id' => ''
            );
                        
            if (OCM_Peachtree_Model_Referer::checkForUser( $invoice->getData('referer_id')))
            	$common_values['customer_id'] = $common_values['customer_id'] . 'W';
            	
            $i = 1;
            foreach($items as $item) {
            	$orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            	if ($orderItem->getParentItemId())
            		continue;
            	if (!$shipTime)
            		$shipTime = date('m/d/Y', strtotime( $item->getData('item_ship_date') ) );
                $item_values = array(
                    'ship_date'   => date('m/d/Y', strtotime( $item->getData('item_ship_date') ) ), 
                    'invoice_cm_distributions' => $i++,
                    'qty' => $item->getQty(),
                    'item_id' => $item->getSku(),
                    'description' => $item->getName(),
                    'gl_account' => self::GL_ACCOUNT_ITEM,
                    'unit_price' => $item->getPrice(),
                    'tax_type' => self::TAX_TYPE_ITEM,
                    'amount' => ($item->getPrice() * $item->getQty())*-1,
                );
                
                $line_values = array_merge($common_values,$item_values);
                $csv .= '"'.implode('","', $line_values).'"'."\r\n";
                
            }
            
            if ($has_tax_line) {
                
                $tax_values = array(
                    'ship_date'   => $shipTime, //use last item ship date
                    'invoice_cm_distributions' => 0,
                    'description' => 'Salt Lake County Sales Tax',
                    'gl_account' => self::GL_ACCOUNT_TAX,
                    'tax_type' => self::TAX_TYPE_TAX,
                    'amount' => ($invoice->getData('tax_amount'))*-1,
                    'sales_tax_agency_id' => self::SALES_TAX_ID,
                );
                $line_values = array_merge($common_values,$tax_values);
                $csv .= '"'.implode('","', $line_values).'"'."\r\n";
                
            }
            
            if ($has_ship_line) {
                
                $ship_values = array(
                    'ship_date'   => $shipTime, //use last item ship date
                    'invoice_cm_distributions' => 0,
                    'description' => 'Freight Amount',
                    'gl_account' => self::GL_ACCOUNT_FRIEGHT,
                    'tax_type' => self::TAX_TYPE_FRIEGHT,
                    'amount' => ($invoice->getData('shipping_amount'))*-1,
                );
                $line_values = array_merge($common_values,$ship_values);
                $csv .= '"'.implode('","', $line_values).'"'."\r\n";
                
            }
            
            if ($has_promo_line) {
                
                $promo_values = array(
                    'ship_date'   => $shipTime, //use last item ship date
                    'invoice_cm_distributions' => 0,
                    'description' => 'Promo: '.$invoice->getData('coupon_rule_name') ,
                    'gl_account' => self::GL_ACCOUNT_PROMO,
                    'tax_type' => self::TAX_TYPE_PROMO,
                    'amount' => ($invoice->getData('discount_amount'))*-1,
                );
                $line_values = array_merge($common_values,$promo_values);
                $csv .= '"'.implode('","', $line_values).'"'."\r\n";
                
            }
            
        }
                
        return $csv;
    }


}