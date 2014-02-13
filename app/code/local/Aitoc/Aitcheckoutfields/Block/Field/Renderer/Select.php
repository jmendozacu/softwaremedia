<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
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
class Aitoc_Aitcheckoutfields_Block_Field_Renderer_Select extends Aitoc_Aitcheckoutfields_Block_Field_Renderer_Abstract 
{
    public function render() 
    {
                $select = Mage::getModel('core/layout')->createBlock('core/html_select')
                    ->setName($this->sFieldName)
                    ->setId($this->sFieldId)
                    ->setTitle($this->sLabel)
                    ->setClass($this->sFieldClass)
                    ->setValue($this->sFieldValue)
                    ->setOptions($this->aOptionHash);
                
               return $select->getHtml();
    }
}

?>