<?php

class AW_Advancedreports_Block_Widget_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function renderExport(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if ( empty($actions) || !is_array($actions) ) {
            return '';
        }

        foreach ($actions as $action) {
            if ( is_array($action) ) {
                return $this->_getUrl($action, $row);
            }
        }
        return '';
    }

    protected function _getUrl($action, Varien_Object $row)
    {
        $actionCaption = '';
        $this->_transformActionData($action, $actionCaption, $row);

        if(isset($action['href'])) {
            return $action['href'];
        }
        return '';
    }
}
