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
 * Rating list block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Rating_List extends Mage_Core_Block_Template
{
    /**
     * initialize
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $ratings = Mage::getResourceModel('softwaremedia_ratings/rating_collection')
                         ->addFieldToFilter('status', 1);
        $ratings->setOrder('user_id', 'asc');
        $this->setRatings($ratings);
        
        $adminId = Mage::app()->getRequest()->getParam('user');
	    
	    if ($adminId) {
	    	$adminUser =  Mage::getModel('admin/user')->load($adminId); 
	    
			$this->setAdminUser($adminUser);
		}
		
		$ratingId = Mage::app()->getRequest()->getParam('rating_id');
		if ($ratingId) {
			$rating = Mage::getModel('softwaremedia_ratings/rating')->load($ratingId);
			$this->setRating($rating);
		}
    }
    
    public function getRatingURL($rating) {
	    
	    if (Mage::app()->getRequest()->getParam('user'))
	    	$chat = '/chat/1/';
	    return "/smratings/rating/rate/user_id/" . Mage::app()->getRequest()->getParam('user') . "/rating/" . $rating . $chat;
    }
    
    public function getAdminName() {
	    if ($this->getAdminUser()) {
	    	return $this->getAdminUser()->getFirstname();
	    }
	    return false;
    }

    /**
     * prepare the layout
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Rating_List
     * @author Ultimate Module Creator
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock(
            'page/html_pager',
            'softwaremedia_ratings.rating.html.pager'
        )
        ->setCollection($this->getRatings());
        $this->setChild('pager', $pager);
        $this->getRatings()->load();
        return $this;
    }

    /**
     * get the pager html
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
