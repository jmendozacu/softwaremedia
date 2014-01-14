<?php
class OCM_Testimonial_Block_Adminhtml_Testimonial extends Mage_Adminhtml_Block_Widget_Grid_Container{
    protected $_addButtonLabel = 'Add New Message';
    public function __construct(){
        $this->_controller = "adminhtml_testimonial";
        $this->_blockGroup = "ocm_testimonial";
        $this->_headerText = Mage::helper('ocm_testimonial')->__('Message Manager');
        parent::__construct();
    }
    protected function _prepareLayout(){
        $this->setChild( 'grid',
            $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
                $this->_controller . '.grid')->setSaveParametersInSession(true) );
        return parent::_prepareLayout();
    }
}