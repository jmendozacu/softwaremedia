<?php class OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Configurable
    extends Mage_Catalog_Model_Product_Type_Configurable
{
    #Copied from Magento v1.3.1 code.
    #Only need to comment out addFilterByRequiredOptions but there's no
    #nice way of doing that without cutting and pasting the method into my own
    #derived class. Boo.
    public function getUsedProducts($requiredAttributeIds = null, $product = null)
    {
        Varien_Profiler::start('CONFIGURABLE:'.__METHOD__);
        if (!$this->getProduct($product)->hasData($this->_usedProducts)) {
            if (is_null($requiredAttributeIds)
                and is_null($this->getProduct($product)->getData($this->_configurableAttributes))) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
                return $this->getProduct($product)->getData($this->_usedProducts);
            }

            $usedProducts = array();
            $collection = $this->getUsedProductCollection($product)
                ->addAttributeToSelect('*');
            // ->addFilterByRequiredOptions();

            if (is_array($requiredAttributeIds)) {
                foreach ($requiredAttributeIds as $attributeId) {
                    $attribute = $this->getAttributeById($attributeId, $product);
                    if (!is_null($attribute))
                        $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
                }
            }

            foreach ($collection as $item) {
                $usedProducts[] = $item;
            }

            $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
        }
        Varien_Profiler::stop('CONFIGURABLE:'.__METHOD__);
        return $this->getProduct($product)->getData($this->_usedProducts);
    }

    // Added by Alex
    // Get product with Lowest MSRP
    public function getMsrp()
    {
        $childProducts = $this->getUsedProducts();
        //Mage::log(print_r($childProducts), 'NULL', 'alex.log');
        if (count($childProducts) == 0) { #If config product has no children
            return false;
        }
        $minPrice = PHP_INT_MAX;
        foreach($childProducts as $childProduct) {
            if($childProduct->getMsrp() < $minPrice) {
                $minPrice = $childProduct->getMsrp();
            }
        }
        return $minPrice;
    }

    public function getFinalPrice()
    {
        $childProducts = $this->getUsedProducts();
        //Mage::log(print_r($childProducts), 'NULL', 'alex.log');
        if (count($childProducts) == 0) { #If config product has no children
            return false;
        }
        $minPrice = PHP_INT_MAX;
        foreach($childProducts as $childProduct) {
            if($childProduct->getFinalPrice() < $minPrice) {
                $minPrice = $childProduct->getFinalPrice();
            }
        }
        return $minPrice;
    }
}
