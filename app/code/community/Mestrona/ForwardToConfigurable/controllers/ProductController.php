<?php
require_once('Mage/Catalog/controllers/ProductController.php');
class Mestrona_ForwardToConfigurable_ProductController extends Mage_Catalog_ProductController
{
    /**
     * Product view action
     */
    public function viewAction()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

		$parentIds = Mage::getModel('catalog/product_type_configurable')
            ->getParentIdsByChild($productId);

        while (count($parentIds) > 0) {
            $parentId = array_shift($parentIds);
            /* @var $parentProduct Mage_Catalog_Model_Product */
            $parentProduct = Mage::getModel('catalog/product');
            $parentProduct->load($parentId);
            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$parentProduct->getId()) {
                throw new Exception(sprintf('Can not load parent product with ID %d', $parentId));
            }
					 //This returns the Label and Value of each selected option as an array. E.g. array( array('label'=>'Length', 'value'=>'32"'))
		        $attributesInfo = $parentProduct->getTypeInstance()->getSelectedAttributesInfo();
				var_dump($attributesId);
		        //This returns the IDs of the attributes that were used to make the configurable product. E.g. array( 0=>513 )
		        $attributesId = $parentProduct->getTypeInstance()->getUsedProductAttributeIds();
		        
		        //An empty array to fill.
		        $attributes = array();

				
		        //For as long as $i is less than the amount of value in $attributesID
		        for ($i = 0; $i < count($attributesId); $i++) {
		        	$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributesId[$i]);
		            //Add an array to the $attributes array that contains the attribute ID at the current index and the attribute value at the current index
		            $attributes[$i] =  $attributesId[$i] . "=" . $product->getData($attribute->getName());
		        }
				$attrList = implode('&',$attributes);
				
            if ($parentProduct->isVisibleInCatalog()) {
                //$this->_redirect();
                //die($parentProduct->getId());
                $url = $parentProduct->getProductUrl(). "?" . $attrList;
     
				$this->getResponse()->setHeader('HTTP/1.1, 301 Moved Permanently');
				$this->getResponse()->setHeader('Location',$url);
				return;
            }
            // try to find other products if one parent product is not visible -> loop
        }
        
        // Prepare helper and params
        $viewHelper = Mage::helper('catalog/product_view');

        $params = new Varien_Object();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $viewHelper->prepareAndRender($productId, $this, $params);
        } catch (Exception $e) {
            if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
                if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                    $this->_redirect('');
                } elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                }
            } else {
                Mage::logException($e);
                $this->_forward('noRoute');
            }
        }
    }

}
