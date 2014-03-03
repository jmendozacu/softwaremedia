<?php

class Ophirah_Qquoteadv_Model_Quoteshippingrate
    extends Mage_Core_Model_Abstract
{
    // from Mage_Shipping_Model_Rate_Abstract
    static protected $_instances;
    
    public $carrier_sort_order;
    
    /**
     * Basic Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('qquoteadv/quoteshippingrate');
    }
    
    /**
     * Modified from
     * Mage_Shipping_Model_Rate_Abstract
     * 
     * @return string
     */
    public function getCarrierInstance()
    {
        $code = $this->getCarrier();
        if (!isset(self::$_instances[$code])) {
            self::$_instances[$code] = Mage::getModel('shipping/config')->getCarrierInstance($code);
        }
        return self::$_instances[$code];
    }
    
    /**
     * Retrieve all grouped shipping rates
     * Modified from Mage_Sales_Model_Quote_Address
     *
     * @return array
     */
    public function getGroupedAllShippingRates(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote)
    {
        $rates = array();
        // modified loop
        foreach ($quote->getAddress()->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted() && $rate->getCarrierInstance()) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = array();
                }

                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $rate->getCarrierInstance()->getSortOrder();
            }
            

        }       
        uasort($rates, array($this, '_sortRates'));
        
        return $rates;
    }
    
    /**
     * Sort rates recursive callback
     * Modified from Mage_Sales_Model_Quote_Address
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortRates($a, $b)
    {
        if ((int)$a[0]->carrier_sort_order < (int)$b[0]->carrier_sort_order) {
            return -1;
        } elseif ((int)$a[0]->carrier_sort_order > (int)$b[0]->carrier_sort_order) {
            return 1;
        } else {
            return 0;
        }
    }
    
    public function removeQuoteRates($addressId){
        $collection = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                        ->addFieldToFilter('address_id', array('eq' => $addressId));

        // Delete current rates
        if($collection){
            foreach($collection as $deleteRate){
                $deleteRate->delete();
            }
        }
    }
    
    public function getShippingRatesList(Ophirah_Qquoteadv_Model_Qqadvcustomer $quote){
        
        $groupedShippingRates = $this->getGroupedAllShippingRates($quote);
        
        // Build list
        $shippingList   = array();
        $itemCount      = 0;
        foreach($groupedShippingRates as $shippingRates):
            $carrierTitle = $shippingRates[0]->getData('carrier_title');
            $itemCount ++;
            foreach($shippingRates as $shiprate){
                $shippingList[$carrierTitle][$itemCount]['code']         = $shiprate->getData('code');
                $shippingList[$carrierTitle][$itemCount]['method_list']  = $shiprate->getData('method_title');
                $shippingList[$carrierTitle][$itemCount]['price']        = $shiprate->getData('price');
                $itemCount ++;
            }
        endforeach;
        
        return array('itemCount' => $itemCount, 'shippingList' => $shippingList);
        
    }
}
