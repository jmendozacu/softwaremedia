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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Product Url model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Url extends Varien_Object
{
    const CACHE_TAG = 'url_rewrite';

    /**
     * URL instance
     *
     * @var Mage_Core_Model_Url
     */
    protected  $_url;

    /**
     * URL Rewrite Instance
     *
     * @var Mage_Core_Model_Url_Rewrite
     */
    protected $_urlRewrite;

    /**
     * Factory instance
     *
     * @var Mage_Catalog_Model_Factory
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Initialize Url model
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('catalog/factory');
        $this->_store = !empty($args['store']) ? $args['store'] : Mage::app()->getStore();
    }

    /**
     * Retrieve URL Instance
     *
     * @return Mage_Core_Model_Url
     */
    public function getUrlInstance()
    {
        if (null === $this->_url) {
            $this->_url = Mage::getModel('core/url');
        }
        return $this->_url;
    }

    /**
     * Retrieve URL Rewrite Instance
     *
     * @return Mage_Core_Model_Url_Rewrite
     */
    public function getUrlRewrite()
    {
        if (null === $this->_urlRewrite) {
            $this->_urlRewrite = $this->_factory->getUrlRewriteInstance();
        }
        return $this->_urlRewrite;
    }

    /**
     * 'no_selection' shouldn't be a valid image attribute value
     *
     * @param string $image
     * @return string
     */
    protected function _validImage($image)
    {
        if($image == 'no_selection') {
            $image = null;
        }
        return $image;
    }

    /**
     * Retrieve URL in current store
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params the URL route params
     * @return string
     */
    public function getUrlInStore(Mage_Catalog_Model_Product $product, $params = array())
    {
        $params['_store_to_url'] = true;
        return $this->getUrl($product, $params);
    }

    /**
     * Retrieve Product URL
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  bool $useSid forced SID mode
     * @return string
     */
    public function getProductUrl($product, $useSid = null)
    {
        if ($useSid === null) {
            $useSid = Mage::app()->getUseSessionInUrl();
        }

        $params = array();
        if (!$useSid) {
            $params['_nosid'] = true;
        }

        return $this->getUrl($product, $params);
    }

    /**
     * Format Key for URL
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Retrieve Product Url path (with category if exists)
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Catalog_Model_Category $category
     *
     * @return string
     */
    public function getUrlPath($product, $category=null)
    {
        $path = $product->getData('url_path');

        if (is_null($category)) {
            /** @todo get default category */
            return $path;
        } elseif (!$category instanceof Mage_Catalog_Model_Category) {
            Mage::throwException('Invalid category object supplied');
        }

        return Mage::helper('catalog/category')->getCategoryUrlPath($category->getUrlPath())
            . '/' . $path;
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     * @return string
     */
     
        public function getUrlNew(Mage_Catalog_Model_Product $product, $params = array())
    {
        $routePath      = '';
        $routeParams    = $params;

        $storeId    = $product->getStoreId();
        
        //if (isset($params['_ignore_category'])) {
            //unset($params['_ignore_category']);
            
            $_categories = $product->getCategoryIds();
            
            $categoryId = null;
            $fourthLevel = null;
            foreach($_categories as $_catId) {
	            $_category = Mage::getModel('catalog/category')->load($_catId);
	            if ($_category->getLevel() == 3) {
		            $categoryId = $_category->getId();
	            }
	            if ($_category->getLevel() == 4) {
		            $fourthLevel = $_category->getId();
	            }
	            if ($_category->getParentId() == 66 || $_category->getParentId() == 110) {
	            	$categoryId = $_category->getId();
	            	break;
	            }
            }
            
             if ($categoryId == null && $fourthLevel != null) {
	             $categoryId = $fourthLevel;
	             
             }          
            
        //} else {
        //    $categoryId = $product->getCategoryId() && !$product->getDoNotUseCategoryId()
        //        ? $product->getCategoryId() : null;
        //}

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();
            //if (empty($requestPath) && $requestPath !== false) {
                $idPath = sprintf('product/%d', $product->getEntityId());
                if ($categoryId) {
                    $idPath = sprintf('%s/%d', $idPath, $categoryId);
                }

                $rewrite = $this->getUrlRewrite();
                $rewrite->setStoreId($storeId)
                    ->loadByIdPath($idPath);
                if ($rewrite->getId()) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            //}
        }
		
        if (isset($routeParams['_store'])) {
            $storeId = Mage::app()->getStore($routeParams['_store'])->getId();
        }

        if ($storeId != Mage::app()->getStore()->getId()) {
            $routeParams['_store_to_url'] = true;
        }

        if (!empty($requestPath)) {
           $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id']  = $product->getId();
            $routeParams['s']   = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = array();
        }

        return $this->getUrlInstance()->setStore($storeId)
            ->getUrl($routePath, $routeParams);
    }
    
    public function getUrl(Mage_Catalog_Model_Product $product, $params = array())
    {
    	Mage::log("getURL " . $params,null,'url.log');
        $url = $product->getData('url');
        if (!empty($url)) {
            //return $url;
        }
Mage::log("cats " . $params,null,'url.log');
		    $_categories = $product->getCategoryIds();
            
            $categoryId = $this->_getCategoryIdForUrl($product, $params);
            $fourthLevel = null;
            foreach($_categories as $_catId) {
	            $_category = Mage::getModel('catalog/category')->load($_catId);
	            if ($_category->getLevel() == 3) {
		            $categoryId = $_category->getId();
	            }
	            if ($_category->getLevel() == 4) {
		            $fourthLevel = $_category->getId();
	            }
	            if ($_category->getParentId() == 66 || $_category->getParentId() == 110) {
	            	$categoryId = $_category->getId();
	            	break;
	            }
            }
            
             if ($categoryId == null && $fourthLevel != null) {
	             $categoryId = $fourthLevel;
	             
             }   
             
        $requestPath = $product->getData('request_path');
        Mage::log("R path " . $requestPath,null,'url.log');
        if (empty($requestPath)) {
        	
            $requestPath = $this->_getRequestPath($product, $this->_getCategoryIdForUrl($product, $categoryId));
            Mage::log("empty " . $requestPath,null,'url.log');
            $product->setRequestPath($requestPath);
        }

        if (isset($params['_store'])) {
            $storeId = $this->_getStoreId($params['_store']);
        } else {
            $storeId = $product->getStoreId();
        }

        if ($storeId != $this->_getStoreId()) {
            $params['_store_to_url'] = true;
        }

        // reset cached URL instance GET query params
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }
		Mage::log("getUrlInstance",null,'url.log');
        $this->getUrlInstance()->setStore($storeId);
        Mage::log("prod url " . $productUrl,null,'url.log');
        $productUrl = $this->_getProductUrlNew($product, $requestPath, $params);
        Mage::log("prod url " . $productUrl,null,'url.log');
        $product->setData('url', $productUrl);
        return $product->getData('url');
    }

    /**
     * Returns checked store_id value
     *
     * @param int|null $id
     * @return int
     */
    protected function _getStoreId($id = null)
    {
        return Mage::app()->getStore($id)->getId();
    }

    /**
     * Check product category
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $params
     *
     * @return int|null
     */
    protected function _getCategoryIdForUrl($product, $params)
    {
        if (isset($params['_ignore_category'])) {
            return null;
        } else {
            return $product->getCategoryId() && !$product->getDoNotUseCategoryId()
                ? $product->getCategoryId() : null;
        }
    }
    /**
     * Retrieve product URL based on requestPath param
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $requestPath
     * @param array $routeParams
     *
     * @return string
     */
    protected function _getProductUrlNew($product, $requestPath, $routeParams)
    {
        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);
        
		    $_categories = $product->getCategoryIds();

            $fourthLevel = null;
            foreach($_categories as $_catId) {
	            $_category = Mage::getModel('catalog/category')->load($_catId);
	            if ($_category->getLevel() == 3) {
		            $categoryId = $_category->getId();
	            }
	            if ($_category->getLevel() == 4) {
		            $fourthLevel = $_category->getId();
	            }
	            if ($_category->getParentId() == 66 || $_category->getParentId() == 110) {
	            	$categoryId = $_category->getId();
	            	break;
	            }
            }
            
             if ($categoryId == null && $fourthLevel != null) {
	             $categoryId = $fourthLevel;
	             
             }   
        if (!empty($requestPath)) {
            if ($categoryId) {
                $category = $this->_factory->getModel('catalog/category', array('disable_flat' => true))
                    ->load($categoryId);
                if ($category->getId()) {
                    $categoryRewrite = $this->_factory->getModel('enterprise_catalog/category')
                        ->loadByCategory($category);
                    if ($categoryRewrite->getId()) {
                        $requestPath = $categoryRewrite->getRequestPath() . '/' . $requestPath;
                    }
                }
            }
            $product->setRequestPath($requestPath);

            $storeId = $this->getUrlInstance()->getStore()->getId();
            $requestPath = $this->_factory->getHelper('enterprise_catalog')
                ->getProductRequestPath($requestPath, $storeId);

            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
        }

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
        }
        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }
    
    /**
     * Retrieve product URL based on requestPath param
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $requestPath
     * @param array $routeParams
     *
     * @return string
     */
    protected function _getProductUrl($product, $requestPath, $routeParams)
    {
    	Mage::log("get Product URL " . $requestPath,null,'url.log');
        if (!empty($requestPath)) {
            return $this->getUrlInstance()->getDirectUrl($requestPath, $routeParams);
         
        }
        Mage::log("request path: " . $requestPath,null,'url.log');
        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
        $categoryId = $this->_getCategoryIdForUrl($product, $routeParams);
        
		    $_categories = $product->getCategoryIds();

            $fourthLevel = null;
            foreach($_categories as $_catId) {
	            $_category = Mage::getModel('catalog/category')->load($_catId);
	            if ($_category->getLevel() == 3) {
		            $categoryId = $_category->getId();
	            }
	            if ($_category->getLevel() == 4) {
		            $fourthLevel = $_category->getId();
	            }
	            if ($_category->getParentId() == 66 || $_category->getParentId() == 110) {
	            	$categoryId = $_category->getId();
	            	break;
	            }
            }
            
             if ($categoryId == null && $fourthLevel != null) {
	             $categoryId = $fourthLevel;
	             
             }   
             
        if ($categoryId) {
            $routeParams['category'] = $categoryId;
             Mage::log("cat path: " . $categoryId,null,'url.log');
        }
        return $this->getUrlInstance()->getUrl('catalog/product/view', $routeParams);
    }

    /**
     * Retrieve request path
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $categoryId
     * @return bool|string
     */
    protected function _getRequestPath($product, $categoryId)
    {
        $idPath = sprintf('product/%d', $product->getEntityId());
        if ($categoryId) {
            $idPath = sprintf('%s/%d', $idPath, $categoryId);
        }
        $rewrite = $this->getUrlRewrite();
        $rewrite->setStoreId($product->getStoreId())
            ->loadByIdPath($idPath);
        if ($rewrite->getId()) {
            return $rewrite->getRequestPath();
        }

        return false;
    }
}
