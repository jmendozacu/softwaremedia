<?php
		 
class OCM_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

	public function estimateDelivery($shippingMethod) {
		$methods = $this->getMethods();
		$shipDate = $this->estimateShipDate($shippingMethod);
		$saturday = false;
		
		if (!array_key_exists($shippingMethod,$methods))
			return false;
			
		$deliveryDate = date('Y-m-d', strtotime('+' . $methods[$shippingMethod] . ' days',strtotime($shipDate)));
		
		
		if ($shippingMethod == 'productmatrix_Overnight_Saturday') 
			$saturday = true;
		
		//If package ships over a sunday, add 1 day
		if (date('N', strtotime($shipDate)) + $methods[$shippingMethod] > 7)
			$deliveryDate = date('Y-m-d', strtotime('+1 days',strtotime($deliveryDate)));
		
		//If package is to be delivered on Sat, add 2 days
		if (date('N', strtotime($deliveryDate)) == 6 && !$saturday)
			$deliveryDate = date('Y-m-d', strtotime('+2 days',strtotime($deliveryDate)));

		//If package is to be delivered on Sun, add 1 day
		if (date('N', strtotime($deliveryDate)) == 7)
			$deliveryDate = date('Y-m-d', strtotime('+1 days',strtotime($deliveryDate)));

		return $deliveryDate;
	}
	
	public function methodExists($shippingMethod) {
		$methods = $this->getMethods();
		return array_key_exists($shippingMethod,$methods);
	}
	
	public function estimateShipDate($shippingMethod ) {
		$methods = $this->getMethods();
		
		if (date('N') == 6)
			$estimate = date('Y-m-d', strtotime('+2 days'));
		elseif (date('N') == 7)
			$estimate = date('Y-m-d', strtotime('+1 days'));
		elseif (date('H') > 15)
			$estimate = date('Y-m-d', strtotime('+1 days'));
		else
			$estimate = date('Y-m-d');
		
		return $estimate;
	}
	
	
	public function getFedExEstimate() {
		$time = now();
		$path_to_wsdl = Mage::getBaseDir('lib') . "/FedEx/RateService_v16.wsdl";
		
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
		
		$request['WebAuthenticationDetail'] = array(
			'UserCredential' =>array(
				'Key' => getProperty('key'), 
				'Password' => getProperty('password')
			)
		); 
		$request['ClientDetail'] = array(
			'AccountNumber' => getProperty('shipaccount'), 
			'MeterNumber' => getProperty('meter')
		);
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request using PHP ***');
		$request['Version'] = array(
			'ServiceId' => 'crs', 
			'Major' => '16', 
			'Intermediate' => '0', 
			'Minor' => '0'
		);
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		$request['RequestedShipment']['ServiceType'] = 'FEDEX_GROUND'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['TotalInsuredValue']=array(
			'Ammount'=>100,
			'Currency'=>'USD'
		);
		$request['RequestedShipment']['Shipper'] = $this->addShipper();
		$request['RequestedShipment']['Recipient'] = $this->addRecipient();
		$request['RequestedShipment']['ShippingChargesPayment'] = $this->addShippingChargesPayment();
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['RequestedPackageLineItems'] = $this->addPackageLineItem1();
		
		
		
		try {
			if(setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation(setEndpoint('endpoint'));
			}
			
			$response = $client -> getRates($request);
		        
		    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){  	
		    	$rateReply = $response -> RateReplyDetails;
		    	echo '<table border="1">';
		        echo '<tr><td>Service Type</td><td>Amount</td><td>Delivery Date</td></tr><tr>';
		    	$serviceType = '<td>'.$rateReply -> ServiceType . '</td>';
		    	foreach($rateReply->RatedShipmentDetails as $rate) {
			    	//var_dump($rate);
		    	}
		    	//var_dump($rate);
		    	//die();
		        $amount = '<td>$' . number_format($rate->ShipmentRateDetail->TotalNetCharge->Amount,2,".",",") . '</td>';
		        if(array_key_exists('DeliveryTimestamp',$rateReply)){
		        	$deliveryDate= '<td>' . $rateReply->DeliveryTimestamp . '</td>';
		        }else if(array_key_exists('TransitTime',$rateReply)){
		        	$deliveryDate= '<td>' . $rateReply->TransitTime . '</td>';
		        }else {
		        	$deliveryDate='<td>&nbsp;</td>';
		        }
		        echo $serviceType . $amount. $deliveryDate;
		        echo '</tr>';
		        echo '</table>';
		        
		        printSuccess($client, $response);
		    }else{
		        printError($client, $response);
		    } 
		} catch (SoapFault $exception) {
		   printFault($exception, $client);        
		}
		
		echo now() - $time;
	}
	
	protected function getMethods() {
		$methods = array(
			'productmatrix_Free_Electronic_Delivery' => 1,
			'productmatrix_Free_Budget_(5-9_Days)' => 7,
			'productmatrix_Express_(3-5_Days)' => 3,
			'productmatrix_Free_Express_(3-5_Days)' => 3,
			'productmatrix_Expedited_Air_(2_Days)' => 2,
			'productmatrix_Free_Expedited_Air_(2_Days)' => 2,
			'productmatrix_Standard_Overnight' => 1,
			'productmatrix_Priority_Overnight' => 1,
			'productmatrix_Overnight_Saturday' => 1,
			'productmatrix_2_Day_Air' => 2,
			'productmatrix_Overnight' => 1,
			'productmatrix_Expedited_Electronic_Processing' => 1);
			
		return $methods;
	}
	public function updateStock($product) {
		$price_array = array();
		$all_price = array();
		$qty = 0;
		
		//Get subtitution links
		$links = $product->getSubstitutionLinkCollection();

		$stock_model = Mage::getModel('cataloginventory/stock_item');
		$hasResult = false;
		$new_stock_model = Mage::getModel('cataloginventory/stock_item');
		
		//Mage::log('Updating Stock: ' . $product->getSku(),null,'stock.log');
		
		foreach (array('techdata','synnex','ingram') as $warehouse_name) {
			if (is_numeric($product->getData($warehouse_name.'_qty')) || is_numeric($product->getData($warehouse_name.'_price')))
				$hasResult = true;
				
			if($product->getData($warehouse_name.'_qty') > 0) {
				$price_array[] = $product->getData($warehouse_name.'_price');
				$qty += $product->getData($warehouse_name.'_qty');
			} else {
				if ($product->getData($warehouse_name.'_price'))
					$all_price[] = $product->getData($warehouse_name.'_price');
			}  
		}
		
		/*
		Regarding prods where cost=0 in PT
		If PT qty is greater than or equal to 1, then it should have PT cost = 0
		It PT qty is less than or equal to 0 (i did see a few negative qtys in pt) then it should not be taking PT cost as 0 (should be 		taking ingram/synnex/techdata cost or if all those blank, then subs cost)
		*/
		
		$cost = false;
		
		$qty += $product->getData('pt_qty');
		if ($qty)
			$hasResult = true;
			
		//If no peachtree cost, or no pt qty, use cost from warehouse if available
		if (!is_numeric($product->getData('pt_avg_cost')) || (!$product->getData('pt_qty') || $product->getData('pt_qty') <= 0)) {
			//If no prices from warehouses with QTY, use all prices
			if (count($price_array) == 0 && count($all_price) > 0)
				$price_array = $all_price;
			asort($price_array);
			$lowest_cost = $price_array[0];
			if ($lowest_cost > 0)
				$cost = $lowest_cost;
		} else {
			//Use PT_avg_cost if not 0, or if 0 and no pt_qty
			//echo $product->getData('pt_avg_cost');
			
			if ((is_numeric($product->getData('pt_qty')) && $product->getData('pt_qty') > 0) || $product->getData('pt_avg_cost') > 0) {
				$cost = $product->getData('pt_avg_cost');
			}
			//die();
		}
		
		//In cases where there was pt_avg_cost but no pt_qty update cost to reflect pt_avg cost
		if (!$cost && $product->getData('pt_qty'))
			$cost = $product->getData('pt_avg_cost');
			
		//Use cost_override if available
		if ($product->getData('cost_override'))
			$cost = $product->getData('cost_override');
			
			
		$stock_model->loadByProduct($product->getId());
		
		//Add up QTY 
		foreach($links as $link) {
			$item = Mage::getModel('catalog/product')->load($link->getLinkedProductId());
			$subQty = $link->getQty();
			$sub_model = Mage::getModel('cataloginventory/stock_item');
			$sub_model->loadByProduct($item->getId());
			$qty += floor($sub_model->getData('qty') / $subQty);
			
			if ($sub_model->getData('manage_stock') == 0)
				$qty = 9999;
			
			
			foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
				if (is_numeric($product->getData($warehouse_name.'_qty')) || is_numeric($product->getData($warehouse_name.'_price')))
					$hasResult = true;
					
				$prod = Mage::getModel('catalog/product')->load($item->getId());
				//$qty+=$prod->getData($warehouse_name.'_qty');
				if ((!$cost && $prod->getData('cost')) || ($cost && $prod->getData('cost') < $cost))
					$cost = $prod->getData('cost') * $link->getQty();
			}
			//$qty+=$item->getData('pt_qty');
		}
		
		//if ($cost) 
			$product->setData('cost',$cost);
		
		//Additional rules for physical items
		$stock_model->setData('backorders',0);
		if($hasResult && (!$qty || $qty < 0)) {
			$qty = 9999;
			
			//If physical set backorder status
			if ($product->getData('package_id')==1085 || $product->getData('package_id')==1216) {
				$stock_model->setData('backorders',1);
				$stock_model->setData('use_config_backorders',0);
				$qty = 0;
			}
		}elseif ($product->getData('package_id')==1084 && $hasResult) {
			$qty = 9999;
		}
				
		//Add stock of simple products
		if ($product->getTypeId() == 'configurable') {
			//echo "config";
			
			$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
			$col = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
			foreach($col as $simple_product){
				$new_stock_model->loadByProduct($simple_product->getId());
				$qty += $new_stock_model->getData('qty');
			}
		}

		if($qty || $stock_model->getData('backorders') == 1) {
			$stock_model->setData('is_in_stock',1);
			//Mage::log('IN STOCK: ' . $qty,null,'stock.log');
		} else {
			$stock_model->setData('is_in_stock',0);
			//Mage::log('OUT OF STOCK: ' . $qty,null,'stock.log');
		}
		
		if ($qty > 9999)
			$qty = 9999;
		
		$stock_model->setData('qty',$qty);
		try {
			$product->save();
			$stock_model->save();
		} catch (Exception $e) {
			Mage::log($e->getMessage(),null,'fulsave.log');
		}
	}
	
	public function addShipper(){
		$shipper = array(
			'Contact' => array(
				'PersonName' => 'Jeff Losee',
				'CompanyName' => 'SoftwareMedia',
				'PhoneNumber' => '9012638716'
			),
			'Address' => array(
				'StreetLines' => array('916 S. Main St'),
				'City' => 'Salt Lake City',
				'StateOrProvinceCode' => 'UT',
				'PostalCode' => '84095',
				'CountryCode' => 'US'
			)
		);
		return $shipper;
	}
	public function addRecipient(){
		$recipient = array(
			'Contact' => array(
				'PersonName' => 'Recipient Name',
				'CompanyName' => 'Company Name',
				'PhoneNumber' => '9012637906'
			),
			'Address' => array(
				'StreetLines' => array('Address Line 1'),
				'City' => 'Richmond',
				'StateOrProvinceCode' => 'BC',
				'PostalCode' => 'V7C4V4',
				'CountryCode' => 'CA',
				'Residential' => false
			)
		);
		return $recipient;	                                    
	}
	public function addShippingChargesPayment(){
		$shippingChargesPayment = array(
			'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
			'Payor' => array(
				'ResponsibleParty' => array(
					'AccountNumber' => getProperty('billaccount'),
					'CountryCode' => 'US'
				)
			)
		);
		return $shippingChargesPayment;
	}
	public function addLabelSpecification(){
		$labelSpecification = array(
			'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
			'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
			'LabelStockType' => 'PAPER_7X4.75'
		);
		return $labelSpecification;
	}
	public function addSpecialServices(){
		$specialServices = array(
			'SpecialServiceTypes' => array('COD'),
			'CodDetail' => array(
				'CodCollectionAmount' => array(
					'Currency' => 'USD', 
					'Amount' => 150
				),
				'CollectionType' => 'ANY' // ANY, GUARANTEED_FUNDS
			)
		);
		return $specialServices; 
	}
	public function addPackageLineItem1(){
		$packageLineItem = array(
			'SequenceNumber'=>1,
			'GroupPackageCount'=>1,
			'Weight' => array(
				'Value' => 50.0,
				'Units' => 'LB'
			),
			'Dimensions' => array(
				'Length' => 108,
				'Width' => 5,
				'Height' => 5,
				'Units' => 'IN'
			)
		);
		return $packageLineItem;
	}

}