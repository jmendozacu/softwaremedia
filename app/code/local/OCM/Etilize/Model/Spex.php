<?php

/**
 * Suite of classes to interact with the SpexLive API.
 * 
 * @package Spex
 */

interface iSpex
{
	public function __construct($id);
	public function spexRequest($method, $options);
}

interface iSpexCatalog
{
	public function getProductForId($mfgId, $partNumber);
}

interface iSpexProduct
{
	public function __construct($data);
	public function getAttributes();
}

/**
 * Provides the base level network wrapper to SpexLive API.
 * 
 * @abstract
 */
abstract class Spex implements iSpex
{
	const REQUEST_URL = 'http://ws-na1.spexlive.net/service/rest/';
	
	protected $app_id = null;
	protected $default_options = array('locale' => 'en_us', 'catalog' => 'na');
	protected $service = '';
	
	public function __construct($id)
	{
		if (!$this->checkIdType($id))
			Mage::log("App ID must not be null and must be of type int.", null, 'OCM_Spex.log');
		
		$this->app_id = $id;
	}
	
	public function spexRequest($method, $options)
	{	
		if (!$this->checkIdType($this->app_id))
			Mage::log('App ID must not be null and must be of type int.',null,"OCM_Spex.log");
		
		$options = array_merge(array('appId' => $this->app_id, 'method' => $method), $options);
		$url = self::REQUEST_URL . $this->service . '?' . http_build_query($options);
		
		echo "\n\n".$url."\n\n";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
	
	protected function checkIdType($id)
	{
		if (!empty($id) && is_int($id))
			return true;
	}
	
	abstract protected function getOptions();
}

/**
 * Interact with the "catalog" method on the API.
 *
 * SpexCatalog extends the abstract class, Spex. It defines additional
 * methods for interacting with the API, particularly the "catalog"
 * path of the API.
 */
class SpexCatalog extends Spex implements iSpexCatalog
{
	protected $service = 'catalog';
	
	/**
	 * Call the catalog API and request a product by it's ID
	 * 
	 * @param int [$id] The ID of the product
	 * 
	 * @return SpexProduct An instance of a SpexProduct object
	 * 
	 * @throws Exception The options property is empty or not an array
	 * @throws Exception The product ID is null or not of type int
	 */
	public function getProductForId($mfgId, $partNumber)
	{
		if (empty($this->default_options) || !is_array($this->default_options))
			Mage::log('Options must not be empty and must be an array.',null,"OCM_Spex.log");
		
		if (!$this->checkIdType($mfgId))
			Mage::log('Mfg ID must not be null and must be of type int.',null,"OCM_Spex.log");
		
		try {
			//Get the default options for products, and merge those with the product ID attribute.
			$options = array_merge(array('mfgId' => $mfgId, 'partNumber' => $partNumber), $this->getOptions());
			$request = $this->spexRequest('getProduct', $options);
		}
		catch (Exception $e)
		{
			//mage::log($e,null,"OCM+Spex.log");
			return false;
		}
		
		return new SpexProduct($request);
	}
	
	
	/**
	 * Definition of the abstract method declared in the Spex class.
	 * 
	 * @return array Options for interacting with the API.
	 */
	protected function getOptions()
	{
		$options = array_merge(
			$this->default_options,
			array(
				'descriptionTypes' => 'none',
				'categories' => 'default',
				//'displayTemplate' => 1,
				'dataSheet' => 'basic',
				'categorizeAccessories' => 'false',
				'skuType' => 'all',
				'resourceType' => 'All'
			)
		);
		
		return $options;
	}	
}

/**
 * Represents a Product in it's purest form
 *
 * This class is a simple wrapper around a returned product
 * result from the API. It stores a reference to the raw (xml) string
 * returned from the request. It also generates a SimpleXMLElement object
 * from the raw string.
 * 
 * This class also defines a convenience method for extracting the attributes
 * from a product. To get more information about the product, some manual work
 * is required. The 'obj' property is protected an can only be accessed by
 * by subclassing or calling the getter, getObj().
 */
class SpexProduct implements iSpexProduct
{	
	protected $raw = null;
	protected $obj = null;
	
	public function __construct($data)
	{	
		$this->raw = $data;
		
		try 
		{
			$this->obj = new SimpleXMLElement($data);
		}
		catch (Exception $e)
		{
			Mage::log($e,null,"OCM_Spex.log");
			$this->obj = null;
		}
	}
	
	public function __toString() {
		return $this->raw;
	}
	
	public function getObj() {
		return $this->obj;
	}
	
	public function getAttributes()
	{
		foreach($this->obj->datasheet->attributeGroup as $group) 
			$attributes[] = $group;

		return $attributes;
	}

public function getSkus()
    {
        foreach($this->obj->skus as $sku)
			$skus[] = $sku;

		return $skus;
    }
	
public function getResources()
	{
		foreach($this->obj->resources->resource as $resource)
			$resources[] = $resource;
		
		return $resources;
	}
	
}
