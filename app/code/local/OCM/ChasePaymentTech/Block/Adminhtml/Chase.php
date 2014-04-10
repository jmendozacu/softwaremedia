<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class OCM_ChasePaymentTech_Block_Adminhtml_Chase
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Columns, that should be removed from grid
     *
     * @var array
     */
    protected $_columnsToRemove = array('visitor_session_id', 'protocol', 'customer_id');

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('chase/payment.phtml');
        
    }
        protected function _prepareLayout()
    {
    	$customer = Mage::registry('current_customer');
		$subtmitUrl = $this->getUrl("*/chase/add",array('customer' => $customer->getId()));
        $this->setChild('add_payment_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'  => Mage::helper('customer')->__('Add New Payment'),
                    'id'     => 'add_payment_button',
                    'name'   => 'add_payment_button',
                    'element_name' => 'add_payment_button',
                    'class'  => 'add',
                    'onclick'=> '$(\'edit_form\').writeAttribute(\'action\',\'' . $subtmitUrl . '\'); editForm.submit();'
                ))
        );
        return parent::_prepareLayout();
    }


    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_payment_button');
    }

    public function isReadonly()
    {
        $customer = Mage::registry('current_customer');
        return $customer->isReadonly();
    }

    protected function _beforeToHtml()
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::registry('current_customer');

        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('address_fieldset', array(
            'legend'    => Mage::helper('customer')->__("Add Payment Information"))
        );
		$fieldset->addField('cc_number', 'text',
                    array(
                        'label' => Mage::helper('customer')->__('Card Number'),
                        'name'  => 'cc_number',
                        'class' => 'input_text validate-cc-number'
                    )
                );
				
		$fieldset->addField('card_type', 'select',
                    array(
                        'label' => Mage::helper('customer')->__('Card Type'),
                        'name'  => 'card_type',
                        'class' => 'input_text',
                        'style' => 'width: 8em;',
                        'options' => $this->getCcAvailableTypes()
                    )
                );
        $fieldset->addField('cc_exp_month', 'select',
                    array(
                        'label' => Mage::helper('customer')->__('Exp Month'),
                        'name'  => 'cc_exp_month',
                        'class' => 'input_text',
                        'style' => 'width: 8em;',
                        'options' => $this->getCcMonths()
                    )
                );
        $fieldset->addField('cc_exp_year', 'select',
                    array(
                        'label' => Mage::helper('customer')->__('Exp Year'),
                        'name'  => 'cc_exp_year',
                        'class' => 'input_text',
                        'style' => 'width: 5em;',
                        'options' => $this->getCcYears()
                    )
                );
                
        $fieldset->addField('cc_cid', 'text',
                    array(
                        'label' => Mage::helper('customer')->__('CVN'),
                        'name'  => 'cc_number',
                        'style' => 'width: 5em;',
                        'class' => 'input_text validate-cc-cvn'
                    )
                );
                
        $paymentCollection =Mage::getModel('chasePaymentTech/profiles')->getCollection()->addFieldToFilter('customer_id',$customer->getId());
        $this->assign('customer', $customer);
        $this->assign('paymentCollection', $paymentCollection);
        $this->setForm($form);

        return $this;
    }
    
    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        if ($method = $this->getMethod()) {
            $availableTypes = $method->getConfigData('cctypes');
            if ($availableTypes) {
                $availableTypes = explode(',', $availableTypes);
                foreach ($types as $code=>$name) {
                    if (!in_array($code, $availableTypes)) {
                        unset($types[$code]);
                    }
                }
            }
        }
        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = $this->_getConfig()->getYears();
            $years = array(0=>$this->__('Year'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }
    
    /**
     * Defines after which tab, this tab should be rendered
     *
     * @return string
     */
    public function getAfter()
    {
        return 'account';
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('MailChimp List Member Activity');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('MailChimp List Member Activity');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $customer = Mage::registry('current_customer');
        return (bool)$customer->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
    	//TODO: Hide if MageMonkey is disabled
        return false;
    }

    protected function _prepareCollection()
    {
    	$customer = Mage::registry('current_customer');

		$collection = Mage::getModel('chasePaymentTech/profiles')->getCollection();
		$collection->addFieldToFilter('customer_id',$customer->getId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('card_type', array(
            'header'=> Mage::helper('chasePaymentTech')->__('Card Type'),
            'index' => 'card_type',
            'sortable' => false
        ));        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}