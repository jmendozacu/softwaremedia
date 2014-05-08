<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reports
 *
 * @author david
 */
class SoftwareMedia_Swmreports_Model_Swmreports extends Mage_Reports_Model_Mysql4_Product_Collection {

	function __construct() {
		parent::__construct();
		$this->setResourceModel('catalog/products');
		$this->_init('catalog/products', 'products_id');
	}

}
