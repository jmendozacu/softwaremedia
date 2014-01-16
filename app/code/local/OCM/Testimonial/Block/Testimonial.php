<?php
class OCM_Testimonial_Block_Testimonial extends Mage_Core_Block_Template{
    public $_collection;
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $toolbar = $this->getLayout()->createBlock('page/html_pager','testimonial.pager')->setTemplate('testimonial/pager.phtml');
       // var_dump($this->getLayout());die();
        $toolbar->setCollection($this->getAllCollection());
        $this->setChild('toolbar.testimonial', $toolbar);
        $this->getAllCollection()->load();
        return $this;
    }
    public function getAllCollection(){
        if(is_null($this->_collection)){
            $this->_collection = Mage::getModel('ocm_testimonial/testimonial')->getCollection()->addFieldToFilter('public', array('eq' =>1))->setOrder('date_post', 'desc');
        }
        return $this->_collection;
    }

}