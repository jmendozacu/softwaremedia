<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product Predict Points
 * options: 
 * - setHideEarning(true); // hides earning line.
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Block_Product_Predictpoints extends TBT_Rewards_Block_Product_List_Abstract {
	
	
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'rewards/product/predictpoints.phtml' );
		//@nelkaake Wednesday March 10, 2010 10:04:42 PM : New caching functionality 
		$this->setCacheLifetime ( 86400 );
		$this->setName("rewards_catalog_product_list_predictpoints");
	}

	/**
	 * Set the product to create the predict points block for.
	 *
	 * @param TBT_Rewards_Model_Catalog_Product $_product
	 */
	public function setProduct($_product) {
	    parent::setProduct($_product);

	    $this->setCacheKey ( $this->_genCacheKey () );
		return $this;
	}
	
	/**
	 * @return string key specic to this cached content
	 * @nelkaake -a 5/11/10:
	 */
	protected function _genCacheKey() {
            
            $nameInLayout = $this->getNameInLayout();
            $blockType = $this['type'];
            $product_id = $this->getProduct ()->getId ();
            $website_id = Mage::app ()->getWebsite ()->getId ();
            $customer_group_id = $this->_getRS ()->getCustomerGroupId ();
            $lang = Mage::getStoreConfig('general/locale/code');

            $key = "rewards_product_predictpoints_{$nameInLayout}_{$blockType}_{$product_id}_{$website_id}_{$customer_group_id}_{$lang}";

            if ($this->_getRS ()->isCustomerLoggedIn ()) {
    		    $customer = Mage::getModel('rewards/customer')->getRewardsCustomer($this->_getRS()->getCustomer());
    			$pts = (string)$customer->getPointsSummary();
                $pts = strtolower(str_replace(' ', '_', $pts));
                $pts = preg_replace ( '/[^a-z0-9_]/', '', $pts );
                $key = $key . "_{$pts}";
            }            
            return $key;
	}

    /**
     * Fetches the HTML for the components that appear within the 'predict points' block.
     * @return string HTML
     */
    public function getPartsHtml() {
        $html = "";
        
        $blocks = $this->_getPredictpointsChildren();
        
        // use each flyweight to generate the inner block content and append to the overall HTML
        foreach ($blocks as $index => $block) {
            $block_html = $block->toHtml();
            
            $html .= $block_html;
        }
        
        return $html;
    
    }

    /**
     * 
     * Fetches an array  of Flyweight Block classes that are used to render what to display under each product block in the HTML
     * output.
     * @return array(TBT_Rewards_Block_Product_List_Abstract)
     */
    protected function _getPredictpointsChildren() {
        
        // Load the flyweight block classes from the global layout.
        $flyweight_nodes = $this->getLayout()->getXpath( "//reference[@name='rewards_catalog_product_list_predictpoints']" );
        
        // if no flyweight were found, return back.
        if ( sizeof( $flyweight_nodes ) <= 0 ) return array();
        
        // Find and sort the children nodes
        $flyweight_children = array();
        $last_priority = 0;
        foreach ($flyweight_nodes as $flyweight_node) {
            foreach ($flyweight_node->children() as $child) {
                if ( ! isset( $child['priority'] ) ) $child['priority'] = $last_priority;
                
                $priority = (int) $child['priority'];
                $child['name'] = $child['name'] . $last_priority;
                $flyweight_children[$priority] = $child;
                $last_priority = $priority+1;
            }
        }
        
        // Sort by priority (index of the array).
        ksort( $flyweight_children );
        
        // Generate the blocks from this block's layout system.
        $blocks = array();
        foreach ($flyweight_children as $child_elem) {
            if ( ! isset( $child_elem['type'] ) ) continue;
            if ( ! isset( $child_elem['name'] ) ) continue;
            if ( ! isset( $child_elem['template'] ) ) continue;
            
            $block = $this->getLayout()
                ->createBlock( (string) $child_elem['type'], (string) $child_elem['name'] )
                ->setTemplate( (string) $child_elem['template'] )
                ->setProduct( $this->getProduct() );

            $blocks[] = $block;
            
        }
        
        return $blocks;
    }
	
	
}

