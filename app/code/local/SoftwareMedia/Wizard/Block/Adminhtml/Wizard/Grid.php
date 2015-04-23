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
 * Wizard admin grid block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setId('wizardGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('softwaremedia_wizard/wizard')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Grid
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
        $this->addColumn(
            'title',
            array(
                'header'    => Mage::helper('softwaremedia_wizard')->__('Title'),
                'align'     => 'left',
                'index'     => 'title',
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
            'url_key',
            array(
                'header' => Mage::helper('softwaremedia_wizard')->__('URL key'),
                'index'  => 'url_key',
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
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('wizard');
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
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param SoftwareMedia_Wizard_Model_Wizard
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
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Wizard_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
