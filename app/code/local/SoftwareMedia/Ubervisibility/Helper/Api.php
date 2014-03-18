<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Api
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Helper_Api extends Mage_Core_Helper_Abstract {

	public function callApi($method, $uri, array $variables = array()) {
		$curl = new Varien_Http_Adapter_Curl();
		$curl->setConfig(array(
			'timeout' => 15 //Timeout in no of seconds
		));

		$url = 'http://ubervisibility.com:8080/v1/' . $uri;
		$curl->write($method, $url, '1.1', array('Accept: application/json', 'Content-Type: application/json'), json_encode($variables));
		$data = $curl->read();
		$curl->close();

		if ($data === false) {
			return false;
		}
		$data = preg_split('/^\r\n/m', $data, 2);
		$data = trim($data[1]);

		return json_decode($data);
	}

}
