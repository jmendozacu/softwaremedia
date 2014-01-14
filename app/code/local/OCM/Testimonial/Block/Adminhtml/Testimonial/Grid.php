<?php
class OCM_Testimonial_Block_Adminhtml_Testimonial_Grid extends Mage_Adminhtml_Block_Widget_Grid{
    public function __construct(){
        parent::__construct();
        $this->setId('testimonial_id');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }
    protected  function _prepareCollection(){
        $collection = Mage::getModel('ocm_testimonial/testimonial')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ocm_testimonial')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('user_name', array(
            'header'    => Mage::helper('ocm_testimonial')->__('User Name'),
            'align'     =>'left',
            'index'     => 'user_name',
        ));

        $this->addColumn('message', array(
            'header'    => Mage::helper('ocm_testimonial')->__('Message'),
            'align'     =>'left',
            'index'     => 'message',
        ));
        $this->addColumn('date_post', array(
            'header'    => Mage::helper('ocm_testimonial')->__('Date'),
            'align'     =>'left',
            'index'     => 'date_post',
        ));
        $this->addColumn('public', array(
            'header'    => Mage::helper('ocm_testimonial')->__('Public'),
            'align'     =>'left',
            'index'     => 'public',
            'type'      => 'options',
            'options'    => array('0' => 'Disable','1' => 'Enable')
        ));
        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('ocm_testimonial')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ));

        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}