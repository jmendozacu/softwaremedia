<?php
class OCM_Testimonial_Model_Mysql4_Testimonial extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct(){
        $this->_init('ocm_testimonial/testimonial','id');
    }
}