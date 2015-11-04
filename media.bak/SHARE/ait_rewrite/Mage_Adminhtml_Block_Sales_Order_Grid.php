<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


if (class_exists('MDN_AdvancedStock_Block_Adminhtml_Sales_Order_Grid', false)) {
    class AW_Ordertags_Block_Adminhtml_Sales_Order_GridTmp extends MDN_AdvancedStock_Block_Adminhtml_Sales_Order_Grid
    {
    }

} else {
    class AW_Ordertags_Block_Adminhtml_Sales_Order_GridTmp extends Mage_Adminhtml_Block_Sales_Order_Grid
    {
    }
}

class AW_Ordertags_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected $_exportFlag = false;
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $_orderidtotagidTable = Mage::getSingleton('core/resource')->getTableName("ordertags/ordertotag");
        $_tagTable = Mage::getSingleton('core/resource')->getTableName("ordertags/managetags");

        $collection = $this->getCollection();
                $collection->getSelect()
            ->joinleft(
                array('ot' => $_orderidtotagidTable), $this->_getSalesOrdersTableSyn() . '.entity_id = ot.order_id',
                array()
            )
            ->joinleft(array('tag' => $_tagTable), 'ot.tag_id = tag.tag_id')
            ->columns(array('filenames' => new Zend_Db_Expr('CONVERT(GROUP_CONCAT(DISTINCT tag.filename) USING utf8)')))
            ->columns(array('tags' => new Zend_Db_Expr('CONVERT(GROUP_CONCAT(DISTINCT tag.tag_id) USING utf8)')));
           
        $collection->getSelect()->group($this->_getSalesOrdersTableSyn() . '.entity_id');
		//$collection->getSelect()->group('ot.order_id');

        $collection->clear();
        $this->setCollection($collection);
        return $this;
    }

    protected function _prepareColumns()
    {
        if (!$this->_exportFlag) {
            $this->addColumn(
                'tag',
                array(
                     'header'                    => Mage::helper('ordertags')->__('Order Tags'),
                     'index'                     => 'tag',
                     'type'                      => 'options',
                     'width'                     => '70px',
                     'options'                   => $this->_returnOptionsList(),
                     'renderer'                  => 'ordertags/adminhtml_sales_order_grid_column_renderer_options',
                     'filter_condition_callback' => array($this, 'filter_tag_callback'),
                     'sortable'                  => false,
                )
            );
        }
        parent::_prepareColumns();
        $this->_exportFlag = false;
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        $tags = Mage::getModel('ordertags/managetags')
            ->getCollection()
            ->setOrder('sort_order', 'ASC')
            ->toOptionArray()
        ;

        $addOrderTagUrl = $this->getUrl(
            'admin_ordertags/adminhtml_managetags/massAddTag', array('_current' => true)
        );
        $this->getMassactionBlock()->addItem(
            'add_ordertag',
            array(
                 'label'      => $this->__('Add Order Tag'),
                 'url'        => $addOrderTagUrl,
                 'additional' => array(
                     'visibility' => array(
                         'name'   => 'tag_id',
                         'type'   => 'select',
                         'class'  => 'required-entry',
                         'label'  => $this->__('Order Tag'),
                         'values' => $tags
                     )
                 )
            )
        );

        $massRemoveTagUrl = $this->getUrl(
            'admin_ordertags/adminhtml_managetags/massRemoveTag', array('_current' => true)
        );
        $this->getMassactionBlock()->addItem(
            'remove_ordertag',
            array(
                 'label'      => $this->__('Remove Order Tag'),
                 'url'        => $massRemoveTagUrl,
                 'additional' => array(
                     'visibility' => array(
                         'name'   => 'tag_id',
                         'type'   => 'select',
                         'class'  => 'required-entry',
                         'label'  => $this->__('Order Tag'),
                         'values' => $tags
                     )
                 )
            )
        );

        $massResetTagsUrl = $this->getUrl(
            'admin_ordertags/adminhtml_managetags/massResetTags', array('_current' => true)
        );
        $this->getMassactionBlock()->addItem(
            'reset_ordertag',
            array(
                 'label' => $this->__('Reset all Order Tags'),
                 'url'   => $massResetTagsUrl,
            )
        );
    }

    protected function filter_tag_callback($collection, $column)
    {
        $val = $column->getFilter()->getValue();

        $_orderidtotagidTable = Mage::getSingleton('core/resource')->getTableName("ordertags/ordertotag");
        if (!@$val) {
            return;
        }
        $cond = array();
        if (@$val) {
            $cond = "ot2.tag_id = $val";
        }
        $collection
            ->getSelect()
            ->joinleft(
                array('ot2' => $_orderidtotagidTable), $this->_getSalesOrdersTableSyn() . '.entity_id = ot2.order_id',
                array()
            )
            ->where($cond)
        ;
    }

    protected function _returnOptionsList()
    {
        $optionsArray = Mage::getModel('ordertags/source_positions')->toOptionArray();
        $optionsList = array();
        foreach ($optionsArray as $value) {
            if ($value['value'] == 'none') {
                continue;
            }
            $optionsList[$value['value']] = $value['label'];
        }
        return $optionsList;
    }

    protected function _getCollection()
    {
        if (preg_match('/^1.4.0/', Mage::getVersion()) || preg_match('/^1.3/', Mage::getVersion())) {
            $collection = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
                ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left')
                ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->addExpressionAttributeToSelect(
                    'billing_name',
                    'CONCAT({{billing_firstname}}, " ", {{billing_lastname}})',
                    array('billing_firstname', 'billing_lastname')
                )
                ->addExpressionAttributeToSelect(
                    'shipping_name',
                    'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                    array('shipping_firstname', 'shipping_lastname')
                )
            ;
        } else {
            $collection = Mage::getResourceModel('sales/order_grid_collection');
        }
        return $collection;
    }

    protected function _getSalesOrdersTableSyn()
    {
        $syn = 'main_table';

        if (preg_match('/^1.4.0/', Mage::getVersion()) || preg_match('/^1.3/', Mage::getVersion())) {
            $syn = 'e';
        } elseif (preg_match('/^1.4.1/', Mage::getVersion())) {
            $syn = 'main_table';
        }

        return $syn;
    }

    public function getCsvFile()
    {
        $this->_exportFlag = true;
        return parent::getCsvFile();
    }

    public function getExcelFile($sheetName = '')
    {
        $this->_exportFlag = true;
        return parent::getExcelFile($sheetName = '');
    }
}



