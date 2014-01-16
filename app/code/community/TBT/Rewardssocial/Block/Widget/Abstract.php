<?php

abstract class TBT_Rewardssocial_Block_Widget_Abstract extends TBT_Rewardssocial_Block_Abstract
{
    protected function _prepareLayout()
    {
        $this->setFrameTags("div class='rewardssocial-widget rewardssocial-{$this->getWidgetKey()}'", "/div");
        return parent::_prepareLayout();
    }

    abstract public function getNotificationBlock();

    abstract public function getHasPredictedPoints();

    public function getWidgetKey()
    {
        return str_replace('.', '-', $this->getNameInLayout());
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if ($html != '') {
            $html .= "
                <script type='text/javascript'>
                    Event.observe(document, 'dom:loaded', function() {
                        rewardsSocialWidgetHover.addWidget('{$this->getWidgetKey()}');
                    });
                </script>
            ";
        }
        return $html;
    }
}
