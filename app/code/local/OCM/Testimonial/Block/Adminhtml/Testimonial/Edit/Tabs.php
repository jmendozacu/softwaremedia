<?php

class OCM_Testimonial_Block_Adminhtml_Testimonial_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs{
    public function __construct()
    {
        parent::__construct();
        $this->setId('testimonial_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Information Message');
    }
    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label' => 'Message Information',
            'title' => 'Message Information',
            'content' => $this->getLayout()
                ->createBlock('ocm_testimonial/adminhtml_testimonial_edit_tab_form')
                ->toHtml()
        ));
        return parent::_beforeToHtml();
    }
}