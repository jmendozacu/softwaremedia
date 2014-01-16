<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Quote_Item
{
    /** @var Ess_M2ePro_Model_Magento_Quote */
    private $quoteBuilder = NULL;

    /** @var Ess_M2ePro_Model_Order_Item_Proxy */
    private $proxyItem = NULL;

    /** @var Mage_Catalog_Model_Product */
    private $product = NULL;

    /** @var Mage_GiftMessage_Model_Message */
    private $giftMessage = NULL;

    private $channelCurrencyPrice = 0;

    // ########################################

    public function setQuoteBuilder(Ess_M2ePro_Model_Magento_Quote $quoteBuilder)
    {
        $this->quoteBuilder = $quoteBuilder;

        return $this;
    }

    // ########################################

    public function setProxyItem(Ess_M2ePro_Model_Order_Item_Proxy $proxyItem)
    {
        $this->proxyItem = $proxyItem;

        return $this;
    }

    // ########################################

    public function getProduct()
    {
        if (!is_null($this->product)) {
            return $this->product;
        }

        if ($this->proxyItem->getMagentoProduct()->isGroupedType()) {
            $this->product = $this->getAssociatedGroupedProduct();

            if (is_null($this->product)) {
                throw new Exception('There is no associated products found for grouped product.');
            }
        } else {
            $this->product = $this->proxyItem->getProduct();

            if ($this->proxyItem->getMagentoProduct()->isBundleType()) {
                $this->product->setPriceType(Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_PARENT);
            }
        }

        // tax class id should be set before price calculation
        $this->product->setTaxClassId($this->getProductTaxClassId());

        $price = $this->getBaseCurrencyPrice();
        $this->product->setPrice($price);
        $this->product->setSpecialPrice($price);

        return $this->product;
    }

    //-----------------------------------------

    private function getAssociatedGroupedProduct()
    {
        $associatedProducts = $this->proxyItem->getAssociatedProducts();
        $associatedProductId = reset($associatedProducts);

        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->quoteBuilder->getQuote()->getStoreId())
            ->load($associatedProductId);

        return $product->getId() ? $product : null;
    }

    // ########################################

    /**
     * Return product price without conversion to store base currency
     *
     * @return float
     */
    public function getChannelCurrencyPrice()
    {
        $this->calculateChannelCurrencyPrice();

        return $this->channelCurrencyPrice;
    }

    /**
     * Return product price converted to store base currency
     *
     * @return float
     */
    private function getBaseCurrencyPrice()
    {
        $this->calculateChannelCurrencyPrice();

        $currency = $this->quoteBuilder->getProxyOrder()->getCurrency();
        $store    = $this->quoteBuilder->getQuote()->getStore();
        $price    = $this->channelCurrencyPrice;

        if (in_array($currency, $store->getAvailableCurrencyCodes(true))) {
            $currencyConvertRate = $store->getBaseCurrency()->getRate($currency);
            $currencyConvertRate == 0 && $currencyConvertRate = 1;
            $price = $price / $currencyConvertRate;
        }

        return $price;
    }

    /**
     * Calculate product price based on tax information and account settings
     */
    private function calculateChannelCurrencyPrice()
    {
        if ($this->channelCurrencyPrice > 0) {
            return;
        }

        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');
        $this->channelCurrencyPrice = $this->proxyItem->getPrice();

        if ($this->needToAddTax()) {
            $this->channelCurrencyPrice += $taxCalculator
                ->calcTaxAmount($this->channelCurrencyPrice, $this->proxyItem->getTaxRate(), false, false);
        } elseif ($this->needToSubtractTax()) {
            $this->channelCurrencyPrice -= $taxCalculator
                ->calcTaxAmount($this->channelCurrencyPrice, $this->proxyItem->getTaxRate(), true, false);
        }

        $this->channelCurrencyPrice = round($this->channelCurrencyPrice, 2);
    }

    private function needToAddTax()
    {
        return $this->quoteBuilder->getProxyOrder()->isTaxModeNone() && $this->proxyItem->hasTax();
    }

    private function needToSubtractTax()
    {
        if (!$this->quoteBuilder->getProxyOrder()->isTaxModeChannel() &&
            !$this->quoteBuilder->getProxyOrder()->isTaxModeMixed()) {
            return false;
        }

        if (!$this->proxyItem->hasVat()) {
            return false;
        }

        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');
        $store = $this->quoteBuilder->getQuote()->getStore();

        $request = new Varien_Object();
        $request->setProductClassId($this->getProduct()->getTaxClassId());

        return $this->proxyItem->getTaxRate() != $taxCalculator->getStoreRate($request, $store);
    }

    //-----------------------------------------

    private function getProductTaxClassId()
    {
        $proxyOrder = $this->quoteBuilder->getProxyOrder();
        $taxRate = $this->proxyItem->getTaxRate();
        $hasRatesForCountry = Mage::getSingleton('M2ePro/Magento_Tax_Helper')->hasRatesForCountry(
            $this->quoteBuilder->getQuote()->getShippingAddress()->getCountryId()
        );

        if ($proxyOrder->isTaxModeNone()
            || ($proxyOrder->isTaxModeChannel() && $taxRate == 0)
            || ($proxyOrder->isTaxModeMagento() && !$hasRatesForCountry)
        ) {
            return Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE;
        }

        if ($proxyOrder->isTaxModeMagento()
            || $taxRate == 0
            || $taxRate == $this->getProductTaxRate()
        ) {
            return $this->getProduct()->getTaxClassId();
        }

        // Create tax rule according to channel tax rate
        // -------------------------
        /** @var $taxRuleBuilder Ess_M2ePro_Model_Magento_Tax_Rule_Builder */
        $taxRuleBuilder = Mage::getModel('M2ePro/Magento_Tax_Rule_Builder');
        $taxRuleBuilder->buildTaxRule(
            $taxRate,
            $this->quoteBuilder->getQuote()->getShippingAddress()->getCountryId(),
            $this->quoteBuilder->getQuote()->getCustomerTaxClassId()
        );

        $taxRule = $taxRuleBuilder->getRule();
        $productTaxClasses = $taxRule->getProductTaxClasses();
        // -------------------------

        return array_shift($productTaxClasses);
    }

    private function getProductTaxRate()
    {
        /** @var $taxCalculator Mage_Tax_Model_Calculation */
        $taxCalculator = Mage::getSingleton('tax/calculation');

        $request = $taxCalculator->getRateRequest(
            $this->quoteBuilder->getQuote()->getShippingAddress(),
            $this->quoteBuilder->getQuote()->getBillingAddress(),
            $this->quoteBuilder->getQuote()->getCustomerTaxClassId(),
            $this->quoteBuilder->getQuote()->getStore()
        );
        $request->setProductClassId($this->getProduct()->getTaxClassId());

        return $taxCalculator->getRate($request);
    }

    // ########################################

    public function getRequest()
    {
        $request = new Varien_Object();
        $request->setQty($this->proxyItem->getQty());

        // grouped and downloadable products doesn't have options
        if ($this->proxyItem->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED ||
            $this->proxyItem->getProduct()->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
            return $request;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product')->setProduct($this->getProduct());
        $options = $this->proxyItem->getOptions();

        if ($magentoProduct->isSimpleType()) {
            !empty($options) && $request->setOptions($options);
        } else if ($magentoProduct->isBundleType()) {
            $request->setBundleOption($options);
        } else if ($magentoProduct->isConfigurableType()) {
            $request->setSuperAttribute($options);
        }

        return $request;
    }

    // ########################################

    public function getGiftMessageId()
    {
        return $this->getGiftMessage() ? $this->getGiftMessage()->getId() : null;
    }

    public function getGiftMessage()
    {
        if (!is_null($this->giftMessage)) {
            return $this->giftMessage;
        }

        $giftMessageData = $this->proxyItem->getGiftMessage();

        if (!is_array($giftMessageData)) {
            return NULL;
        }

        $giftMessageData['customer_id'] = (int)$this->quoteBuilder->getQuote()->getCustomerId();
        /** @var $giftMessage Mage_GiftMessage_Model_Message */
        $giftMessage = Mage::getModel('giftmessage/message')->addData($giftMessageData);

        if ($giftMessage->isMessageEmpty()) {
            return NULL;
        }

        $this->giftMessage = $giftMessage->save();

        return $this->giftMessage;
    }

    // ########################################

    public function getAdditionalData(Mage_Sales_Model_Quote_Item $quoteItem)
    {
        $additionalData = $this->proxyItem->getAdditionalData();

        $existAdditionalData = $quoteItem->getAdditionalData();
        $existAdditionalData = is_string($existAdditionalData) ? @unserialize($existAdditionalData) : array();

        return serialize(array_merge((array)$existAdditionalData, $additionalData));
    }

    // ########################################
}