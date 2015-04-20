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
 * Product edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Product_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Product_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('product_');
        $form->setFieldNameSuffix('product');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'product_form',
            array('legend' => Mage::helper('softwaremedia_wizard')->__('Product'))
        );
        
        $values = array();
        $questions = Mage::getModel('softwaremedia_wizard/question')->getCollection();
        
        foreach($questions as $question) {
        	
	        $children = Mage::getModel('softwaremedia_wizard/question')->getCollection()->addFieldToFilter('parent_id',$question->getId());
	        
	        if ($children->getSize() == 0) {
	        	$qId = $question->getId();
		        $name = $question->getTitle();
		        while($question->getParentId()>1) {
			        $question->load($question->getParentId());
			        $name = $question->getTitle() . " > " . $name;
		        }
		        $values[] = array('value' => $qId,'label' => $name);
	        }
	        	
	        
        }
           
            
            
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="product_question_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeQuestionIdLink() {
                if ($(\'product_question_id\').value == \'\') {
                    $(\'product_question_id_link\').hide();
                } else {
                    $(\'product_question_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/wizard_question/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'product_question_id\').value);
                    $(\'product_question_id_link\').href = realUrl;
                    $(\'product_question_id_link\').innerHTML = text.replace(\'{#name}\', $(\'product_question_id\').options[$(\'product_question_id\').selectedIndex].innerHTML);
                }
            }
            $(\'product_question_id\').observe(\'change\', changeQuestionIdLink);
            changeQuestionIdLink();
            </script>';

        $fieldset->addField(
            'question_id',
            'select',
            array(
                'label'     => Mage::helper('softwaremedia_wizard')->__('Question'),
                'name'      => 'question_id',
                'required'  => false,
                'values'    => $values,
                'after_element_html' => $html
            )
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Name'),
                'name'  => 'name',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'sku',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('SKU'),
                'name'  => 'sku',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Description'),
                'name'  => 'description',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        $fieldset->addField(
            'status',
            'select',
            array(
                'label'  => Mage::helper('softwaremedia_wizard')->__('Status'),
                'name'   => 'status',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('softwaremedia_wizard')->__('Enabled'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('softwaremedia_wizard')->__('Disabled'),
                    ),
                ),
            )
        );
        $formValues = Mage::registry('current_product')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getProductData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getProductData());
            Mage::getSingleton('adminhtml/session')->setProductData(null);
        } elseif (Mage::registry('current_product')) {
            $formValues = array_merge($formValues, Mage::registry('current_product')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
