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
            
		if (Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR'))
			$rating->setIp(Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR'));
		 else
		 	$rating->setIp(Mage::helper('core/http')->getRemoteAddr());
	     
	     Mage::log("Remote Addr: " . Mage::helper('core/http')->getRemoteAddr(), NULL,'addr.log');
	     Mage::log("FORWARDED Addr: " . Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR'), NULL,'addr.log');
	     Mage::log("FORWARDED Addr: " . Mage::app()->getRequest()->getServer('HTTP_CLIENT_IP'), NULL,'addr.log');
	     
	     $rating->save();
	     
	     $this->_redirect('*/*/index/rating_id/' . $rating->getId());
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
