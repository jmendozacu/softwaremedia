<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package     Mage_Cms
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Cms index controller
 *
 * @category   Mage
 * @package    Mage_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mestrona_ForwardToConfigurable_Cms_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Renders CMS Home page
     *
     * @param string $coreRoute
     */
    public function indexAction($coreRoute = null)
    {
        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultIndex');
        }
    }

    /**
     * Default index action (with 404 Not Found headers)
     * Used if default page don't configure or available
     *
     */
    public function defaultIndexAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Render CMS 404 Not found page
     *
     * @param string $coreRoute
     */
    public function noRouteAction($coreRoute = null)
    {
    	$urlString = Mage::helper('core/url')->getCurrentUrl();
		$url = Mage::getSingleton('core/url')->parseUrl($urlString);
		$path = $url->getPath();
		$path = str_replace('.html','',$path);
		$path = explode('/', $path);
		if ($path) {
			$path  = $path[count($path) - 1];
			$productList = Mage::getModel ('catalog/product')
            ->getCollection ()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter ('url_key', $path);
            
            $product = $productList->getFirstItem();
            
            if ($product->getId()) {
            	$parentIds = Mage::getModel('catalog/product_type_configurable')
	            ->getParentIdsByChild($product->getId());
	
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
		                //die($url . " 1");
						$this->getResponse()->setHeader('HTTP/1.1, 301 Moved Permanently');
						$this->getResponse()->setHeader('Location',$url);
						return;
		            }
		            // try to find other products if one parent product is not visible -> loop
		        }
	            

	            if ($product->isVisibleInCatalog() && $product->getUrlKey() != $path) {
	                //$this->_redirect();
	                //die($parentProduct->getId());
	                $url = $product->getProductUrl();
	                
					$this->getResponse()->setHeader('HTTP/1.1, 301 Moved Permanently');
					$this->getResponse()->setHeader('Location',$url);
					return;
	            }
            }
            
		}
		
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }
    }

    /**
     * Default no route page action
     * Used if no route page don't configure or available
     *
     */
    public function defaultNoRouteAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Render Disable cookies page
     *
     */
    public function noCookiesAction()
    {
        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_COOKIES_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoCookies');;
        }
    }

    /**
     * Default no cookies page action
     * Used if no cookies page don't configure or available
     *
     */
    public function defaultNoCookiesAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
