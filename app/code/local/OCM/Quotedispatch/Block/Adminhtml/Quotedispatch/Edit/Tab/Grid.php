<?php

class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit_Tab_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('quote_dispatch_product_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('sku');
        $this->setDefaultFilter(array('in_products'=>1));        
        $this->setSaveParametersInSession(false);
    }

    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('cpc_price')
            ->addAttributeToFilter('type_id','simple')
        ;
        

        $collection->joinAttribute(
            'orgprice',
            'catalog_product/price',
            'entity_id',
            null,
            'left'
        );

        $quotedispatch_id = $this->getRequest()->getParam('id');
        $db = Mage::helper('quotedispatch')->getDb();
    
        $quotedispatch_info = $db->select()
            ->from(array('i' => 'ocm_quotedispatch_item'))
            ->where('i.quotedispatch_id = '.$quotedispatch_id)
        ;
        
        $collection->getSelect()->joinLeft(
            array('item'=>$quotedispatch_info),
            'item.product_id=e.entity_id',
             array('price','qty')
        );

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
        }



    protected function _prepareColumns()
    {
        $store = $this->_getStore();

        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));
        
        $this->addColumn('qty', array(
                    'header'            => Mage::helper('catalog')->__('Qty Requested'),
                    'name'              => 'qty',
                    'width'             => 60,
                    'type'              => 'number',
                    'validate_class'    => 'validate-number',
                    'index'             => 'qty',
                    'editable'          => true,
                    'edit_only'         => true,
                    ));       
        
        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'type'  => 'price',
                'name'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'editable'  => 1,
                'index' => 'cost',
        ));

        $this->addColumn('cpc_price',
            array(
                'header'=> Mage::helper('catalog')->__('CPC Price'),
                'type'  => 'price',
                'name'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'editable'  => 1,
                'index' => 'cpc_price',
        ));

        $this->addColumn('orgprice',
            array(
                'header'=> Mage::helper('catalog')->__('Site Price'),
                'type'  => 'price',
                'name'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'editable'  => 1,
                'index' => 'orgprice',
        ));


        $this->addColumn('price', array(
                    'header'            => Mage::helper('catalog')->__('Price'),
                    'name'              => 'price',
                    'width'             => 60,
                    'type'              => 'number',
                    'validate_class'    => 'validate-number',
                    'index'             => 'price',
                    'editable'          => true,
                    'edit_only'         => true,
                    ));
        
        return parent::_prepareColumns();
    }


    protected function _getSelectedProducts() {
        
        $id = Mage::app()->getRequest()->getParam('id');
        $products = array();
        if($id) {
            $collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
                ->addFieldToFilter('quotedispatch_id',$id)
            ;
            foreach ($collection as $product) {
                $products[] = $product->getProductId();
            }
        }
        if(!count($products)) return false;
        
        return $products;

    }


    public function getGridUrl()
        {
            return $this->_getData('grid_url') ? $this->_getData('grid_url') : $this->getUrl('*/*/quoteitemsgrid', array('_current'=>true));
        }

}
