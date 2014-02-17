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
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitcheckoutfields_Block_Widget_Grid_Column_Renderer_Multiselect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if(count($itemsArr = explode(',', $value))>0)
            {
                $res = array();
                foreach ($itemsArr as $item) {
                    if (isset($options[$item])) {
                        $res[] = $options[$item];
                    }
                    elseif($showMissingOptionValues) {
                        $res[] = $item;
                    }
                }
                return implode(', ', $res);
            }
            else return $value;
        }
    }
}