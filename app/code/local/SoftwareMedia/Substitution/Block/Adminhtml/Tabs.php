<?php
 
class SoftwareMedia_Substitution_Block_Adminhtml_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    private $parent;
 
    protected function _prepareLayout()
    {
        //get all existing tabs
        $this->parent = parent::_prepareLayout();
        //add new tab
        $this->addTab('substitution', array(
                     'label'     => Mage::helper('catalog')->__('Substitution'),
                     'content'   => $this->getLayout()
             ->getBlock('subblock')->toHtml(),
        ));
        return $this->parent;
    }
}