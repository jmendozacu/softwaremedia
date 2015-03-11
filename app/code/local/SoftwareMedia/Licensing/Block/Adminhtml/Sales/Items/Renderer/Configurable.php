<?php
/**
 * Catalog product tabs controller
 *
 * @category   SoftwareMedia
 * @package	   SoftwareMedia_Substition
 * @author	   Jeff Losee
 */
class SoftwareMedia_Licensing_Block_Adminhtml_Sales_Items_Renderer_Configurable extends SoftwareMedia_Licensing_Block_Adminhtml_Sales_Items_Abstract
{
 	public function getItem()
    {
        return $this->_getData('item');//->getOrderItem();
    }
}