<?php
/**
 * Catalog product subtitution block
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */
 
class SoftwareMedia_Substitution_Model_Catalog_Product extends OCM_Price_Model_Catalog_Product
{
	const LINK_TYPE_SUBSTITUTION   = 6;
	
    /**
     * Retrieve array of related roducts
     *
     * @return array
     */
    public function getSubstitutionProducts()
    {
        if (!$this->hasSubstitutionProducts()) {
            $products = array();
            $collection = $this->getSubstitutionProductCollection();
            foreach ($collection as $product) {
                $products[] = $product;
            }
            $this->setSubstitutionProducts($products);
        }
        return $this->getData('substitution_products');
    }

    /**
     * Retrieve related products identifiers
     *
     * @return array
     */
    public function getSubstitutionProductIds()
    {
        if (!$this->hasSubstitutionProductIds()) {
            $ids = array();
            foreach ($this->getSubstitutionProducts() as $product) {
                $ids[] = $product->getId();
            }
            $this->setSubstitutionProductIds($ids);
        }
        return $this->getData('substitution_product_ids');
    }

    /**
     * Retrieve collection related product
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getSubstitutionProductCollection()
    {
        $collection = $this->getLinkInstance()->useSubstitutionLinks()
            ->getProductCollection()
            ->setIsStrongMode();
            
        $collection->setProduct($this);
        return $collection;
    }

    /**
     * Retrieve collection related link
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Collection
     */
    public function getSubstitutionLinkCollection()
    {
        $collection = $this->getLinkInstance()->setLinkTypeId(self::LINK_TYPE_SUBSTITUTION)
            ->getLinkCollection();
        $collection->setProduct($this);
        $collection->addLinkTypeIdFilter();
        $collection->addProductIdFilter();
        $collection->joinAttributes();
        return $collection;
    }
}