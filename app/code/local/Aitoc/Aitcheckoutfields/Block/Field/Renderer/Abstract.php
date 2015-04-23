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
class Aitoc_Aitcheckoutfields_Block_Field_Renderer_Abstract  extends Mage_Core_Block_Abstract
{
    public function setParams(array $data) {
        foreach($data as $key => $value)
        {
            $this->$key=$value;
        }
        return $this;
    }
    
    public function render()
    {
        return '';
    }
}

?>