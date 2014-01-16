<?php

class OCM_Frauddetection_Block_Adminhtml_Sales_Order_View_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $html = '<a href="javascript:void(0);" field="invoice_id" onclick="addSub'.$value.'('.$value.');">Add Sub</a>';
        $html .= '<script type="text/javascript">
                        function addSub'.$value.'(_value){
                            $("addsub" + _value).update("please choose sub additional information!");
                        }
                  </script>';

        return $html;
    }
}