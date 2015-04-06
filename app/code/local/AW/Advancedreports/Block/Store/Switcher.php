<?php

class AW_Advancedreports_Block_Store_Switcher extends Mage_Adminhtml_Block_Store_Switcher
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('advancedreports/store/switcher.phtml');
    }

    public function clearParams($params)
    {
        $params['_current'] = true;
        foreach (array('store', 'group', 'website') as $key) {
            $params[$key] = null;
        }
        return $params;
    }

    public function getSwitchUrl()
    {
        return $this->getUrl('*/*/*', $this->clearParams($this->getRequest()->getParams()));
    }
}