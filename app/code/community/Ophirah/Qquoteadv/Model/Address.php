<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ophirah_Qquoteadv_Model_Address extends Mage_Sales_Model_Quote_Address
{
    CONST DEFAULT_DEST_STREET = -1;
    /**
     * DEPRACTED
     * Since C2Qv4.2.1
     * CounrtyId() can be used from
     * default address()
     * No need to call $_countryId
     */
//    protected $_countryId = null;
    protected $_quote = null;
    protected $_rates = null;
    
    protected $_itemsQty = null;
    
    public $_shippingRates = null;
    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'ophirah_qquoteadv_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quoteadv_address';

    /**
     * Override resource as we are defining the field ourselves
     */
    protected function _construct()
    {
    }

    /**
     * Init mapping array of short fields to its full names
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _initOldFieldsMap()
    {
        return $this;
    }

    /**
     * Initialize quote identifier before save
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        return $this;
    }
    
    /**
     * Declare adress quote model object
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {   
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }
  
    /**
     * Retrieve address items collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getItemsCollection()
    {
        if (is_null($this->_items)) {
          $items = $this->getAllItems();
          foreach($items as $item){
              $item->setAddress($this);
              $item->setQuote($this->getQuote());
          }
        }
        return $items;
    }

    /**
     * Get all available address items
     *
     * @return array
     */
    public function getAllItems()
    {      
        return $this->getQuote()->getAllRequestItems();
    }  

    /**
     * Get combined weight of the
     * quote products
     * 
     * @return \Ophirah_Qquoteadv_Model_Address
     */
    public function getWeight()
    {   
        if($this->getQuote() instanceof Ophirah_Qquoteadv_Model_Qqadvcustomer){
            return $this->getQuote()->getWeight();
        }
        
        return $this;
    }

    /**
     * Retrieve item quantity by id
     *
     * @param int $itemId
     * @return float|int
     */
    public function getItemQty($itemId = 0)
    {        
        if($this->_itemsQty == null){
            $this->_itemsQty = 0;
            $items = $this->getAllItems();
            foreach($items as $item){
                // skip non visible items
                if($item->getParentItem()){
                    continue;
                }
                // If items get shipped seperatly
                if($item->isShipSeparately() && $item->getData('qty_options')){
                    foreach($item->getData('qty_options') as $optionItem){
                        $this->_itemsQty += $optionItem->getProduct()->getData('qty');
                    }
                } else {
                    $this->_itemsQty +=  $item->getData('qty');    
                }
            }
        }

        return $this->_itemsQty;        
    }    
    
    
    /**
     * Add item to address
     *
     * @param   Ophirah_Qquoteadv_Model_Requestitemt $item
     * @param   int $qty
     * @return  Mage_Sales_Model_Quote_Address
     */
    public function addItem(Mage_Sales_Model_Quote_Item_Abstract $item, $qty=null)
    {
     
        return $this;
    }
    
    function getId(){
      return $this->getQuote()->getId();
    }
    
    
    public function getCollectShippingRates(){
        return true;
    }

    /**
     * Retrieve collection of quote shipping rates
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getShippingRatesCollection()
    {
        if( $this->_rates == null) {
            $this->_rates = array();
            if($this->getQuote()->getIsCustomShipping()) {
               
               $price =  $this->getQuote()->getShippingBasePrice();
               
               if($this->getQuote()->getShippingType() == "I") {
                 $price = ($price * $this->getQuote()->getItemsQty());  
               } 
                
               $rate = Mage::getModel('qquoteadv/shippingrate');
               $rate->setData('carrier', 'flatrate');
               $rate->setData('carrier_title', 'Flat Rate');
               $rate->setData('price', $price);
               $rate->setData('cost', $price);
               $rate->setData('method', 'flatrate');
               $rate->setData('method_title', 'Fixed');   
               $quoteRate = Mage::getModel('sales/quote_address_rate')->importShippingRate($rate); 
               $this->_rates = array($quoteRate);               
               
            } else {
                $this->collectShippingRates();
                $this->_rates = Mage::getModel('qquoteadv/quoteshippingrate')->getCollection()
                                        ->addFieldToFilter('address_id', array('eq' => $this->getData('address_id')));
            
                if ($this->hasNominalItems(false)) {
                    $this->_rates->setFixedOnlyFilter(true);
                }
                if ($this->getId()) {
                    foreach ($this->_rates as $rate) {
                        $rate->setAddress($this);
                    }
                }
                
                /* // Not need for now
                $this->_rates = Mage::getModel('sales/quote_address_rate')->getCollection()
                    ->setAddressFilter($this->getId());
                if ($this->getQuote()->hasNominalItems(false)) {
                    $this->_rates->setFixedOnlyFilter(true);
                }
                if ($this->getId()) {
                    foreach ($this->_rates as $rate) {                      
                        $rate->setAddress($this);
                    }
                }
                
                return $this->_rates;
                */
           }
        }

        return $this->_rates;
    }

    
      public function collectShippingRates()
    {
        if (!$this->getCollectShippingRates()) {
            return $this;
        }
       
        $this->removeAllShippingRates();

        if (!$this->getCountryId()) {
            return $this;
        }
        $found = $this->requestShippingRates();
        if (!$found) {
            $this->setShippingAmount(0)
                ->setBaseShippingAmount(0)
                ->setShippingMethod('')
                ->setShippingDescription('');
        }
        return $this;
    }
    
    /**
     * Request shipping rates for entire address or specified address item
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     */
    public function requestShippingRates(Mage_Sales_Model_Quote_Item_Abstract $item = null)
    {
        /** @var $request Mage_Shipping_Model_Rate_Request */
        $request = Mage::getModel('shipping/rate_request');
        $request->setAllItems($item ? array($item) : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        /**
         * need to call getStreet with -1
         * to get data in string instead of array
         */
        $request->setDestStreet($this->getStreet(self::DEFAULT_DEST_STREET));
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageValueWithDiscount = $item
            ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount()
            : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageValueWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());

        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty() );       

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item
            ? $item->getBaseRowTotal()
            : $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());

        /**
         * Store and website identifiers need specify from quote
         */
        /*$request->setStoreId(Mage::app()->getStore()->getId());
        $request->setWebsiteId(Mage::app()->getStore()->getWebsiteId());*/

        $request->setStoreId($this->getQuote()->getStore()->getId());
        $request->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->getQuote()->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->getQuote()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());

        // OLD CODE
