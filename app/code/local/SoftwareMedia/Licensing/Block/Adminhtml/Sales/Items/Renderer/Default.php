<?php
/**
 * Catalog product tabs controller
 *
 * @category   SoftwareMedia
 * @package	   SoftwareMedia_Substition
 * @author	   Jeff Losee
 */
class SoftwareMedia_Licensing_Block_Adminhtml_Sales_Items_Renderer_Default extends Mage_Adminhtml_Block_Sales_Items_Renderer_Default
{
	public function isLicense() {
		$item = $this->getItem();
		echo $item->getId();
	}
}