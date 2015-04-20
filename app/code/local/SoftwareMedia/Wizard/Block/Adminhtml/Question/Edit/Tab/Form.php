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
 * Question edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Block_Adminhtml_Question_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('question_');
        $form->setFieldNameSuffix('question');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'question_form',
            array('legend' => Mage::helper('softwaremedia_wizard')->__('Question'))
        );
        if (!$this->getQuestion()->getId()) {
            $parentId = $this->getRequest()->getParam('parent');
            if (!$parentId) {
                $parentId = Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
            }
            $fieldset->addField(
                'path',
                'hidden',
                array(
                    'name'  => 'path',
                    'value' => $parentId
                )
            );
        } else {
        	if ($this->getQuestion()->getParentId() > 1)
            		$parentId = $this->getQuestion()->getParentId();
            		
            $fieldset->addField(
                'id',
                'hidden',
                array(
                    'name'  => 'id',
                    'value' => $this->getQuestion()->getId()
                )
            );
            $fieldset->addField(
                'path',
                'hidden',
                array(
                    'name'  => 'path',
                    'value' => $this->getQuestion()->getPath()
                )
            );
        }
        $values = Mage::getResourceModel('softwaremedia_wizard/wizard_collection')
            ->toOptionArray();
        array_unshift($values, array('label' => '', 'value' => ''));

        $html = '<a href="{#url}" id="question_wizard_id_link" target="_blank"></a>';
        $html .= '<script type="text/javascript">
            function changeWizardIdLink() {
                if ($(\'question_wizard_id\').value == \'\') {
                    $(\'question_wizard_id_link\').hide();
                } else {
                    $(\'question_wizard_id_link\').show();
                    var url = \''.$this->getUrl('adminhtml/wizard_wizard/edit', array('id'=>'{#id}', 'clear'=>1)).'\';
                    var text = \''.Mage::helper('core')->escapeHtml($this->__('View {#name}')).'\';
                    var realUrl = url.replace(\'{#id}\', $(\'question_wizard_id\').value);
                    $(\'question_wizard_id_link\').href = realUrl;
                    $(\'question_wizard_id_link\').innerHTML = text.replace(\'{#name}\', $(\'question_wizard_id\').options[$(\'question_wizard_id\').selectedIndex].innerHTML);
                }
            }
            $(\'question_wizard_id\').observe(\'change\', changeWizardIdLink);
            changeWizardIdLink();
            </script>';

		if ($parentId > 1) {
			$parent = Mage::getModel('softwaremedia_wizard/question')->load($parentId);
			$fieldset->addField(
            'wizard_id',
            'hidden',
	            array(
	                'label'     => Mage::helper('softwaremedia_wizard')->__('Wizard'),
	                'name'      => 'wizard_id',
	                'required'  => false,
	                'value'    => $parent->getWizardId()
	            )
			);
		} else {
			$fieldset->addField(
            'wizard_id',
            'select',
            array(
                'label'     => Mage::helper('softwaremedia_wizard')->__('Wizard'),
                'name'      => 'wizard_id',
                'required'  => false,
                'values'    => $values,
                'after_element_html' => $html
            )
			);
        }

		$fieldset->addField(
            'title',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Title'),
                'name'  => 'title',
            'required'  => true,
            'class' => 'required-entry',

           )
        );
        if ($parentId > 1) {
	        $fieldset->addField(
		            'answer',
		            'text',
		            array(
		                'label' => 'Answer',
		                'name'  => 'answer',
			            'required'  => true,
			            'class' => 'required-entry',
						'note'  => $parent->getQuestion()
		
		           )
		        );
	    }
	    
        $fieldset->addField(
            'question',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Question'),
                'name'  => 'question'

           )
        );
		
	    $fieldset->addField(
            'comment',
            'textarea',
            array(
                'label' => Mage::helper('softwaremedia_wizard')->__('Description'),
                'name'  => 'comment'

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
        $form->addValues($this->getQuestion()->getData());
        return parent::_prepareForm();
    }

    /**
     * get the current question
     *
     * @access public
     * @return SoftwareMedia_Wizard_Model_Question
     */
    public function getQuestion()
    {
        return Mage::registry('question');
    }
}