//        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax());
        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax() + $this->getBaseExtraTaxAmount());
        
        $result = Mage::getModel('shipping/shipping')->collectRates($request)->getResult();
        
        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();

            // Remove existing rates
            if($shippingRates){
                Mage::getModel('qquoteadv/quoteshippingrate')->removeQuoteRates($this->getData('address_id'));
            }
            
            foreach ($shippingRates as $shippingRate) {
                $rate = Mage::getModel('sales/quote_address_rate')
                    ->importShippingRate($shippingRate);

                if (!$item) {
                    $this->addQuoteShippingRate($rate);
                }              

                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        /**
                         * possible bug: this should be setBaseShippingAmount(),
                         * see Mage_Sales_Model_Quote_Address_Total_Shipping::collect()
                         * where this value is set again from the current specified rate price
                         * (looks like a workaround for this bug)
                         */

                        $this->setShippingAmount($rate->getPrice());
                    }

                    $found = true;
                }

            }
        }

        $found = true;
       
        return $found;
    }
    
    /**
     * Add / Update Quote Shipping rate table
     * 
     * @param Mage_Sales_Model_Quote_Address_Rate $rate
     * @return boolean
     */
    public function addQuoteShippingRate(Mage_Sales_Model_Quote_Address_Rate $rate){


        
        // Add new shippingdata
        $newRate = Mage::getModel('qquoteadv/quoteshippingrate');
        $newRate->addData($rate->getData());
        $newRate->setData('address_id',  $this->getData('address_id'));
        $newRate->setData('created_at', NOW());
        $newRate->setData('updated_at', NOW());
        $newRate->save();
        return;
    }

    public function getStreet($line = 0){
        return $this->getQuote()->getStreet($line);
    }
    
    public function getRegionId(){
        return $this->getQuote()->getRegionId();
    }
    
    /**
     * DEPRACTED
     * From v4.2.1. The country Id is
     * within the address()
     * No need to call the ShippingCounrtyId()
     */
    public function getCountryId(){
        
        return $this->getQuote()->getCountryId();
/*        
        if($this->_countryId == null) {
            $this->_countryId = $this->getQuote()->getShippingCountryId();    
        }
        return $this->_countryId;
 * 
 */
    }
    
    public function getCity(){
        return $this->getQuote()->getCity();    
        
    }
    
    public function getPostcode(){
         return $this->getQuote()->getPostcode();    
    }
    
   
     /**
     * Retrieve all address shipping rates
     *
     * @return array
     */
    public function getAllShippingRates()
    {
        $rates = array();
        foreach ($this->getShippingRatesCollection() as $rate) {
          
             $rates[] = $rate;
        }
        return $rates;
    }

    /**
     * Get totals collector model
     *
     * @return Mage_Sales_Model_Quote_Address_Total_Collector
     */
    public function getTotalCollector()
    {
        if ($this->_totalCollector === null) {            
            $this->_totalCollector = Mage::getSingleton(
                'sales/quote_address_total_collector',
                array('store'=>$this->getQuote()->getStore())
            );
        }
        return $this->_totalCollector;
    }

    /**
     * Retrieve total models
     *
     * @deprecated
     * @return array
     */
    public function getTotalModels()
    {
        return $this->getTotalCollector()->getRetrievers();
    }

    /**
     * Collect address totals
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function collectTotals()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_before', array($this->_eventObject => $this));
        foreach ($this->getTotalCollector()->getCollectors() as $model) {              
            $model->collect($this);
        }
        Mage::dispatchEvent($this->_eventPrefix . '_collect_totals_after', array($this->_eventObject => $this));
        
        // update address table
        if($this->getAddressId()){
            $addresses = Mage::helper('qquoteadv/address')->getAddressCollection($this->getData('quote_id'));
            if($addresses){
                foreach($addresses as $address){
                    if($address->getData('address_type') == $this->getData('address_type')){ 
                        $address->addData($this->getData());
                        $address->save();
                    }
                }
            }
        }
        
        return $this;
    }
    
    public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        if (!Mage::getStoreConfigFlag('sales/minimum_order/active', $storeId)) {
            return true;
        }

        if ($this->getQuote()->getIsVirtual() && $this->getAddressType() == Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        }
        elseif (!$this->getQuote()->getIsVirtual() && $this->getAddressType() != Ophirah_Qquoteadv_Helper_Address::ADDRESS_TYPE_SHIPPING) {
            return true;
        }

        $amount = Mage::getStoreConfig('sales/minimum_order/amount', $storeId);
        if ($this->getBaseSubtotalWithDiscount() < $amount) {
            return false;
        }
        return true;
    }

   
    public function setShippingAmount($value, $alreadyExclTax = false)
    {
        return $this->getQuote()->setData('shipping_amount', $value);
    }

    /**
     * Set base shipping amount
     *
     * @param float $value
     * @param bool $alreadyExclTax
     * @return Mage_Sales_Model_Quote_Address
     */
    public function setBaseShippingAmount($value, $alreadyExclTax = false)
    {
        return $this->getQuote()->setData('base_shipping_amount', $value);
    }
    
    public function getFreeShipping(){
        return $this->getQuote()->getFreeShipping();
    }
    
    public function getShippingMethod(){
        return $this->getQuote()->getAddressShippingMethod();
    }
    
    public function getShippingDescription(){
       return $this->getQuote()->getAddressShippingDescription();
    }
    
    public function setShippingDescription($desc){
       return $this->getQuote()->setAddressShippingDescription($desc);
    }
    
    public function removeAllShippingRates()
    {
        /*foreach ($this->getShippingRatesCollection() as $rate) {
            $rate->isDeleted(true);
        }*/
        return $this;
    }   

}
