<?php
/**
 * SoftwareMedia_Ratings extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Ratings
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Rating front contrller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
 
 error_reporting(E_ALL);
class SoftwareMedia_Ratings_RatingController extends Mage_Core_Controller_Front_Action
{

    /**
      * default action
      *
      * @access public
      * @return void
      * @author Ultimate Module Creator
      */
     
    public function commentAction() {
	     $rating    = Mage::getModel('softwaremedia_ratings/rating')->load(Mage::app()->getRequest()->getParam('rating_id'));
	     
	     $rating->setComment(Mage::app()->getRequest()->getParam('comment'));

	     $rating->save();
	     
	     $this->_redirect('*/*/index/rating_id/' . $rating->getId());
    }
     
    public function rateAction() {
	     $rating    = Mage::getModel('softwaremedia_ratings/rating');
	     
	     if (Mage::app()->getRequest()->getParam('source')) 
	     	$rating->setSource(Mage::app()->getRequest()->getParam('source'));
	     else 
	     	$rating->setSource('E-Mail');
	     	
	     if (Mage::app()->getRequest()->getParam('user_id'))
	     	$rating->setUserId(Mage::app()->getRequest()->getParam('user_id'));
	     
	     $rating->setRating(Mage::app()->getRequest()->getParam('rating'));
	     if($customer = Mage::getSingleton('customer/session')->isLoggedIn()) {
		    $rating->setCustomerId(Mage::getSingleton('customer/session')->getId());
		}
            
		 $rating->setIp($this->get_ip_address());
	     
	     $rating->save();
	     
	     $this->_redirect('*/*/index/rating_id/' . $rating->getId());
    }
     
      public function get_ip_address() {
      	foreach($_SERVER as $key => $val) {
	      	Mage::log($key . ": " . $val,NULL,'addr.log');
      	}
		  // Check for shared internet/ISP IP
		  if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP']))
		   return $_SERVER['HTTP_CLIENT_IP'];
		
		  // Check for IPs passing through proxies
		  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		   // Check if multiple IP addresses exist in var
		    $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		    foreach ($iplist as $ip) {
		     	if ($this->validate_ip($ip))
			 		return $ip;
		    }
		   }
		  
		  if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
		   return $_SERVER['HTTP_X_FORWARDED'];
		  if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
		   return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		  if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
		   return $_SERVER['HTTP_FORWARDED_FOR'];
		  if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
		   return $_SERVER['HTTP_FORWARDED'];
		
		  // Return unreliable IP address since all else failed
		  return $_SERVER['REMOTE_ADDR'];
		 }
		
		 /**
		  * Ensures an IP address is both a valid IP address and does not fall within
		  * a private network range.
		  *
		  * @access public
		  * @param string $ip
		  */
		 public function validate_ip($ip) {
		     if (filter_var($ip, FILTER_VALIDATE_IP, 
		                         FILTER_FLAG_IPV4 | 
		                         FILTER_FLAG_IPV6 |
		                         FILTER_FLAG_NO_PRIV_RANGE | 
		                         FILTER_FLAG_NO_RES_RANGE) === false)
		         return false;
		     return true;
		 }
		 
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        if (Mage::helper('softwaremedia_ratings/rating')->getUseBreadcrumbs()) {
            if ($breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbBlock->addCrumb(
                    'home',
                    array(
                        'label' => Mage::helper('softwaremedia_ratings')->__('Home'),
                        'link'  => Mage::getUrl(),
                    )
                );
                $breadcrumbBlock->addCrumb(
                    'smratings',
                    array(
                        'label' => Mage::helper('softwaremedia_ratings')->__('Rate Your Experience'),
                        'link'  => '',
                    )
                );
            }
        }
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->addLinkRel('canonical', Mage::helper('softwaremedia_ratings/rating')->getRatingsUrl());
        }
        $this->renderLayout();
    }
}
