<?php

class AW_Advancedreports_Block_Adminhtml_Setup_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Setup Instace
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function getSelect()
    {
        return Mage::helper('advancedreports/setup');
    }

    protected function _setTypes($fieldset)
    {
        $fieldset->addType('ar_select', 'AW_Advancedreports_Model_Form_Element_Select');
        $fieldset->addType('ar_multiselect', 'AW_Advancedreports_Model_Form_Element_Multiselect');
        $fieldset->addType('ar_text', 'AW_Advancedreports_Model_Form_Element_Text');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $statuses = Mage::getModel('advancedreports/system_config_source_statuses');
        $datefields = Mage::getModel('advancedreports/system_config_source_datefilter');

        /**
         * Select options
         */
        $fieldset = $form->addFieldset(
            'advancedreports_select', array('legend' => Mage::helper('advancedreports')->__('Select options'))
        );

        $this->_setTypes($fieldset);

        $fieldset->addField(
            'process_orders',
            'ar_multiselect',
            array(
                'label'  => Mage::helper('advancedreports')->__('Process orders'),
                'title'  => Mage::helper('advancedreports')->__('Process orders'),
                'name'   => 'process_orders',
                'values' => $statuses->toOptionArray(),
            )
        );

        $fieldset->addField(
            'order_datefilter',
            'ar_select',
            array(
                'label'  => Mage::helper('advancedreports')->__('Select orders by'),
                'title'  => Mage::helper('advancedreports')->__('Select orders by'),
                'name'   => 'order_datefilter',
                'values' => $datefields->toOptionArray(),
            )
        );

        if (count($options = $this->getSelect()->getGrid()->getCustomOptionsRequired())) {
            foreach ($options as $option) {
                if (!isset($option['hidden']) || !$option['hidden']) {
                    $fieldset->addField($option['id'], $option['type'], $option['args']);
                }
            }
        }

        /**
         * Filter Options
         */
        $fieldset = $form->addFieldset(
            'advancedreports_filter', array('legend' => Mage::helper('advancedreports')->__('Filter options'))
        );

        $this->_setTypes($fieldset);

        $fieldset->addField(
            'recently_filter_count',
            'ar_text',
            array(
                'label' => Mage::helper('advancedreports')->__('Number of latest custom date ranges'),
                'title' => Mage::helper('advancedreports')->__('Number of latest custom date ranges'),
                'name'  => 'recently_filter_count',
                'class' => 'validate-number',
            )
        );

        $form->setValues(Mage::registry('setup_data'));
        $this->setForm($form);
        parent::_prepareForm();
    }
}