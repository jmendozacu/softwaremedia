<?php
require_once(Mage::getBaseDir('lib') . '/FedEx/fedex-common.php');
ini_set("soap.wsdl_cache_enabled", "0");

class OCM_Fulfillment_Model_FedEx extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ocm_fulfillment/fedex');
    }
	
	protected function addDays($transitTime) {
		$transitTime = strtolower($transitTime);
		$search = array('_','one','two','three','four','five','six','seven','eight','nine');
		$replace = array(' ','1','2','3','4','5','6','7','8','9');
		$transitTime = str_replace($search,$replace,$transitTime);
		
		$shipDate = Mage::helper('ocm_fulfillment')->estimateShipDate();
		$deliveryDate = date('Y-m-d', strtotime('+' . $transitTime,strtotime($shipDate)));
	
		
		//If package ships over a sunday, add 1 day
		if (date('N', strtotime($shipDate)) + $methods[$shippingMethod] > 7)
			$deliveryDate = date('Y-m-d', strtotime('+1 days',strtotime($deliveryDate)));
		
		//If package is to be delivered on Sat, add 2 days
		if (date('N', strtotime($deliveryDate)) == 6 && !$this->getSaturday())
			$deliveryDate = date('Y-m-d', strtotime('+2 days',strtotime($deliveryDate)));

		//If package is to be delivered on Sun, add 1 day
		if (date('N', strtotime($deliveryDate)) == 7)
			$deliveryDate = date('Y-m-d', strtotime('+1 days',strtotime($deliveryDate)));
			
		return $deliveryDate;
	}
	
	//Return delivery estimate from FedEx API
    public function getEstimate() {
    	$fedExRates = array();
				
		$shipDate = Mage::helper('ocm_fulfillment')->estimateShipDate();
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
		$request['RequestedShipment']['ShipTimestamp'] = date('c', strtotime($shipDate));
		//$request['RequestedShipment']['ServiceType'] = $method; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['TotalInsuredValue']=array(
			'Ammount'=>100,
			'Currency'=>'USD'
		);
		$request['RequestedShipment']['Shipper'] = $this->addShipper();
		$request['RequestedShipment']['Recipient'] = $this->getRecipient();
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['RequestedPackageLineItems'] = $this->addPackageLineItem1();
		
		try {
			if(setEndpoint('changeEndpoint')){
				$newLocation = $client->__setLocation(setEndpoint('endpoint'));
			}
			
			$response = $client -> getRates($request);

		    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){  	
		    	
				
					
				foreach ($response -> RateReplyDetails as $rateReply){
			        if(array_key_exists('DeliveryTimestamp',$rateReply)){
			        	$deliveryDate= date('Y-m-d',strtotime($rateReply->DeliveryTimestamp));
			        }else if(array_key_exists('TransitTime',$rateReply)){
			        	$deliveryDate= $this->addDays($rateReply->TransitTime);
			        }else {
			        	$deliveryDate=NULL;
			        }
			        $serviceType = $rateReply -> ServiceType;
					if ($serviceType == 'GROUND_HOME_DELIVERY')
						$serviceType = 'FEDEX_GROUND';
						
			        if ($deliveryDate)
			        	$fedExRates[$serviceType] = date('M jS',strtotime($deliveryDate));
			        	
			        if ($serviceType == 'STANDARD_OVERNIGHT') {
			        	if (date('N', strtotime($deliveryDate)) == 1)
			        		$satDate = date('Y-m-d', strtotime('+2 days',strtotime($deliveryDate)));
			        	else 
			        		$satDate = $deliveryDate;
			        		
				        $fedExRates['SATURDAY_OVERNIGHT'] = date('M jS',strtotime($satDate));
			        }
			        
				}
		        
		        return $fedExRates;
		    }else{
		    	return false;
		        //printError($client, $response);
		    } 
		} catch (SoapFault $exception) {
			return false;
		   //printFault($exception, $client);        
		}
		
		
	}
    	
	public function addShipper(){
		$this->setShipper(array(
			'Address' => array(
				'StateOrProvinceCode' => 'UT',
				'PostalCode' => '84095',
				'CountryCode' => 'US'
			)
		));
		
		return $this->getShipper();
	}
	public function addRecipient($state,$zip,$country){
		$this->setRecipient(array(
			'Address' => array(
				'StateOrProvinceCode' => $state,
				'PostalCode' => $zip,
				'CountryCode' => $country,
				'Residential' => true
			)
		));                                   
	}
	public function addLabelSpecification(){
		$labelSpecification = array(
			'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
			'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
			'LabelStockType' => 'PAPER_7X4.75'
		);
		return $labelSpecification;
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
	
	public function getFedExMethod($method) {
		$methods = $this->getMethods();
		if (array_key_exists($method,$methods))
			return $methods[$method];
		else
			return false;
		
	}
	
	protected function getMethods() {
		$methods = array(
			'Free_Budget' => 'FEDEX_GROUND',
			'Express' => 'FEDEX_EXPRESS_SAVER',
			'Free_Express' => 'FEDEX_EXPRESS_SAVER',
			'Expedited_Air' => 'FEDEX_2_DAY',
			'Free_Expedited_Air' => 'FEDEX_2_DAY',
			'Standard_Overnight' => 'STANDARD_OVERNIGHT',
			'Overnight_Saturday' => 'SATURDAY_OVERNIGHT',
			'Priority_Overnight' => 'PRIORITY_OVERNIGHT');
			
		return $methods;
	}
	
}