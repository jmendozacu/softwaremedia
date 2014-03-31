<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profiles
 *
 * @author david
 */
class OCM_ChasePaymentTech_Model_Mysql4_Profiles extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('chasePaymentTech/chase_profiles', 'id');
	}

}
