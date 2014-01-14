<?php
class OCM_Advancedstaticleftnav_Block_Tree extends Mage_Core_Block_Template
{
    public function get_category_path($category_id) {
        Mage::helper('core/url')->getHomeUrl().Mage::getModel('catalog/category')->load($tree_section['related_category_id'])->getUrlPath();
    }
    public function getTrees($category_id) {
        // Key tree names
        $tree_names = Mage::getModel('advancedstaticleftnav/lntree')->getCollection()->toArray();
        $tree_names = $tree_names['items'];
        $keyed_names = array();
        foreach($tree_names as $name) {
            $keyed_names[$name['id']] = $name['title'];
        }

        // Key the array based off of the tree id's
        $trees = Mage::getModel('advancedstaticleftnav/lnobject')->getCollection()
            ->addFieldToFilter('category_id', $category_id)
            ->toArray();
        array_shift($trees);
        $keyed_tree = array();
        foreach($trees['items'] as $tree_section) {
            $tid = $keyed_names[$tree_section['tree_id']];
            if(!isset($keyed_tree[$tid])) {
                $keyed_tree[$tid] = array();
            }
            $keyed_tree[$tid][] = $tree_section;
        }

        return $keyed_tree;
    }
}
