<?php
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

class AW_Ordertags_Block_Adminhtml_Managetags_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        $this->setId('managetagsGrid');
        $this->setDefaultSort('tag_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        parent::__construct();
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ordertags/managetags')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('tag_id' => $row->getId()));
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'tag_id',
            array(
                 'header' => Mage::helper('ordertags')->__('ID'),
                 'index'  => 'tag_id',
                 'width'  => '50px',
                 'align'  => 'right',
            )
        );

        $this->addColumn(
            'filename',
            array(
                 'header'   => Mage::helper('ordertags')->__('Tag Icon'),
                 'index'    => 'filename',
                 'filter'   => false,
                 'sortable' => false,
                 'renderer' => 'ordertags/adminhtml_managetags_grid_column_renderer_image',
                 'width'    => '50px',
                 'align'    => 'center',
            )
        );

        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('ordertags')->__('Name'),
                 'index'  => 'name',
            )
        );

        $this->addColumn(
            'comment',
            array(
                 'header'   => Mage::helper('ordertags')->__('Comment'),
                 'index'    => 'comment',
                 'type'     => 'text',
                 'width'    => '320px',
                 'truncate' => '250',
                 'nl2br'    => true,
                 'escape'   => true,
            )
        );

        $this->addColumn(
            'sort_order',
            array(
                 'header' => Mage::helper('ordertags')->__('Sort Order'),
                 'index'  => 'sort_order',
                 'width'  => '50px',
                 'align'  => 'right',
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('ordertags')->__('Action'),
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('ordertags')->__('Edit'),
                         'url'     => array('base' => '*/*/edit'),
                         'field'   => 'tag_id'
                     ),
                     array(
                         'caption' => Mage::helper('ordertags')->__('Delete'),
                         'url'     => array('base' => '*/*/delete'),
                         'field'   => 'tag_id',
                         'confirm' => Mage::helper('ordertags')->__('Are you sure you want to do this?')
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
                 'width'     => '100px',
            )
        );
        return parent::_prepareColumns();
    }
}