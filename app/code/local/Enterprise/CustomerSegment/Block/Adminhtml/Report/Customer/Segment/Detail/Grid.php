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
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Customer Segments Detail grid
 *
 * @category   Enterprise
 * @package    Enterprise_CustomerSegment
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Detail_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid parameters
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setId('segmentGrid')->setUseAjax(true);
    }

    /**
     * Instanitate collection and set required data joins
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Detail_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Enterprise_CustomerSegment_Model_Mysql4_Report_Customer_Collection */
        $collection = Mage::getResourceModel('enterprise_customersegment/report_customer_collection');
        $collection->addNameToSelect()
            ->setViewMode($this->getCustomerSegment()->getViewMode())
            ->addSegmentFilter($this->getCustomerSegment())
            ->addWebsiteFilter(Mage::registry('filter_website_ids'))
            //->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_shipping', null, 'left')
            //->joinAttribute('billing_city', 'customer_address/city', 'default_shipping', null, 'left')
            //->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left');
            //->joinAttribute('billing_region', 'customer_address/region', 'default_shipping', null, 'left')
            //->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_shipping', null, 'left');
            ;
			$collection->getSelect()->joinLeft(
				'customer_entity_int', '`customer_entity_int`.entity_id=`e`.entity_id AND `customer_entity_int`.attribute_id = 1553', array('value')
			);
			$collection->getSelect()->joinLeft(
				array('add_entity'=>'customer_address_entity'), '`add_entity`.parent_id=`e`.entity_id', array('add_id' => 'entity_id')
			);
			
			//Join telephone
			$collection->getSelect()->joinLeft(
				array('add_telephone'=>'customer_address_entity_varchar'), '`add_telephone`.entity_id=add_entity.entity_id AND (`add_telephone`.`attribute_id` = 17)', array('billing_telephone' => 'value')
			);
			
			$collection->getSelect()->joinLeft(
				array('add_city'=>'customer_address_entity_varchar'), '`add_city`.entity_id=add_entity.entity_id AND (`add_city`.`attribute_id` = 15)', array('billing_city' => 'value')
			);
			
			$collection->getSelect()->joinLeft(
				array('add_country'=>'customer_address_entity_varchar'), '`add_country`.entity_id=add_entity.entity_id AND (`add_country`.`attribute_id` = 11)', array('billing_country_id' => 'value')
			);
			
			$collection->getSelect()->joinLeft(
				array('add_post'=>'customer_address_entity_varchar'), '`add_post`.entity_id=add_entity.entity_id AND (`add_post`.`attribute_id` = 14)', array('billing_postcode' => 'value')
			);
			
			$collection->getSelect()->joinLeft(
				array('add_region'=>'customer_address_entity_varchar'), '`add_region`.entity_id=add_entity.entity_id AND (`add_region`.`attribute_id` = 12)', array('billing_region' => 'value')
			);
			
			$collection->getSelect()->joinLeft(
				'eav_attribute_option_value', '`customer_entity_int`.value=`eav_attribute_option_value`.option_id', array('customer_rep' => 'value')
			);
			
			
			
			
			$collection->getSelect()->group('e.entity_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Customer Segment Getter
     *
     * @return Enterprise_CustomerSegment_Model_Segment
     */
    public function getCustomerSegment()
    {
        return Mage::registry('current_customer_segment');
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_CustomerSegment_Block_Adminhtml_Report_Customer_Segment_Detail_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('grid_entity_id', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('ID'),
            'width'     => 50,
            'index'     => 'entity_id',
            'type'      => 'number',
        ));
        $this->addColumn('grid_name', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Name'),
            'index'     => 'name'
        ));
        
        $this->addColumn('customer_rep', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Customer Rep'),
            'index'     => 'customer_rep'
        ));
        
        $this->addColumn('grid_email', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Email'),
            'width'     => 150,
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('grid_group', array(
            'header'    =>  Mage::helper('enterprise_customersegment')->__('Group'),
            'width'     =>  100,
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('grid_telephone', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Telephone'),
            'width'     => 100,
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('grid_billing_postcode', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('ZIP'),
            'width'     => 90,
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('grid_billing_country_id', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Country'),
            'width'     => 100,
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('grid_billing_region', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('State/Province'),
            'width'     => 100,
            'index'     => 'billing_region',
        ));

        $this->addColumn('grid_customer_since', array(
            'header'    => Mage::helper('enterprise_customersegment')->__('Customer Since'),
            'width'     => 200,
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('enterprise_customersegment')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('enterprise_customersegment')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Ajax grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customerGrid',
            array('segment_id' => Mage::registry('current_customer_segment')->getId()));
    }

    /**
     * Mock function to prevent grid row highlight
     *
     * @param $item
     * @return null
     */
    public function getRowUrl($item)
    {
        return null;
    }
}
