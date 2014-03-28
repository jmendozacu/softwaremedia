<?php
class OCM_Catalog_Model_Container_Category extends Enterprise_PageCache_Model_Container_Abstract
{

    protected function _getIdentifier()
    {
    	
        Mage::log('Inside getIdentifier', null, 'cache.log');
        $cacheId = $this->_getCookieValue('softwaremedia_ovchn', '');
        return $cacheId;
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        Mage::log('Inside getCacheId', null, 'cache.log');
        return 'CATEGORY_VIEW_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        Mage::log('Inside renderBlock', null, 'cache.log');
        var_dump($this->_placeholder);
        $blockClass = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $blockClass;
        $block->setTemplate($template);
        
        $block->setData('current_category', Mage::getModel('catalog/category')->load($this->_placeholder->getAttribute('category_id')));
        $block->setLayout(Mage::app()->getLayout());
        return $block->toHtml();
    }


    //protected function _saveCache($data, $id, $tags = array(), $lifetime = null) { return false; }    

}