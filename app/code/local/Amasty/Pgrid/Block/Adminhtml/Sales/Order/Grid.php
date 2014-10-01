<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

	protected function _prepareColumns() {
		$parent =  parent::_prepareColumns();

		foreach(Mage::helper('ampgrid')->getOrderGridAttributes() as $key => $remove) {
			$this->removeColumn($remove);
		}
		
		return $parent;
	}
}

