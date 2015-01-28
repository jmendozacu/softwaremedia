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
 * Rating admin grid block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Adminhtml_Rating_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('ratingGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('softwaremedia_ratings/rating')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareColumns()
    {
    
    	/*
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('softwaremedia_ratings')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        */
        $adminUserModel = Mage::getModel('admin/user');
		$userCollection = $adminUserModel->getCollection()->addFieldToFilter('is_active',1); 
		
		$referer_arr = array();
		
		foreach($userCollection as $user) {
			$referer_arr[$user->getId()] = $user->getUsername();
		}
		
		$this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('softwaremedia_ratings')->__('Date'),
                'index'  => 'created_at',
                'type'   => 'datetime',
            )
        );
        
		$this->addColumn('user_id', array(
			'header' => Mage::helper('sales')->__('Admin User'),
			'index' => 'user_id',
			'type' => 'options',
			'width' => '70px',
			'options' => $referer_arr,
		));

		$this->addColumn('source', array(
			'header' => Mage::helper('sales')->__('Source'),
			'index' => 'source',
			'type' => 'options',
			'options' => array('Chat' => 'Chat','E-Mail' => 'E-Mail'),
		));
		$this->addColumn(
            'rating',
            array(
                'header' => Mage::helper('softwaremedia_ratings')->__('Rating'),
                'index'  => 'rating',
                'type'=> 'number',

            )
        );
		$this->addColumn('comment', array(
			'header' => Mage::helper('sales')->__('Comment'),
			'index' => 'comment'
		));
		
        $this->addColumn(
            'customer_id',
            array(
                'header' => Mage::helper('softwaremedia_ratings')->__('Customer ID'),
                'index'  => 'customer_id',
                'type'=> 'text',
                'width' => '70px'

            )
        );
        
        $roleId = implode('', Mage::getSingleton('admin/session')->getUser()->getRoles());
        if ($roleId == 1) {
	        $this->addColumn(
	            'ip',
	            array(
	                'header' => Mage::helper('softwaremedia_ratings')->__('IP Address'),
	                'index'  => 'ip',
	                'type'=> 'text',
	
	            )
	        );
        }

        
       
        $this->addExportType('*/*/exportCsv', Mage::helper('softwaremedia_ratings')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('softwaremedia_ratings')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('softwaremedia_ratings')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {

        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param SoftwareMedia_Ratings_Model_Rating
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/index', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
