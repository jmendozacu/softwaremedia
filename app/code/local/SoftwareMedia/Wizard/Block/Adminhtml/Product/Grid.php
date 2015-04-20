<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Product admin grid block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author Ultimate Module Creator
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Product_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('softwaremedia_wizard/product')
            ->getCollection();
        $collection->getSelect()->joinLeft(
				'softwaremedia_wizard_question', 'main_table.question_id = softwaremedia_wizard_question.entity_id', array('question_id'=>'entity_id')
			);
			
		$collection->getSelect()->joinLeft(
				'softwaremedia_wizard_wizard', 'softwaremedia_wizard_question.wizard_id = softwaremedia_wizard_wizard.entity_id', array('wizard_id'=>'entity_id')
			);
				
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Product_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('softwaremedia_wizard')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        
        $wizards = Mage::getModel('softwaremedia_wizard/wizard')->getCollection();
        $values = array();
        foreach($wizards as $wizard) {
        	echo $wizard->getTitle();
	    	$values[$wizard->getId()] = $wizard->getTitle();    
        }
        
            
         $this->addColumn(
            'wizard_id',
            array(
                'header'    => Mage::helper('softwaremedia_wizard')->__('Wizard'),
                'align'     => 'left',
                'index'     => 'wizard_id',
                'type'		=> 'options',
                'options'	=> $values,
                'filter_index' => 'softwaremedia_wizard_wizard.entity_id'
            )
        );
        
        $this->addColumn(
            'question_id',
            array(
                'header'    => Mage::helper('softwaremedia_wizard')->__('Question'),
                'index'     => 'question_id',
                'type'      => 'options',
                'options'   => Mage::getResourceModel('softwaremedia_wizard/question_collection')
                    ->toOptionHash(),
                'renderer'  => 'softwaremedia_wizard/adminhtml_helper_column_renderer_parent',
                'params'    => array(
                    'id'    => 'getQuestionId'
                ),
                'static' => array(
                    'clear' => 1
                ),
                'base_link' => 'adminhtml/wizard_question/edit'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('softwaremedia_wizard')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('softwaremedia_wizard')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('softwaremedia_wizard')->__('Enabled'),
                    '0' => Mage::helper('softwaremedia_wizard')->__('Disabled'),
                )
            )
        );
        $this->addColumn(
            'sku',
            array(
                'header' => Mage::helper('softwaremedia_wizard')->__('SKU'),
                'index'  => 'sku',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('softwaremedia_wizard')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'updated_at',
            array(
                'header'    => Mage::helper('softwaremedia_wizard')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('softwaremedia_wizard')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('softwaremedia_wizard')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('softwaremedia_wizard')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('softwaremedia_wizard')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('softwaremedia_wizard')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Product_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('softwaremedia_wizard')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('softwaremedia_wizard')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('softwaremedia_wizard')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('softwaremedia_wizard')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('softwaremedia_wizard')->__('Enabled'),
                            '0' => Mage::helper('softwaremedia_wizard')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $values = Mage::getResourceModel('softwaremedia_wizard/question_collection')->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        $this->getMassactionBlock()->addItem(
            'question_id',
            array(
                'label'      => Mage::helper('softwaremedia_wizard')->__('Change Question'),
                'url'        => $this->getUrl('*/*/massQuestionId', array('_current'=>true)),
                'additional' => array(
                    'flag_question_id' => array(
                        'name'   => 'flag_question_id',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('softwaremedia_wizard')->__('Question'),
                        'values' => $values
                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Product
     * @return string
     * @author Ultimate Module Creator
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author Ultimate Module Creator
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Product_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
