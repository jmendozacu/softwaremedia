<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitoptionstemplate_Block_Template_Edit_Tab_Ajax_Serializer extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('catalog/product/edit/serializer.phtml');
        return $this;
    }

    public function getProductsJSON()
    {
        $result = array();
        if ($this->getProducts()) {
            foreach ($this->getProducts() as $iProductId) {
#                $id = $isEntityId ? $product->getEntityId() : $product->getId();
#                $result[$id] = $product->toArray(array('qty', 'position'));
                $result[$iProductId] = array('qty' => null, 'position' => 0);
            }
        }
        /*
        $result = array();
        if ($this->getProducts()) {
            $isEntityId = $this->getIsEntityId();
            foreach ($this->getProducts() as $product) {
                $id = $isEntityId ? $product->getEntityId() : $product->getId();
#                $result[$id] = $product->toArray(array('qty', 'position'));
                $result[$id] = array('qty' => null, 'position' => 0);
            }
        }
        */
        return $result ? Zend_Json_Encoder::encode($result) : '{}';
    }
}