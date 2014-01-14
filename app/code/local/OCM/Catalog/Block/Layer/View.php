<?php
class OCM_Catalog_Block_Layer_View extends Mage_Catalog_Block_Layer_View
{
    public function getFilters()
    {
        $filters = array();
        //if ($categoryFilter = $this->_getCategoryFilter()) {
        //    $filters[] = $categoryFilter;
        //}

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
        }
        return $filters;
    }
}
