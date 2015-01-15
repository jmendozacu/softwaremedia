<?php
/**
 * SoftwareMedia_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Campaign
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Step admin grid block
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Campaign
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Campaign_Block_Adminhtml_Step_Grid extends Mage_Adminhtml_Block_Widget_Grid
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
        $this->setId('stepGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('softwaremedia_campaign/step')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('softwaremedia_campaign')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'campaign_id',
            array(
                'header'    => Mage::helper('softwaremedia_campaign')->__('Campaign'),
                'index'     => 'campaign_id',
                'type'      => 'options',
                'options'   => Mage::getResourceModel('softwaremedia_campaign/campaign_collection')
                    ->toOptionHash(),
                'renderer'  => 'softwaremedia_campaign/adminhtml_helper_column_renderer_parent',
                'params'    => array(
                    'id'    => 'getCampaignId'
                ),
                'base_link' => 'adminhtml/campaign_campaign/edit'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('softwaremedia_campaign')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('softwaremedia_campaign')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('softwaremedia_campaign')->__('Enabled'),
                    '0' => Mage::helper('softwaremedia_campaign')->__('Disabled'),
                )
            )
        );
        $this->addColumn(
            'sort',
            array(
                'header' => Mage::helper('softwaremedia_campaign')->__('Sort Order'),
                'index'  => 'sort',
                'type'=> 'number',

            )
        );
        $this->addColumn(
            'reminder',
            array(
                'header' => Mage::helper('softwaremedia_campaign')->__('Reminder (days)'),
                'index'  => 'reminder',
                'type'=> 'number',

            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('softwaremedia_campaign')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('softwaremedia_campaign')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('softwaremedia_campaign')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('softwaremedia_campaign')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('softwaremedia_campaign')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('softwaremedia_campaign')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Grid
     * @author Ultimate Module Creator
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('step');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('softwaremedia_campaign')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('softwaremedia_campaign')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('softwaremedia_campaign')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('softwaremedia_campaign')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('softwaremedia_campaign')->__('Enabled'),
                            '0' => Mage::helper('softwaremedia_campaign')->__('Disabled'),
                        )
                    )
                )
            )
        );
        $values = Mage::getResourceModel('softwaremedia_campaign/campaign_collection')->toOptionHash();
        $values = array_reverse($values, true);
        $values[''] = '';
        $values = array_reverse($values, true);
        $this->getMassactionBlock()->addItem(
            'campaign_id',
            array(
                'label'      => Mage::helper('softwaremedia_campaign')->__('Change Campaign'),
                'url'        => $this->getUrl('*/*/massCampaignId', array('_current'=>true)),
                'additional' => array(
                    'flag_campaign_id' => array(
                        'name'   => 'flag_campaign_id',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('softwaremedia_campaign')->__('Campaign'),
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
     * @param SoftwareMedia_Campaign_Model_Step
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
     * @return SoftwareMedia_Campaign_Block_Adminhtml_Step_Grid
     * @author Ultimate Module Creator
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}
