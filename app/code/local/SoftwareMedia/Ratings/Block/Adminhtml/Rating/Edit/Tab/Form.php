<?php
/**
 * SoftwareMedia_Ratings extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Ratings
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Rating edit form tab
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Ratings
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Ratings_Block_Adminhtml_Rating_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return SoftwareMedia_Ratings_Block_Adminhtml_Rating_Edit_Tab_Form
     * @author Ultimate Module Creator
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rating_');
        $form->setFieldNameSuffix('rating');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'rating_form',
            array('legend' => Mage::helper('softwaremedia_ratings')->__('Rating'))
        );

        $fieldset->addField(
            'user_id',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_ratings')->__('Admin User ID'),
                'name'  => 'user_id',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'customer_id',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_ratings')->__('Customer ID'),
                'name'  => 'customer_id',

           )
        );

        $fieldset->addField(
            'ip',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_ratings')->__('IP Address'),
                'name'  => 'ip',

           )
        );

        $fieldset->addField(
            'rating',
            'text',
            array(
                'label' => Mage::helper('softwaremedia_ratings')->__('Rating'),
                'name'  => 'rating',
            'required'  => true,
            'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'comment',
            'textarea',
            array(
                'label' => Mage::helper('softwaremedia_ratings')->__('Comment'),
                'name'  => 'comment',

           )
        );

        $formValues = Mage::registry('current_rating')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getRatingData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getRatingData());
            Mage::getSingleton('adminhtml/session')->setRatingData(null);
        } elseif (Mage::registry('current_rating')) {
            $formValues = array_merge($formValues, Mage::registry('current_rating')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