/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */

/**
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminhtmlSalesOrderGrid extends AW_Ordertags_Block_Adminhtml_Sales_Order_Grid {

	public function __construct() {
		parent::__construct();
		$attributeCollection = $this->getAttributeCollection(true);
//        if(count($attributeCollection)>0)
//        {
//            $this->addExportType('aitcheckoutfields/index/exportexcelcfm',Mage::helper('aitcheckoutfields')->__('EXCEL checkoutfields'));
//        }
	}

	protected function getStoreId() {
		$filter = $this->getParam($this->getVarNameFilter(), null);
		if (is_string($filter)) {
			$data = $this->helper('adminhtml')->prepareFilterString($filter);
			if (isset($data['store_id'])) {
				return $data['store_id'];
			}
		}
		return -1;
	}

	protected function getAttributeCollection($bCheckCanExport = false) {
		$iStoreId = $this->getStoreId();
		$type = 'aitoc_checkout';
		$oResource = Mage::getResourceModel('eav/entity_attribute');
		$this->type = $type;
		$attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter(Mage::getModel('eav/entity')->setType($type)->getTypeId())
		;
		if ((Mage::registry('aitcheckoutfields_excel')) || ($bCheckCanExport)) {
			$attributeCollection->getSelect()->join(
				array('additional_table' => $oResource->getTable('catalog/eav_attribute')), 'additional_table.attribute_id=main_table.attribute_id AND ait_in_excel=1'
			);
		} else {
			$attributeCollection->getSelect()->join(
				array('additional_table' => $oResource->getTable('catalog/eav_attribute')), 'additional_table.attribute_id=main_table.attribute_id AND ait_filterable=1'
			);
		}

		if ($iStoreId != -1) {
			$sWhereScope = '(find_in_set("' . $iStoreId . '", main_table.note) OR main_table.note="")';
			$attributeCollection->getSelect()->where($sWhereScope);
		}
		return $attributeCollection;
	}

	public function setCollection($collection) {
		$attributeCollection = $this->getAttributeCollection();
		$joinTable = Mage::getSingleton('core/resource')->getTableName('aitoc_order_entity_custom');
		$select = $collection->getSelect();

		foreach ($attributeCollection->getItems() as $attr) {
			if (in_array($attr['frontend_input'], array('select', 'radio'))) {
				$option_value_table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
				$select->joinLeft(
						array('aitec' . $attr['attribute_id'] . 'val' => $joinTable), "(main_table.entity_id = aitec{$attr['attribute_id']}val.entity_id AND aitec{$attr['attribute_id']}val.attribute_id = " . $attr['attribute_id'] . ")", array('aitecval' . $attr['attribute_id'] => "aitec{$attr['attribute_id']}val.value")
					)
					->joinLeft(array('aitec' . $attr['attribute_id'] => $option_value_table), "(aitec{$attr['attribute_id']}.option_id = aitec{$attr['attribute_id']}val.value AND aitec{$attr['attribute_id']}.store_id = 0)", array('aitec' . $attr['attribute_id'] . '.value' => "aitec{$attr['attribute_id']}.value")
				);
			} else {
				$select->joinLeft(
					array('aitec' . $attr['attribute_id'] . 'val' => $joinTable), "(main_table.entity_id = aitec{$attr['attribute_id']}val.entity_id AND aitec{$attr['attribute_id']}val.attribute_id = " . $attr['attribute_id'] . ")", array('aitec' . $attr['attribute_id'] . 'val.value' => "aitec{$attr['attribute_id']}val.value")
				);
			}
		}
		return parent::setCollection($collection);
	}

	protected function _prepareColumns() {
		$res = parent::_prepareColumns();
		$action = $this->_columns['action'];
		unset($this->_columns['action']);


		if (Mage::registry('aitcheckoutfields_excel')) {
			foreach ($this->_columns as $k => $v) {
				if ($k != 'real_order_id') {
					unset($this->_columns[$k]);
				}
			}
		}


		$attributeCollection = $this->getAttributeCollection();
		$i = 0;
		$checkoutFieldsModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

		$store = $this->getColumn('store_id');
		if ($store) {
			$store->setData('index', 'store_id');
			//$store->setData('renderer', 'aitcheckoutfields/widget_grid_column_renderer_store');
		}

		foreach ($attributeCollection->getItems() as $attr) {
			$i++;
			$options = $checkoutFieldsModel->getOptionValues($attr['attribute_id']);
			if (Mage::registry('aitcheckoutfields_excel')) {
				if ($attr['frontend_input'] == 'date') {
					$attr['frontend_input'] = 'text';
				}
			}
			switch ($attr['frontend_input']) {
				case 'radio':
				case 'select':
					$this->addColumn('aitec' . $attr['attribute_id'] . '_value', array(
						'header' => $attr->getData('frontend_label'),
						'index' => 'aitec' . $attr['attribute_id'] . '.value',
						'type' => 'options',
						'renderer' => 'adminhtml/widget_grid_column_renderer_longtext',
						//'renderer' => 'aitcheckoutfields/widget_grid_column_renderer_options',
						'filter' => 'aitcheckoutfields/widget_grid_column_filter_select',
						'width' => '100px',
						'options' => $options
					));

					break;
				/*
				  case 'date':
				  $this->addColumn('aitec'.$attr['attribute_id'].'val_value', array(
				  'header' => $attr->getData('frontend_label'),
				  'index'  =>'aitec'.$attr['attribute_id'].'val.value',
				  'type'   => 'date',
				  #'filter' => 'aitcheckoutfields/widget_grid_column_filter_date',
				  'width'  => '100px',
				  'format' => Mage::app()->getLocale()->getDateFormat('medium')
				  ));
				  break;
				 */
				case 'boolean':
					$this->addColumn('aitec' . $attr['attribute_id'] . 'val_value', array(
						'header' => $attr->getData('frontend_label'),
						'index' => 'aitec' . $attr['attribute_id'] . 'val.value',
						'type' => 'options',
						'width' => '100px',
						'filter' => 'aitcheckoutfields/widget_grid_column_filter_yesno',
						'options' => array(
							'1' => Mage::helper('catalog')->__('Yes'),
							'0' => Mage::helper('catalog')->__('No'),
						),
					));
					break;
				case 'multiselect':
				case 'checkbox':
					$this->addColumn('aitec' . $attr['attribute_id'] . 'val_value', array(
						'header' => $attr->getData('frontend_label'),
						'index' => 'aitec' . $attr['attribute_id'] . 'val.value',
						'renderer' => 'aitcheckoutfields/widget_grid_column_renderer_multiselect',
						'filter' => 'aitcheckoutfields/widget_grid_column_filter_multiselect',
						'filter_condition_callback' => array($checkoutFieldsModel, 'multiSelectFilter'),
						'type' => 'multiselect',
						'options' => $options,
						'width' => '100px',
						'sortable' => false
					));
					break;
				case 'textarea':
				case 'text':
					$this->addColumn('aitec' . $attr['attribute_id'] . 'val_value', array(
						'header' => $attr->getData('frontend_label'),
						'index' => 'aitec' . $attr['attribute_id'] . 'val.value',
						'type' => 'text',
						'width' => '100px'
					));
					break;
			}
		}

		$this->_columns['action'] = $action;
		$this->_columns['action']->setId('action');
		$this->_lastColumnId = 'action';


		return $res;
	}

	protected function _addColumnFilterToCollection($column) {
		if ($this->getCollection()) {
			$field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
			if ($column->getFilterConditionCallback()) {
				call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
			} else {
				$cond = $column->getFilter()->getCondition();
				if ($field && isset($cond)) {
					if (false === stripos($field, 'aitec') && false === stripos($field, 'billing_o_a')) {
						//$field = 'main_table.' . $field;
					}
					$this->getCollection()->addFieldToFilter($field, $cond);
				}
			}
		}
		return $this;
	}

}

