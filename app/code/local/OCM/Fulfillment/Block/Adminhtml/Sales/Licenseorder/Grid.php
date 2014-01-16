<?php

class OCM_Fulfillment_Block_Adminhtml_Sales_Licenseorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('licenseorderGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {

        $collection = Mage::getModel('ocm_fulfillment/license')->getCollection();
        $collection->getSelect()
            ->join(array('order'=>'sales_flat_order'),'main_table.order_id=order.entity_id',array('increment_id','base_grand_total','grand_total'));
        $this->setCollection($collection);
        return $this;
    }

    protected function _prepareColumns()
    {

        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('ocm_fulfillment')->__('Order ID #'),
            'align'     =>'center',
            'width'     => '10px',
            'index'     => 'increment_id',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('ocm_fulfillment')->__('Status'),
            'align'     =>'left',
            'index'     => 'status',

        ));
        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('havelicense', array(
            'label'=> $this->__('License assigned'),
            'url'  => $this->getUrl('*/*/assigned'),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));
        $this->getMassactionBlock()->addItem('nolicense', array(
            'label'=> $this->__('Not assigned'),
            'url'  => $this->getUrl('*/*/notassigned'),
            'confirm' => Mage::helper('catalog')->__('Are you sure?')
        ));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()));
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}