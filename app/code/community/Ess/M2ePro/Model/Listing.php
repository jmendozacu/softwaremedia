<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const SOURCE_PRODUCTS_CUSTOM     = 1;
    const SOURCE_PRODUCTS_CATEGORIES = 2;

    const AUTO_MODE_NONE     = 0;
    const AUTO_MODE_GLOBAL   = 1;
    const AUTO_MODE_WEBSITE  = 2;
    const AUTO_MODE_CATEGORY = 3;

    const ADDING_MODE_NONE          = 0;
    const ADDING_MODE_ADD           = 1;

    const DELETING_MODE_NONE        = 0;
    const DELETING_MODE_STOP        = 1;
    const DELETING_MODE_STOP_REMOVE = 2;

    /**
     * @var Ess_M2ePro_Model_Account
     */
    private $accountModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Marketplace
     */
    private $marketplaceModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing');
    }

    // ########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        if ($this->isComponentModeEbay() && $this->getAccount()->getChildObject()->isModeSandbox()) {
            return false;
        }

        return (bool)Mage::getModel('M2ePro/Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('listing_id', $this->getId())
                            ->addFieldToFilter('status', Ess_M2ePro_Model_Listing_Product::STATUS_LISTED)
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $products = $this->getProducts(true);
        foreach ($products as $product) {
            $product->deleteInstance();
        }

        $categoriesGroups = $this->getAutoCategoriesGroups(true);
        foreach ($categoriesGroups as $categoryGroup) {
            $categoryGroup->deleteInstance();
        }

        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addListingMessage( $this->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Listing was successfully deleted
                                     'Listing was successfully deleted',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH );

        $this->accountModel = NULL;
        $this->marketplaceModel = NULL;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if (is_null($this->accountModel)) {
            $this->accountModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Account',$this->getAccountId()
            );
        }

        return $this->accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->accountModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if (is_null($this->marketplaceModel)) {
            $this->marketplaceModel = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(),'Marketplace',$this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param Ess_M2ePro_Model_Marketplace $instance
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $instance)
    {
         $this->marketplaceModel = $instance;
    }

    // ########################################

    public function getProducts($asObjects = false, array $filters = array())
    {
        $products = $this->getRelatedComponentItems('Listing_Product','listing_id',$asObjects,$filters);

        if ($asObjects) {
            foreach ($products as $product) {
                /** @var $product Ess_M2ePro_Model_Listing_Product */
                $product->setListing($this);
            }
        }

        return $products;
    }

    public function getAutoCategoriesGroups($asObjects = false, array $filters = array())
    {
        return $this->getRelatedComponentItems('Listing_Auto_Category_Group', 'listing_id', $asObjects, $filters);
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function getAccountId()
    {
        return (int)$this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getStoreId()
    {
        return (int)$this->getData('store_id');
    }

    //-----------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    // ########################################

    public function isSourceProducts()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CUSTOM;
    }

    public function isSourceCategories()
    {
        return (int)$this->getData('source_products') == self::SOURCE_PRODUCTS_CATEGORIES;
    }

    // ########################################

    public function getAutoMode()
    {
        return (int)$this->getData('auto_mode');
    }

    public function isAutoModeNone()
    {
        return $this->getAutoMode() == self::AUTO_MODE_NONE;
    }

    public function isAutoModeGlobal()
    {
        return $this->getAutoMode() == self::AUTO_MODE_GLOBAL;
    }

    public function isAutoModeWebsite()
    {
        return $this->getAutoMode() == self::AUTO_MODE_WEBSITE;
    }

    public function isAutoModeCategory()
    {
        return $this->getAutoMode() == self::AUTO_MODE_CATEGORY;
    }

    // ########################################

    public function getAutoGlobalAddingMode()
    {
        return (int)$this->getData('auto_global_adding_mode');
    }

    //----------------------------------------

    public function isAutoGlobalAddingModeNone()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_NONE;
    }

    public function isAutoGlobalAddingModeAdd()
    {
        return $this->getAutoGlobalAddingMode() == self::ADDING_MODE_ADD;
    }

    // #######################################

    public function getAutoWebsiteAddingMode()
    {
        return (int)$this->getData('auto_website_adding_mode');
    }

    //----------------------------------------

    public function isAutoWebsiteAddingModeNone()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_NONE;
    }

    public function isAutoWebsiteAddingModeAdd()
    {
        return $this->getAutoWebsiteAddingMode() == self::ADDING_MODE_ADD;
    }

    // #######################################

    public function getAutoWebsiteDeletingMode()
    {
        return (int)$this->getData('auto_website_deleting_mode');
    }

    //----------------------------------------

    public function isAutoWebsiteDeletingModeNone()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_NONE;
    }

    public function isAutoWebsiteDeletingModeStop()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP;
    }

    public function isAutoWebsiteDeletingModeStopRemove()
    {
        return $this->getAutoWebsiteDeletingMode() == self::DELETING_MODE_STOP_REMOVE;
    }

    // ########################################

    public function addProduct($product, $checkingMode = false, $checkHasProduct = true)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        if ($checkHasProduct && $this->hasProduct($productId)) {
            return false;
        }

        if ($checkingMode) {
            return true;
        }

        $data = array(
            'listing_id' => $this->getId(),
            'product_id' => $productId,
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN
        );

        $listingProductTemp =
            Mage::helper('M2ePro/Component')->getComponentModel($this->getComponentMode(),'Listing_Product')
                                    ->setData($data)->save();

        $variationUpdaterModel = ucwords($this->getComponentMode()).'_Listing_Product_Variation_Updater';
        /** @var Ess_M2ePro_Model_Listing_Product_Variation_Updater $variationUpdaterObject */
        $variationUpdaterObject = Mage::getModel('M2ePro/'.$variationUpdaterModel);
        $variationUpdaterObject->process($listingProductTemp);
        $variationUpdaterObject->afterMassProcessEvent();

        // Add message for listing log
        //------------------------------
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode($this->getComponentMode());
        $tempLog->addProductMessage( $this->getId(),
                                     $productId,
                                     $listingProductTemp->getId(),
                                     Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
                                     NULL,
                                     Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
                                     // M2ePro_TRANSLATIONS
                                     // Item was successfully Added
                                     'Item was successfully Added',
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW);
        //------------------------------

        return $listingProductTemp;
    }

    //-----------------------------------------

    public function addProductsFromCategory($categoryId)
    {
        $categoryProductsArray = $this->getProductsFromCategory($categoryId);
        foreach ($categoryProductsArray as $productTemp) {
            $this->addProduct($productTemp);
        }
    }

    public function getProductsFromCategory($categoryId, $hideProductsPresentedInAnotherListings = false)
    {
        // Make collection
        //----------------------------
        $collection = Mage::getModel('catalog/product')->getCollection();
        //----------------------------

        // Hide products others listings
        //----------------------------
        if ($hideProductsPresentedInAnotherListings) {

            $table = Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable();
            $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                ->select()
                ->from($table,new Zend_Db_Expr('DISTINCT `product_id`'))
                ->where('`component_mode` = ?',(string)$this->getComponentMode());

            $collection->getSelect()->where('`e`.`entity_id` NOT IN ('.$dbSelect->__toString().')');
        }
        //----------------------------

        // Add categories filter
        //----------------------------
        $table = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from($table,new Zend_Db_Expr('DISTINCT `product_id`'))
            ->where("`category_id` = ?",(int)$categoryId);

        $collection->getSelect()->where('`e`.`entity_id` IN ('.$dbSelect->__toString().')');
        //----------------------------

        // Get categories products
        //----------------------------
        $sqlQuery = $collection->getSelect()->__toString();

        $categoryProductsArray = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($sqlQuery);

        return (array)$categoryProductsArray;
    }

    // ########################################

    public function hasProduct($productId)
    {
        return count($this->getProducts(false,array('product_id'=>$productId))) > 0;
    }

    public function removeDeletedProduct($product)
    {
        $productId = $product instanceof Mage_Catalog_Model_Product ?
                        (int)$product->getId() : (int)$product;

        $processedListings = array();

        // Delete Products
        //------------------
        $listingsProducts = Mage::getModel('M2ePro/Listing_Product')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        $listingsProductsForRemove = array();

        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        foreach ($listingsProducts as $listingProduct) {

            if (!isset($listingsProductsForRemove[$listingProduct->getId()])) {
                $listingProduct->deleteProcessingRequests();
                $listingProduct->deleteObjectLocks();
                $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                $listingProduct->setStatus(Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED)->save();
                $listingsProductsForRemove[$listingProduct->getId()] = $listingProduct;
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId.'_'.$componentMode])) {
                continue;
            }

            $processedListings[$listingId.'_'.$componentMode] = 1;

            Mage::getModel('M2ePro/Listing_Log')
                ->setComponentMode($componentMode)
                ->addProductMessage($listingId,
                                    $productId,
                                    $listingProduct->getId(),
                                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                                    NULL,
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH );
        }

        $processedListings = array();

        // Delete Options
        //------------------
        $variationOptions = Mage::getModel('M2ePro/Listing_Product_Variation_Option')
                                    ->getCollection()
                                    ->addFieldToFilter('product_id', $productId)
                                    ->getItems();

        $deletedVariationsIds = array();

        /** @var $variationOption Ess_M2ePro_Model_Listing_Product_Variation_Option */
        foreach ($variationOptions as $variationOption) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = $variationOption->getListingProduct();

            if (!in_array($variationOption->getListingProductVariationId(),$deletedVariationsIds)) {
                $variationOption->getListingProductVariation()->deleteInstance();
                $deletedVariationsIds[] = $variationOption->getListingProductVariationId();
            }

            $listingId = $listingProduct->getListingId();
            $componentMode = $listingProduct->getComponentMode();

            if (isset($processedListings[$listingId.'_'.$componentMode])) {
                continue;
            }

            $processedListings[$listingId.'_'.$componentMode] = 1;

            Mage::getModel('M2ePro/Listing_Log')
                ->setComponentMode($componentMode)
                ->addProductMessage($listingId,
                                    $productId,
                                    $listingProduct->getId(),
                                    Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                                    NULL,
                                    Ess_M2ePro_Model_Listing_Log::ACTION_DELETE_PRODUCT_FROM_MAGENTO,
                                    // M2ePro_TRANSLATIONS
                                    // Variation Option was deleted. Item was reset.
                                    'Variation Option was deleted. Item was reset.',
                                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }

        foreach ($listingsProductsForRemove as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        //------------------
    }

    // ########################################

    public function getTrackingAttributes()
    {
        return $this->getChildObject()->getTrackingAttributes();
    }

    // ########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');
        return parent::delete();
    }

    // ########################################
}