<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Date
 *
 * @author kirichenko
 */
class Aitoc_Aitcheckoutfields_Block_Field_Renderer_Date extends Aitoc_Aitcheckoutfields_Block_Field_Renderer_Abstract 
{
    public function render() 
    {
                $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $calendar = Mage::getModel('core/layout')
                    ->createBlock('core/html_date')
                    ->setName($this->sFieldName)
                    ->setId($this->sFieldId)
                    ->setTitle($this->sLabel) 
                    ->setClass($this->sFieldClass)
                    ->setValue($this->sFieldValue, $dateFormatIso)
                    ->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'))
                    ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                return $calendar->getHtml();
    }
}

?>