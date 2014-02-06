<?php
/**
 * Catalog product controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */
 
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Catalog'.DS.'ProductController.php';

class SoftwareMedia_Substitution_Adminhtml_Catalog_SubstitutionController extends Mage_Adminhtml_Catalog_ProductController
{
    /**
     * Product substitution page
     */
    public function substitutionAction()
    {
       $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.substitution')
            ->setProductsRelated($this->getRequest()->getPost('products_substitution', null));
        $this->renderLayout();
    }
    /**
     * Get substitution products grid
     */
    public function substitutionGridAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->getLayout()->getBlock('catalog.product.edit.tab.substitution')
            ->setProductsRelated($this->getRequest()->getPost('products_substitution', null));
        $this->renderLayout();
    }
}

