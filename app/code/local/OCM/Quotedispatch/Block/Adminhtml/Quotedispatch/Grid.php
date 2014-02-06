<?php

class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('quotedispatchGrid');
      $this->setDefaultSort('quotedispatch_id');
      $this->setDefaultDir('ASC');
      $this->setDefaultFilter(array('in_products'=>1));
      $this->setSaveParametersInSession(true);
  }
  
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('quotedispatch/quotedispatch')->getCollection()
        ->addFirstLastNameToSelect()
      ;
//      $collection->getSelect()
//              ->join(array('ALIAS'=>'TABLE_NAME'),'main_table.order_id=ALIAS.entity_id',array('FIELD TO SELECT 1','FIELD TO SELECT 2'));
      $this->setCollection($collection);
      
      /////
      
      
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
// if (!$this->hasData('all_items')) {
//            
//            $name_attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');
//            
//            //die(var_dump($name_attr->getData()));
//            
//            $collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
//                ->addFieldToFilter('quotedispatch_id',$this->getId())
//                //->addFieldToFilter('email',$this->getEmail())
//            ;
//        
//            $collection->getSelect()
//                ->joinleft(
//                    array('e' => 'catalog_product_entity'),
//                    'main_table.product_id = e.entity_id'
//                )
//                ->joinleft(
//                    array('pv' => 'catalog_product_entity_varchar'), 
//                    'pv.entity_id=main_table.product_id', 
//                    array('name' => 'value')
//                )
//                ->where('pv.attribute_id='.$name_attr->getAttributeId())
//                ->columns(array(
//                    'line_total' => new Zend_Db_Expr('main_table.price * main_table.qty')
//                    )
//                )
//            ;
//        $collection->joinAttribute('name','catalog_product/name', 'entity_id', 'entity_id','left', 0);
//        $collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
//                ->addFieldToFilter('quotedispatch_id',$this->getId())
//        $collection->getSelect()->joinRight('catalog_product_flat_1','catalog_product_flat_1.entity_id= `ocm_quotedispatch`.product_id','');
//            //die(var_dump($collection->getSelect()));
//        
//            $this->setData('all_items', $collection);
//        }
//        return $this->getData('all_items');
  
        //die(var_dump($this->getData('all_items')));
      $this->addColumn('quotedispatch_id', array(
          'header'    => Mage::helper('quotedispatch')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'quotedispatch_id',
      ));

//      $this->addColumn('title', array(
//          'header'    => Mage::helper('quotedispatch')->__('Quote Name'),
//          'align'     =>'left',
//          'index'     => 'title',
//      ));

      $this->addColumn('first_last_name', array(
          'header'    => Mage::helper('quotedispatch')->__('Name'),
          'align'     =>'left',
          'index'     => 'first_last_name',
          'filter_condition_callback' => array($this,'addConcatToFilter'),
      ));

      $this->addColumn('company', array(
          'header'    => Mage::helper('quotedispatch')->__('Company'),
          'align'     =>'left',
          'index'     => 'company',
      ));

      $this->addColumn('email', array(
          'header'    => Mage::helper('quotedispatch')->__('Email'),
          'align'     =>'left',
          'index'     => 'email',
      ));
      
      $this->addColumn('products', array(
          'header'    => Mage::helper('quotedispatch')->__('products'),
          'align'     =>'left',
          'index'     => $this->setCollection($collection),
      ));
      
       $this->addColumn('total',
            array(
                'header'=> Mage::helper('catalog')->__('Total'),
                'type'  => 'price',
                'name'  => 'total',
//                'currency_code' => $store->getBaseCurrency()->getCode(),
                'editable'  => 1,
                'index' => 'cost',
        ));
             
//       $this->addColumn('total', array(
//          'header'    => Mage::helper('quotedispatch')->__('total'),
//          'align'     =>'left',
//          'index'     => 'price',
//      ));

      $this->addColumn('expire_time', array(
            'header'  => Mage::helper('quotedispatch')->__('Expires'),
            'index'   => 'expire_time',
            'type'    => 'date',
            'width'   => '100px'
        ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('quotedispatch')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => Mage::getModel('quotedispatch/status')->getOptionArray(),
      ));

      $this->addColumn('created_by', array(
          'header'    => Mage::helper('quotedispatch')->__('Sales Rep'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'created_by',
          'type'      => 'options',
          'options'   => Mage::getModel('quotedispatch/adminuser')->getOptionArray(),
      ));
      
      $this->addExportType('*/*/exportCsv', Mage::helper('quotedispatch')->__('CSV'));
      $this->addExportType('*/*/exportExcel', Mage::helper('quotedispatch')->__('Excel XML'));
        
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('quotedispatch_id');
        $this->getMassactionBlock()->setFormFieldName('quotedispatch');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('quotedispatch')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('quotedispatch')->__('Are you sure?')
        ));
        
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }


    // Callback function converts first_last_name to CONCAT to culomn for select
    public function addConcatToFilter($collection, $column) {
        
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('CONCAT(firstname," ",lastname)', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);

    }


}