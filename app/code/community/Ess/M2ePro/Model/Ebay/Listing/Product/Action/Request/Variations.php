<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator getConfigurator()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Variations
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    // ########################################

    public function getData()
    {
        $data = array(
            'is_variation_item' => $this->getIsVariationItem()
        );

        $this->logLimitationsAndReasons();

        if (!$this->getIsVariationItem() || !$this->getConfigurator()->isVariationsAllowed()) {
            return $data;
        }

        $data['variation'] = $this->getVariationsData();

        if ($sets = $this->getSetsData()) {
            $data['variations_sets'] = $sets;
        }

        $data['variation_image'] = $this->getImagesData();

        return $data;
    }

    // ########################################

    public function getVariationsData()
    {
        $data = array();

        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();

        $variations = $this->getListingProduct()->getVariations(true);
        $productsIds = array();

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            /** @var $ebayVariation Ess_M2ePro_Model_Ebay_Listing_Product_Variation */

            $ebayVariation = $variation->getChildObject();

            $item = array(
                '_instance_' => $variation,
                'price'      => $ebayVariation->getPrice(),
                'qty'        => $ebayVariation->isDelete() ? 0 : $ebayVariation->getQty(),
                'sku'        => $ebayVariation->getSku(),
                'add'        => $ebayVariation->isAdd(),
                'delete'     => $ebayVariation->isDelete(),
                'specifics'  => array()
            );

            if (($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) && !$item['delete']) {

                $options = $variation->getOptions();
                foreach ($options as $option) {
                    $productsIds[] = $option['product_id'];
                }
            }

            if ($this->getEbayListingProduct()->isPriceDiscountStp()) {

                $priceDiscountData = array(
                    'original_retail_price' => $ebayVariation->getPriceDiscountStp()
                );

                if ($this->getEbayMarketplace()->isStpAdvancedEnabled()) {
                    $priceDiscountData = array_merge(
                        $priceDiscountData,
                        $this->getEbayListingProduct()->getEbaySellingFormatTemplate()
                             ->getPriceDiscountStpAdditionalFlags()
                    );
                }

                $item['price_discount_stp'] = $priceDiscountData;
            }

            if ($this->getEbayListingProduct()->isPriceDiscountMap()) {
                $priceDiscountMapData = array(
                    'minimum_advertised_price' => $ebayVariation->getPriceDiscountMap(),
                );

                $exposure = $ebayVariation->getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
                $priceDiscountMapData['minimum_advertised_price_exposure'] =
                    Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::
                        getPriceDiscountMapExposureType($exposure);

                $item['price_discount_map'] = $priceDiscountMapData;
            }

            $variationDetails = $this->getVariationDetails($variation);

            if (!empty($variationDetails)) {
                $item['details'] = $variationDetails;
            }

            $options = $variation->getOptions(true);

            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $item['specifics'][$option->getAttribute()] = $option->getOption();
            }

            $data[] = $item;
        }

        $this->checkQtyWarnings($productsIds);

        return $data;
    }

    public function getSetsData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (isset($additionalData['variations_sets'])) {
            return $additionalData['variations_sets'];
        }

        return false;
    }

    public function getImagesData()
    {
        $attributeLabels = array();

        if ($this->getMagentoProduct()->isConfigurableType()) {
            $attributeLabels = $this->getConfigurableImagesAttributeLabels();
        }

        if ($this->getMagentoProduct()->isGroupedType()) {
            $attributeLabels = array(Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
        }

        if (count($attributeLabels) <= 0) {
            return array();
        }

        return $this->getImagesDataByAttributeLabels($attributeLabels);
    }

    // ########################################

    private function logLimitationsAndReasons()
    {
        if ($this->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        if (!$this->getEbayMarketplace()->isMultivariationEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: eBay Site allows to list only Simple Items.'
                )
            );
            return;
        }

        $isVariationEnabled = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                    ->isVariationEnabled(
                                        (int)$this->getCategorySource()->getMainCategory(),
                                        $this->getMarketplace()->getId()
                                    );

        if (!is_null($isVariationEnabled) && !$isVariationEnabled) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: eBay Primary Category allows to list only Simple Items.'
                )
            );
            return;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: ignore Variation Option is enabled in Price, Quantity and Format Policy.'
                )
            );
            return;
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: Listing type "Auction" does not support Multi-Variations.'
                )
            );
            return;
        }
    }

    // ----------------------------------------

    private function getConfigurableImagesAttributeLabels()
    {
        $descriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        if (!$descriptionTemplate->isVariationConfigurableImages()) {
            return array();
        }

        $product = $this->getMagentoProduct()->getProduct();

        $attributeCode = $descriptionTemplate->getVariationConfigurableImages();

        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $product->getResource()->getAttribute($attributeCode);

        if (!$attribute) {
            return array();
        }

        $attribute->setStoreId($product->getStoreId());

        $attributeLabels = array();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->getMagentoProduct()->getTypeInstance();

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($product->getStoreId());

            if ((int)$attribute->getAttributeId() == (int)$configurableAttribute->getAttributeId()) {

                $attributeLabels = array_values($attribute->getStoreLabels());
                $attributeLabels[] = $configurableAttribute->getData('label');
                $attributeLabels[] = $attribute->getFrontendLabel();

                $attributeLabels = array_filter($attributeLabels);

                break;
            }
        }

        if (empty($attributeLabels)) {

            $this->addNotFoundAttributesMessages(
                Mage::helper('M2ePro')->__('Change Images for Attribute'),
                array($attributeCode)
            );

            return array();
        }

        return $attributeLabels;
    }

    private function getImagesDataByAttributeLabels(array $attributeLabels)
    {
        $images = array();
        $attributeLabel = false;

        $variations = $this->getListingProduct()->getVariations(true);

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            if ($variation->getChildObject()->isDelete()) {
                continue;
            }

            $options = $variation->getOptions(true);

            foreach ($options as $option) {

                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                $foundAttributeLabel = false;
                foreach ($attributeLabels as $tempLabel) {
                    if (strtolower($tempLabel) == strtolower($option->getAttribute())) {
                        $foundAttributeLabel = $option->getAttribute();
                        break;
                    }
                }

                if ($foundAttributeLabel === false) {
                    continue;
                }

                $attributeLabel = $foundAttributeLabel;

                $optionImages = $this->getEbayListingProduct()->getEbayDescriptionTemplate()
                                     ->getSource($option->getMagentoProduct())
                                     ->getVariationImages();

                if (count($optionImages) <= 0) {
                    continue;
                }

                $images[$option->getOption()] = $optionImages;
            }
        }

        if (!$attributeLabel || !$images) {
            return array();
        }

        return array(
            'specific' => $attributeLabel,
            'images' => $images
        );
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    private function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }

    // ########################################

    public function checkQtyWarnings($productsIds)
    {
        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $productsIds = array_unique($productsIds);
            $qtyWarnings = array();

            $listingProductId = $this->getListingProduct()->getId();
            $storeId = $this->getListing()->getStoreId();

            foreach ($productsIds as $productId) {
                if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'])) {

                    $qtys = Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'];
                    $qtyWarnings = array_unique(array_merge($qtyWarnings, array_keys($qtys)));
                }

                if (count($qtyWarnings) === 2) {
                    break;
                }
            }

            foreach ($qtyWarnings as $qtyWarningType) {
                $this->addQtyWarnings($qtyWarningType);
            }
        }
    }

    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
        // M2ePro_TRANSLATIONS
        // During the Quantity Calculation the Settings in the "Manage Stock No" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Manage Stock No" '.
                                     'field were taken into consideration.');
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Backorders" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Backorders" '.
                                     'field were taken into consideration.');
        }
    }

    // ########################################

    private function getVariationDetails(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $data = array();
        /** @var Ess_M2ePro_Model_Ebay_Template_Description $ebayDescriptionTemplate */
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        $this->searchNotFoundAttributes();

        $tempValue = NULL;

        if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply('brand')) {
            $tempValue = Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::PRODUCT_DETAILS_UNBRANDED;
        } elseif ($ebayDescriptionTemplate->isProductDetailsModeAttribute('brand') &&
                  $this->processNotFoundAttributes(strtoupper('brand'))) {
            $tempValue = $this->getEbayListingProduct()->getDescriptionTemplateSource()->getProductDetail('brand');
        }

        if ($tempValue) {
            $data['brand'] = $tempValue;
        }

        $options = NULL;
        $additionalData = $variation->getAdditionalData();

        foreach (array('isbn','upc','ean','mpn') as $tempType) {

            if (isset($additionalData['product_details'][$tempType])) {
                $data[$tempType] = $additionalData['product_details'][$tempType];
                continue;
            }

            if (!$this->getMagentoProduct()->isConfigurableType() &&
                !$this->getMagentoProduct()->isGroupedType()) {
                continue;
            }

            if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply($tempType)) {
                $data[$tempType] = Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description::
                                                                            PRODUCT_DETAILS_DOES_NOT_APPLY;
                continue;
            }

            $attribute = $ebayDescriptionTemplate->getProductDetailAttribute($tempType);

            if (!$attribute) {
                continue;
            }

            if (is_null($options)) {
                $options = $variation->getOptions(true);
            }

            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $option = reset($options);

            $this->searchNotFoundAttributes();
            $tempValue = $option->getMagentoProduct()->getAttributeValue($attribute);

            if (!$this->processNotFoundAttributes(strtoupper($tempType)) || !$tempValue) {
                continue;
            }

            $data[$tempType] = $tempValue;
        }

        return $this->deleteNotAllowedIdentifier($data);
    }

    private function deleteNotAllowedIdentifier(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        $categoryId = $this->getCategorySource()->getMainCategory();
        $marketplaceId = $this->getMarketplace()->getId();
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                  ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return $data;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean','upc','isbn') as $identifier) {

            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {

                unset($data[$identifier]);

                // M2ePro_TRANSLATIONS
                // The value of %type% was no sent because it is not allowed in this Category
                $this->addWarningMessage(
                    Mage::helper('M2ePro')->__(
                        'The value of %type% was no sent because it is not allowed in this Category',
                        Mage::helper('M2ePro')->__(strtoupper($identifier))
                    )
                );
            }
        }

        return $data;
    }

    // ########################################
}