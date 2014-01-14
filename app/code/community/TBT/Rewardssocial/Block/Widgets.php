<?php

class TBT_Rewardssocial_Block_Widgets extends TBT_Rewardssocial_Block_Abstract
{
    protected $_pointsBlock = null;

    public function getPointsNotificationBlock()
    {
        if ($this->_pointsBlock === null) {
            $this->_pointsBlock = $this->_fetchPointsNotificationBlock();
        }

        return $this->_pointsBlock;
    }

    protected function _fetchPointsNotificationBlock()
    {
        $pointsBlock = $this->getLayout()->createBlock('rewardssocial/widgets_points');

        foreach ($this->getSortedChildren() as $name) {
            $widget = $this->getLayout()->getBlock($name);

            // do not try to add points notification block is button is not enabled
            if ($widget->getIsHidden()) {
                continue;
            }

            if ($widget->getHasPredictedPoints()) {
                $pointsBlock->setHasPredictedPoints(true);
                $pointsBlock->addPointsNotification($name, $widget->getNotificationBlock());
            }
        }

        return $pointsBlock;
    }
}
