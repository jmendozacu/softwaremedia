<?php

/**
 * Overriding Html page block
 *
 * 
 */
class OCM_Page_Block_Html_Header extends Mage_Page_Block_Html_Header
{
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = new Mage_Customer_Helper_Data;
                $linkLogout = $customerData->getLogoutUrl();
                $customername = $this->escapeHtml(Mage::getSingleton('customer/session')->getCustomer()->getName());
                $html = 'Welcome back, <b>'.$customername.'</b> (<a class="welcome-msg-logout" href="'.$linkLogout.'">not '.$customername.'</a>?)';
                $this->_data['welcome'] = $html;
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }
} 