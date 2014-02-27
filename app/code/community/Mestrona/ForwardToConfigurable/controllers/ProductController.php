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

            if ($parentProduct->isVisibleInCatalog()) {
                //$this->_redirect();
                //die($parentProduct->getId());
                $url = $parentProduct->getProductUrl();
                //die($url);
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
