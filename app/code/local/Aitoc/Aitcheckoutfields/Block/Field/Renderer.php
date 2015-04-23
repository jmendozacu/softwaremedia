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
class Aitoc_Aitcheckoutfields_Block_Field_Renderer  extends Mage_Core_Block_Abstract
{
        protected $_renderer;
        protected $_type;
        protected $_params=array();
        
        public function setType($type) {
            $this->_type = $type;
            return $this;
        }
        
        public function setParams(array $params)
        {
            $this->_params = $params;
            return $this;
        }
        
        protected function _getRendererByType()
        {
            $type = strtolower($this->_type);
            return 'aitcheckoutfields/field_renderer_'.$type;
        }
        
        public function _getRenderer()
        {
            if(!$this->_renderer)
            {
                $this->_renderer = $this->getLayout()->createBlock($this->_getRendererByType())->setParams($this->_params);
            }
            return $this->_renderer;
        }
        
        public function render()
        {
            return $this->_getRenderer()->render();
        }
        
}

?>