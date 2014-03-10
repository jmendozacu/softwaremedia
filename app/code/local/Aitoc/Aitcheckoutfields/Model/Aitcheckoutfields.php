<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Aitcheckoutfields extends Mage_Eav_Model_Entity_Attribute
{
    protected $_aCheckoutAtrrList;
    protected $_aCustomerAtrrList;
    protected $_sEntityTypeCode     = 'aitoc_checkout';
    protected $_sCustomAttrTable    = 'aitoc_order_entity_custom';
    protected $_sCustomerAttrTable  = 'aitoc_customer_entity_data';
    protected $_sDescAttrTable      = 'aitoc_custom_attribute_description';
    protected $_sNeedSelectTable    = 'aitoc_custom_attribute_need_select';
    protected $_sCustomRecProfileAttrTable = 'aitoc_recurring_profile_entity_custom';
    
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->_sCustomAttrTable    = $resource->getTableName('aitoc_order_entity_custom');
        $this->_sCustomerAttrTable  = $resource->getTableName('aitoc_customer_entity_data');
        $this->_sDescAttrTable      = $resource->getTableName('aitoc_custom_attribute_description');
        $this->_sNeedSelectTable    = $resource->getTableName('aitoc_custom_attribute_need_select');
        $this->_sCustomRecProfileAttrTable = $resource->getTableName('aitoc_recurring_profile_entity_custom');
        return parent::__construct();
    }

    
    public function getAttributeLabel($iAttributeId, $iStoreId = 0)
    {
        if (!$iAttributeId) return false;
        
		$oAttribute  = Mage::getModel('eav/entity_attribute');
		$oAttribute->load($iAttributeId);

		if (!$oAttribute->getData()) return false;
		
		if (!$iStoreId)
		{
		    $iStoreId = Mage::app()->getStore()->getId();
		}
		
        $values = array();
        $values[0] = $oAttribute->getFrontend()->getLabel();
        // it can be array and cause bug
        
        $frontendLabel = $oAttribute->getFrontend()->getLabel();
        if (is_array($frontendLabel)) {
            $frontendLabel = array_shift($frontendLabel);
        }
        
        $storeLabels = $oAttribute->getStoreLabels();
        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
        }
        
        if (isset($values[$iStoreId]) AND $values[$iStoreId])
        {
            $sLabel = $values[$iStoreId];
        }
        else 
        {
            $sLabel = $values[0];
        }
        
        return $sLabel;
    }    
    
    
    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }
        return $stores;
    }

    public function getAttributeOptionValues($sFieldId, $iStoreId = 0, $aOptionIdList)
    {
        if (!$sFieldId OR !isset($iStoreId) OR !$aOptionIdList) return false;
        
        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($sFieldId)
            ->setStoreFilter($iStoreId, true)
            ->load();   

        if (!is_array($aOptionIdList))
        {
            $aOptionIdList = explode(',',$aOptionIdList);
        }            

        if (empty($aOptionIdList))
        {
            $aOptionIdList = array($aOptionIdList);
        }
            
        $aValueList = array();
        
        foreach ($valuesCollection as $item) 
        {   $id = $item->getId();
            if(empty($id))$id = $item->getOptionId();
            if (in_array($id, $aOptionIdList)) 
            {
                $aValueList[] = $item->getValue();
            }
        }
        return $aValueList;
    }
    
    public function getAttributeHtml($aField, $sSetName, $sPageType, $iStoreId = 0, $bForAdmin = false, $bLeft = false)
    {


       if (!$iStoreId)
       {
       		$iStoreId = Mage::app()->getStore()->getId();
       }
        
            	
        $oView = new Zend_View();

        $iItemId = $aField['attribute_id'];
        $sPrefix = 'aitoc_checkout_';

        $sFieldId = $sSetName . ':' . $sPrefix . $iItemId;
        
        $sLabel = $this->getAttributeLabel($iItemId, $iStoreId);
        
		$sLiClass = 'fields' . $iItemId;
		
        $sHtml = '<li class="' . $sLiClass . '" style="clear:both;"><div class="field'.$iItemId.'" style="padding: 5px'.(($aField['frontend_input']==='static')?';width:auto':'').'">';
        
        $sHtml .= '<label '. ($bLeft ? 'style="text-align:left; width:100%;"' :'') . ' for="' . $sFieldId . '"';
        
        if (!$bForAdmin)
        {
            //if ($aField['is_required'])
            if (!strpos($sLabel,'Prior'))
            {
                $sHtml .= ' class="required"><em>*</em>';
            }
            else 
            {
                $sHtml .= '>';
            }
            
            $sHtml .= $sLabel;
        }
        else 
        {
            $sHtml .= '>' .$sLabel;
            
            if ($aField['is_required'])
            {
                $sHtml .= ' <span class="required">*</span>';
            }
        }
        
        $sHtml .= '</label><div class="input-box"'.(($aField['frontend_input']==='static')?' style="width:380px"':'').'> <table><tr><td class="value">';

        $sFieldName     = $sSetName . '[' . $sPrefix . $iItemId . ']';

        $sFieldValue = $this->getCustomValue($aField, $sPageType);
        
        $sFieldValue    = is_array($sFieldValue) ? $sFieldValue : htmlspecialchars_decode($sFieldValue);

        $sFieldClass = '';
        
        if ($aField['frontend_class'])
        {
            $sFieldClass .= $aField['frontend_class'];
        }
        
        if ($aField['is_required'])
        {
            $sFieldClass .= ' required-entry';
        }
        
        $aParams = array
        (
            'id' => $sFieldId,
#                    'class' => 'validate-zip-international required-entry input-text', // to do - check
            'class' => $sFieldClass, // to do - check
            'title' => $sLabel,
        );
                
        $aOptionHash = $this->getOptionValues($iItemId, $iStoreId);
        
        
        // add 'please select' value to option list
         
        if (!empty($aField['used_in_product_listing']) AND $aField['frontend_input'] != 'multiselect')
        {
            
            $aTitleHash = $this->getAttributeNeedSelect($iItemId);
            
            if ($aTitleHash AND isset($aTitleHash[$iStoreId]) AND $aTitleHash[$iStoreId])
            {
                $sNeedSelectTitle = $aTitleHash[$iStoreId];
            }
            elseif ($aTitleHash) 
            {
                $sNeedSelectTitle = current($aTitleHash);
            }
            else // must not happen
            {
                $sNeedSelectTitle = '';
            }
        
            if ($aOptionHash)
            {
                $aFullOptionHash = array('' => $sNeedSelectTitle);
                
                foreach ($aOptionHash as $iKey => $sOption)
                {
                    $aFullOptionHash[$iKey] = $sOption;
                }
                
                $aOptionHash = $aFullOptionHash;
            }
            else 
            {
                $aOptionHash = array('' => $sNeedSelectTitle);
            }
#            $aOptionHash = array_merge_recursive(, $aOptionHash);
        }
        
        switch ($aField['frontend_input'])
        {
            case 'text':
                $aParams['class'] .= ' input-text';
                $sHtml .= $oView->formText($sFieldName, $sFieldValue, $aParams);
            break;    
            
            case 'textarea':
                $aParams['class'] .= ' input-text';
//                $aParams['style'] = 'height:50px; width:100%';
                $aParams['style'] = 'height:50px;';
                $sHtml .= $oView->formTextarea($sFieldName, $sFieldValue, $aParams);
            break;    
            
            case 'select':
                $select = Mage::getModel('core/layout')->createBlock('core/html_select')
                    ->setName($sFieldName)
                    ->setId($sFieldId)
                    ->setTitle($sLabel)
#                    ->setClass('validate-select')
                    ->setClass($sFieldClass)
                    ->setValue($sFieldValue)
                    ->setOptions($aOptionHash);
                
                    $sHtml .= $select->getHtml();
            break;    
            
            case 'multiselect':
                $select = Mage::getModel('core/layout')->createBlock('core/html_select')
                    ->setName($sFieldName . '[]')
                    ->setId($sFieldId)
                    ->setTitle($sLabel)
//                    ->setClass('validate-select')
                    ->setClass($sFieldClass)
                    ->setValue($sFieldValue)
                    ->setExtraParams('multiple')
                    ->setOptions($aOptionHash);
                
                    $sHidden = '<input type="hidden" name="'.$sFieldName.'"  value="" />';
                    
                    $sHtml .= $sHidden . $select->getHtml();
            break;    
            
            case 'checkbox':
                
#            $selectHtml = '<ul id="options-'.$_option->getId().'-list" class="options-list">';
            $selectHtml = '<ul id="options-'.$sFieldId.'-list" class="options-list">';
            $require = ($aField['is_required']) ? ' validate-one-required-by-name' : '';
            $arraySign = '';
                    $type = 'checkbox';
                    $class = 'checkbox';
                    $arraySign = '[]';
                    
            $count = 0;
            
            if ($aOptionHash)
            {
                foreach ($aOptionHash as $iKey => $sValue) 
                {
                    $count++;
                    
                    $sChecked = '';
                    
                    if ($sFieldValue AND in_array($iKey, $sFieldValue))
                    {
                        $sChecked = 'checked';
                    }
                    
                    $selectHtml .= '<li>' .
                                   '<input type="'.$type.'" class="'.$class.' '.$require.' product-custom-option" name="'.$sFieldName.''.$arraySign.'" id="'.$sFieldId.'_'.$count.'" value="'.$iKey.'" '.$sChecked.' />' .
//                                   '<span class="label"><label for="'.$sFieldId.'_'.$count.'"'.(($sPageType=='register')?' style="font-weight:normal;"':"").'>'.$sValue.'</label></span>';
                                   $sValue;
                    $selectHtml .= '</li>';
                }
            }
            $selectHtml .= '</ul>';
                
                $sHidden = '<input type="hidden" name="'.$sFieldName.'"  value="" />';                
            
                $sHtml .= $sHidden . $selectHtml;
            break;    
            
            case 'radio':
                
            $selectHtml = '<ul id="options-'.$sFieldId.'-list" class="options-list">';
            $require = ($aField['is_required']) ? ' validate-one-required-by-name' : '';
            
                    $type = 'radio';
                    $class = 'radio';
                    if (!$aField['is_required']) {
//                        $selectHtml .= '<li><input type="radio" id="'.$sFieldId.'" class="'.$class.' product-custom-option" name="'.$sFieldName.'" value="" checked="checked" /><span class="label"><label for="options_'.$sFieldId.'"'.(($sPageType=='register')?' style="font-weight:normal;"':"").'>' . Mage::helper('catalog')->__('None') . '</label></span></li>';
                        $selectHtml .= '<li><input type="radio" id="'.$sFieldId.'" class="'.$class.' product-custom-option" name="'.$sFieldName.'" value="" checked="checked" />' . Mage::helper('catalog')->__('None') . '</li>';
                    }
                    
            $count = 0;
            
            if ($aOptionHash)
            {
                foreach ($aOptionHash as $iKey => $sValue) 
                {
                    $count++;
                    
                    $sChecked = '';
                    
                    if ($iKey == $sFieldValue)
                    {
                        $sChecked = 'checked';
                    }
                    
                    $selectHtml .= '<li>' .
                                   '<input type="'.$type.'" class="'.$class.' '.$require.' product-custom-option" name="'.$sFieldName.''.'" id="'.$sFieldId.'_'.$count.'" value="'.$iKey.'" '.$sChecked.' />' .
                          //         '<span class="label"><label for="'.$sFieldId.'_'.$count.'"'.(($sPageType=='register')?' style="font-weight:normal;"':"").'>'.$sValue.'</label></span>';
                                   $sValue;
                    $selectHtml .= '</li>';
                }
            }
            $selectHtml .= '</ul>';
                
                $sHidden = '<input type="hidden" name="'.$sFieldName.'"  value="" />';                
            
                $sHtml .= $sHidden . $selectHtml;
            break;    
            
            case 'boolean':
                
                $yesno = array();
                $yesno[] = array
                (
                    'value' => 0,
                    'label' => Mage::helper('catalog')->__('No')
                );
                
                $yesno[] = array
                (
                    'value' => 1,
                    'label' => Mage::helper('catalog')->__('Yes')
                );
                
                $select = Mage::getModel('core/layout')->createBlock('core/html_select')
                    ->setName($sFieldName)
                    ->setId($sFieldId)
                    ->setTitle($sLabel) 
                    ->setClass('validate-select')
                    ->setValue($sFieldValue)
                    ->setOptions($yesno);
                
                    $sHtml .= $select->getHtml();
            break;    
            
            case 'date':
                
                $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $calendar = Mage::getModel('core/layout')
                    ->createBlock('core/html_date')
                    ->setName($sFieldName)
                    ->setId($sFieldId)
                    ->setTitle($sLabel) 
                    ->setClass($sFieldClass)
                    ->setValue($sFieldValue, $dateFormatIso)
#                    ->setValue($sDateValue)
#                    ->setClass('input-text'.$require)
//                    ->setImage(Mage::getDesign()->getSkinUrl('images/calendar.gif'))
                    ->setFormat(Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        
                if (!$bForAdmin)
                {
                    $calendar->setImage(Mage::getDesign()->getSkinUrl('images/calendar.gif'));
                } else {
                    $calendar->setImage(Mage::getDesign()->getSkinBaseUrl() . 'images/grid-cal.gif');
                }
                $sHtml .= $calendar->getHtml();
            break;
            case 'static':
                $sHtml .= $sFieldValue;
            break;
        }

        $aDescHash = $this->getAttributeDescription($iItemId);
        
	    $iStoreId = Mage::app()->getStore()->getId();
        
	    $sHtml .= '</div>';
	    $sHtml .= '</td></tr><tr><td stytle="width:380px;">';
        if ($aDescHash AND isset($aDescHash[$iStoreId]))
        {
            $sHtml .= $aDescHash[$iStoreId] ;
        }

	$sHtml .= '</td></tr></table>';
        
	    $sHtml .= '</div></li>';
        
        return $sHtml;        
    }

    public function getAttributeEnableHtml($aField, $sSetName)
    {
        $iItemId = $aField['attribute_id'];
        $sPrefix = 'aitoc_checkout_';
        
        $sFieldId = $sSetName . ':' . $sPrefix . $iItemId;
            
        if ($aField['frontend_input'] == 'radio' OR $aField['frontend_input'] == 'checkbox')
        {
            $sHtml = ''; 
            
            $aOptionHash = $this->getOptionValues($iItemId);            
            
            $count = 0;
            
            if ($aOptionHash)
            {
                foreach ($aOptionHash as $sVal)
                {
                    $count++;
                    $sHtml .= ' $("' . $sFieldId.'_'.$count . '").disabled = false; ';
                }
            }
        }
        else 
        {
            $sHtml = ' $("' . $sFieldId . '").disabled = false; ';
        }
        
        return $sHtml;        
    }

    public function getCustomValue($aField, $sPageType)
    {
        
        if (!$aField) return false;

        if ($aField['frontend_input'] == 'multiselect' OR $aField['frontend_input'] == 'checkbox')
        {
            $sValue = explode(',', $aField['default_value']);
        }
        else 
        {
            $sValue = $aField['default_value'];            
        }
        
        $customerSession=Mage::getSingleton('customer/session');
        $customerId = $customerSession->getCustomerId(); 
                
        if($customerId)
        {
            $attribute = $this->getCustomerData($customerId, 0, true,$aField['attribute_id']);

            if($attribute)
            {
                $attribute = current($attribute);
                if(in_array($attribute['type'], array('multiselect','checkbox')))
                {
                    $sValue = explode(',',$attribute['rawval']);
                }else{
                    $sValue = $attribute['rawval'];  
                }
            }
        }
        
        if (isset($_SESSION['aitoc_checkout_used'][$sPageType][$aField['attribute_id']]))
        {
            return $_SESSION['aitoc_checkout_used'][$sPageType][$aField['attribute_id']];
        }
        
        return $sValue;
    }
    
    public function getOptionValues($sFieldId, $iStoreId = 0)
    {
        if (!$sFieldId) return false;
        
        $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($sFieldId)
            ->setStoreFilter($iStoreId)
            ->load();
            
        $aOptionHash    = array();
        $aRawOptionHash = array();
        $aSortHash      = array();
        
        foreach ($valuesCollection as $item) 
        {
            $aSortHash[$item->getId()] = $item->getData('sort_order');
            $aRawOptionHash[$item->getId()] = $item->getValue();
        }
        
        if ($aSortHash)
        {
            asort($aSortHash);
            
            foreach ($aSortHash as $iKey => $sVal)
            {
                $aOptionHash[$iKey] = $aRawOptionHash[$iKey];
            }
        }
        
        return $aOptionHash;
    }

    /*compatibility wrap*/
    public function getCheckoutAtrributeList($iStepId, $iTplPlaceId, $sPageType, $allFields=false)
    {
        return $this->getCheckoutAttributeList($iStepId, $iTplPlaceId, $sPageType, $allFields);
    }     
    
    public function getCheckoutAttributeList($iStepId, $iTplPlaceId, $sPageType, $allFields=false)
    {
    	
        $iStoreId = Mage::app()->getStore()->getId();
		$iSiteId  = Mage::app()->getWebsite()->getId();

		if (isset($_SESSION['adminhtml_quote']['store_id']))
		{
			$iStoreId = $_SESSION['adminhtml_quote']['store_id'];
			$iSiteId  =  Mage::getModel('core/store')->load($iStoreId)->getWebsiteId();
		}
		
		/* {#AITOC_COMMENT_END#}
        $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckoutfields')->getLicense()->getPerformer();
        $ruler = $performer->getRuler();
        if (!($ruler->checkRule('store',$iStoreId,'store') || $ruler->checkRule('store',$iSiteId,'website')))
        {
            return false;
        }
        {#AITOC_COMMENT_START#} */
        
        if ($this->_aCheckoutAtrrList === NULL)
        {
            if (!isset($_SESSION['aitoc_checkout_used']))
            {
                $_SESSION['aitoc_checkout_used'] = array();
            }
            
            $sStepField = Mage::helper('aitcheckoutfields')->getStepField($sPageType);
            
		    $sWhereScope = '(additional_table.is_visible_in_advanced_search = 1 OR (find_in_set("' . $iStoreId . '", main_table.note) OR find_in_set("' . $iSiteId . '", additional_table.apply_to)))';
            
            $collection = $this->getAttributeCollecton();
                    
            $collection->getSelect()->where('additional_table.' . $sStepField . ' > 0');                
            if($sStepField != 'attribute_id')
            {                
                $collection->getSelect()->where($sWhereScope);
            } 
            $collection->getSelect()->order('additional_table.position ASC');                
  
            $aAttributeList = $collection->getData();

            $postOrder = Mage::app()->getRequest()->getPost('order');
            if($postOrder && isset($postOrder['account']) && isset($postOrder['account']['group_id']))
            {
                $groupId = $postOrder['account']['group_id'];
            }
            else
            {
                $groupId = Mage::getSingleton('customer/session')->getCustomerGroupId(); 
            }
            

            $this->_aCheckoutAtrrList = array();
       
            if ($aAttributeList)
            {
                foreach ($aAttributeList as $aItem)
                {
                    
                    if(!in_array($groupId, Mage::getModel('aitcheckoutfields/attributecustomergroups')->getGroups($aItem['attribute_id'])))
                    {
                        continue;
                    }
                    if($aItem['ait_product_category_dependant'])
                    {
                        $product_intersect = array_intersect(Mage::getModel('aitcheckoutfields/attributecatalogrefs')->getRefs($aItem['attribute_id'], 'product'), Mage::helper('aitcheckoutfields')->getCartItems());
                        $category_intersect = array_intersect(Mage::getModel('aitcheckoutfields/attributecatalogrefs')->getRefs($aItem['attribute_id'], 'category'), Mage::helper('aitcheckoutfields')->getCartCategories());
                        if(empty($product_intersect) && empty($category_intersect))
                        {
                            continue;
                        }
                
                     }
                    $this->_aCheckoutAtrrList[$aItem[$sStepField]][$aItem['is_filterable']][$aItem['attribute_id']] = $aItem;
                }
            }
        }
        
        if($this->_aCheckoutAtrrList && $allFields)
        {
            return $this->_aCheckoutAtrrList;
        }
        elseif (isset($this->_aCheckoutAtrrList[$iStepId][$iTplPlaceId]))
        {
            return $this->_aCheckoutAtrrList[$iStepId][$iTplPlaceId];
        }
        else 
        {
            return false;
        }
    }
    
    private function _getCustomerUsergroupById($customerId)
    {
        $oCustomer = Mage::getModel('customer/customer')->load($customerId);
        return $oCustomer->getGroupId();
    }
    /*to refactor*/
    public function getCustomerAttributeList($tplPlace=false, $customerDefinedId=false)
    {
        $iStoreId = Mage::app()->getStore()->getId();
		$iSiteId  = Mage::app()->getWebsite()->getId();

         if($customerDefinedId)
         {
             $iGroupId = $this->_getCustomerUsergroupById($customerDefinedId);
         }
         elseif($customerId = Mage::getSingleton('customer/session')->getCustomerId())
         {
             $iGroupId = $this->_getCustomerUsergroupById($customerId);
         }
         elseif($customerId = Mage::app()->getRequest()->getParam('id'))
         {
             $iGroupId = $this->_getCustomerUsergroupById($customerId);
         }
         else
         {
             $iGroupId = 0;
         }
		/* {#AITOC_COMMENT_END#}
		if($tplPlace && !Mage::app()->getStore()->isAdmin())
		{
        	$performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckoutfields')->getLicense()->getPerformer();
        	$ruler = $performer->getRuler();
        	if (!($ruler->checkRule('store',$iStoreId,'store') || $ruler->checkRule('store',$iSiteId,'website')))
        	{
            	return false;
        	}
        }
        {#AITOC_COMMENT_START#} */
		
		if($this->_aCustomerAtrrList===NULL)
		{
		    $sWhereScope = '(additional_table.is_visible_in_advanced_search = 1 OR (find_in_set("' . $iStoreId . '", main_table.note) OR find_in_set("' . $iSiteId . '", additional_table.apply_to)))';
		    
    		$collection = $this->getAttributeCollecton();
    		$collection->getSelect()->where('additional_table.ait_registration_page > 0');
    		if(!Mage::app()->getStore()->isAdmin())
    		{
                $collection->getSelect()->where($sWhereScope);
    		}
            $collection->getSelect()->order('additional_table.ait_registration_position ASC');                
      
            $aAttributeList = $collection->getData();
            
            $this->_aCustomerAtrrList = array();
            
            if ($aAttributeList)
            {
                foreach ($aAttributeList as $aItem)
                {
                    if(isset($iGroupId) && !in_array($iGroupId, Mage::getModel('aitcheckoutfields/attributecustomergroups')->getGroups($aItem['attribute_id'])))
                    {
                        continue;
                    }
                    
                    $this->_aCustomerAtrrList[$aItem['ait_registration_place']][$aItem['attribute_id']] = $aItem;
                }
            }
		}
        if($this->_aCustomerAtrrList && !$tplPlace)
        {
            return $this->_aCustomerAtrrList;
        }
        elseif (isset($this->_aCustomerAtrrList[$tplPlace]))
        {
            return $this->_aCustomerAtrrList[$tplPlace];
        }
        else 
        {
            return false;
        }
    }
    
    public function checkStepHasRequired($iStepId, $sPageType)
    {
        switch ($sPageType)
        {
            case 'onepage':
                $sStepField = 'is_searchable'; // hook for input source (one page)
            break;    
            
            case 'multishipping':
                $sStepField = 'is_comparable'; // hook for input source (multi shipping)
            break;    
        }
        
        $collection = $this->getAttributeCollecton();
                
        $collection->getSelect()->where('additional_table.' . $sStepField . ' = ' . $iStepId);                
        $collection->getSelect()->where('main_table.is_required = 1');                
        
        $aAttributeList = $collection->getData();
        
        if ($aAttributeList)
        {
        
            foreach ($aAttributeList as $aItem)
            {
                
                if($aItem['ait_product_category_dependant'])
                {
                    $product_intersect = array_intersect(Mage::getModel('aitcheckoutfields/attributecatalogrefs')->getRefs($aItem['attribute_id'], 'product'), Mage::helper('aitcheckoutfields')->getCartItems());
                    $category_intersect = array_intersect(Mage::getModel('aitcheckoutfields/attributecatalogrefs')->getRefs($aItem['attribute_id'], 'category'), Mage::helper('aitcheckoutfields')->getCartCategories());
                                            
                    if(empty($product_intersect) && empty($category_intersect))
                    {
                        continue;
                    }
            
                }
                return true;
            }        
        
            
        }
        return false;
        
    }    
    
    public function setCustomValue($sFieldName, $sFieldValue, $sPageType)
    {
        if (!$sFieldName OR !$sPageType) return false;

        if (strpos($sFieldName, 'itoc_checkout_'))
        {
            $aNameParts = explode('_', $sFieldName);
            
            $sFieldId = $aNameParts[2];
            
            $_SESSION['aitoc_checkout_used'][$sPageType][$sFieldId] = $sFieldValue;
        }
        
        return true;
    }       
    
    protected function _updateAttributeValue($sFieldId , $sValue = null)
    {
        if(empty($sFieldId)) return null;
        $attribute = $this->load($sFieldId);
        $type = empty($attribute)? null : $attribute->getData('frontend_input');
        if($sValue === '' && $type == 'radio')
        {
            $sValue = 'None';    
        } 
        return $sValue;   
    }
    
    /**
     * Retrieves field data from session by field id and saves it to DB
     * 
     * @param string  $table        Attribute table
     * @param integer $entityId     Order or customer ID
     * @param string  $sessionType  onepage/multishipping/register
     * @param integer $fieldId      Field ID
     * @param boolean $skipEmpty    Skip or not empty fields
     */
    protected function _saveData($table, $entityId, $sessionType, $fieldId, $skipEmpty=false)
    {   if(isset($_SESSION['aitoc_checkout_used'][$sessionType])
           && $_SESSION['aitoc_checkout_used'][$sessionType]
           && isset($_SESSION['aitoc_checkout_used'][$sessionType][$fieldId]))
        {
            $fieldValue = $_SESSION['aitoc_checkout_used'][$sessionType][$fieldId];
            
            if(!$skipEmpty || ($fieldValue !== ''))
            {
                $this->_saveDataToDb($table, $entityId, $fieldId, $fieldValue);
            }
        }
    }
    
	public function copyRecProfileFieldsToOrderFields($recProfileId, $orderId)
	{
	    $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
		$query = 'SELECT * FROM ' . $resource->getTableName($this->_sCustomRecProfileAttrTable) . ' WHERE entity_id=' . $recProfileId;
		$results = $readConnection->fetchAll($query);
		if(count($results) > 0)
		{
		    foreach($results as $recProfileData)
			{
			    $this->_saveDataToDb($this->_sCustomAttrTable, $orderId, $recProfileData['attribute_id'], $recProfileData['value']);
			}
		}
	}
    
    protected function _saveDataToDb($table, $entityId, $fieldId, $fieldValue)
    {
                $Db = Mage::getSingleton('core/resource')->getConnection('core_write');
                if (is_array($fieldValue))
                {
                    $fieldValue = implode(',', $fieldValue);                    
                }
                
                $DBInfo = array
                (
                    'entity_id'     => $entityId,
                    'attribute_id'  => $fieldId,
            'value'         => Mage::helper('core')->escapeHtml($fieldValue),
                );

                $Db->insert($table, $DBInfo);
            }
    
    public function saveCustomOrderData($iOrderId, $sPageType)
    {
        if (!$iOrderId OR !$sPageType) return false;

        if($steps = $this->getCheckoutAttributeList(1, 1, $sPageType, true))
        {
            foreach($steps as $stepPlaceholders)
            {
                foreach($stepPlaceholders as $placeholderFields)
                {
                    foreach(array_keys($placeholderFields) as $fieldId)
                    {
                        $this->_saveData($this->_sCustomAttrTable, $iOrderId, $sPageType, $fieldId, false);
                    }
                }
            }
        }
    }
	
	public function saveCustomRecurrentProfileData($iRecurringProfileIds, $sPageType)
    {
        if (!$iRecurringProfileIds OR !$sPageType) return false;

        if($steps = $this->getCheckoutAttributeList(1, 1, $sPageType, true))
        {
            foreach($steps as $stepPlaceholders)
            {
                foreach($stepPlaceholders as $placeholderFields)
                {
                    foreach(array_keys($placeholderFields) as $fieldId)
                    {
					    foreach($iRecurringProfileIds as $recProfileId)
						{
                            $this->_saveData($this->_sCustomRecProfileAttrTable, $recProfileId, $sPageType, $fieldId, false);
						}
                    }
                }
            }
        }
    }
    
    public function saveCustomerData($customerId, $mixed=false)
    {
        if(!$customerId) return false;
        
        if($placeholders = $this->getCustomerAttributeList(false,$customerId))
        {
            foreach($placeholders as $placeholderFields)
            {
                foreach($placeholderFields as $fieldId => $fieldData)
                {
                    $sessionType = ($mixed)?'onepage':'register';
                    $this->_saveData($this->_sCustomerAttrTable, $customerId, $sessionType, $fieldId, false);
                }
            }
        }
    }
    
    public function clearCheckoutSession($sPageType)
    {
        unset($_SESSION['aitoc_checkout_used'][$sPageType]);
    }
    
    protected function _getItemsByOrderId($iOrderId)
    {
        if (!$iOrderId || empty($iOrderId)) return false;  
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $select = $oDb->select()       
            ->from(array('c' => $this->_sCustomAttrTable), '*')
            ->where('c.entity_id=?', $iOrderId)
            ->order('value_id ASC');                
        
        return $oDb->fetchAll($select);        
    }
	
	protected function _getItemsByRecurrentProfileId($iRecProfileId)
    {
        if (!$iRecProfileId || empty($iRecProfileId)) return false;  
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $select = $oDb->select()       
            ->from(array('c' => $this->_sCustomRecProfileAttrTable), '*')
            ->where('c.entity_id=?', $iRecProfileId)
            ->order('value_id ASC');                
        
        return $oDb->fetchAll($select);        
    }
    
    public function getOrderCustomData($iOrderId, $iStoreId, $bForAdmin, $forView = false)
    {
        $aCustomAtrrList = array();
        
        $aItemList = $this->_getItemsByOrderId($iOrderId);
        if(!$aItemList)  return false;
        
        return $this->_formatCustomData($aItemList, $iStoreId, $bForAdmin);
    }
	
	public function getRecurringProfileCustomData($iRecProfileId, $iStoreId, $bForAdmin, $forView = false)
    {
        $aCustomAtrrList = array();
        
        $aItemList = $this->_getItemsByRecurrentProfileId($iRecProfileId);
        if(!$aItemList)  return $aCustomAtrrList;
        
        return $this->_formatCustomData($aItemList, $iStoreId, $bForAdmin);
    }
    
    public function getEmailOrderCustomData($iOrderId, $iStoreId)
    {
        if (!$iOrderId) return false;
        

        $aItemList = $this->_getItemsByOrderId($iOrderId);
        
        $result = $this->_formatCustomData($aItemList, $iStoreId, true,true);
       
        return $result;
    }

    public function getCustomerData($customerId, $iStoreId=0, $bForAdmin, $attributeId=null, $bForView=false)
    {
        if (!$customerId) return false;
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $oDb->select()
            ->from(array('c' => $this->_sCustomerAttrTable), '*')
            ->where('c.entity_id=?', $customerId)
            ->order('value_id ASC');                
        ;
        if($attributeId)
        {
            $select->where('c.attribute_id=?', $attributeId);
        }
        
        $aItemList = $oDb->fetchAll($select);
        
        
        return $this->_formatCustomData($aItemList, $iStoreId, $bForAdmin, true, $bForView);
    }
    /*to refactor*/
    protected function _formatCustomData($aItemList, $iStoreId, $bForAdmin, $bForCustomer=false, $bForView=false)
    {  

        $aCustomAtrrList = array();
        if ($aItemList)
        {
            $aAttrIdHash = array();
            
            foreach ($aItemList as $aItem)
            {
                $aAttrIdHash[] = $aItem['attribute_id'];
            }
            
            $collection = $this->getAttributeCollecton();
                    
            $collection->getSelect()->where('main_table.attribute_id IN (' . implode(',', $aAttrIdHash) . ')');                
            
            
            if ($aAttrList = $collection->getData())
            {
                foreach ($aAttrList as $aData)
                {
                    $aAttrDataHash[$aData['attribute_id']] = $aData;                
                }
            }
            
            
            foreach ($aItemList as $aItem)
            {
            	 $aStores = explode(',', $aAttrDataHash[$aItem['attribute_id']]['note']);
        	
            	if(in_array($iStoreId,$aStores) || $aAttrDataHash[$aItem['attribute_id']]['note'] == '' || $iStoreId==0 || $aAttrDataHash[$aItem['attribute_id']]['is_visible_in_advanced_search'])
            	{
                $aAttrData = $aAttrDataHash[$aItem['attribute_id']];
                
                if ($aAttrData)
                {
                    if($bForCustomer && !$bForView)
                    {
                        $bShowAttribute = true;
                    }
                    elseif ($bForAdmin)
                    {
                        $bShowAttribute = $aAttrData['is_used_for_price_rules']; // fix for admin
                    }
                    else 
                    {
                        $bShowAttribute = $aAttrData['is_filterable_in_search']; // fix for member
                    }
                }
                else 
                {
                    $bShowAttribute = false;
                }
                
                if ($bShowAttribute)
                {
                    $sValue = '';
                    switch ($aAttrData['frontend_input'])
                    {
                        case 'text':
                        case 'date': // to check?
                        case 'textarea':
                            $sValue = $aItem['value'];
                        break;
                            
                        case 'boolean':
                            
                            if ($aItem['value'] == 1)
                            {
                                $sValue = Mage::helper('catalog')->__('Yes');
                            }
                            elseif ($aItem['value']) 
                            {
                                $sValue = '';
                            }
                            else 
                            {
                                $sValue = Mage::helper('catalog')->__('No');
                            }
                            
                        break;
                            
                        case 'select':
                        case 'radio':
                            
                            $aValueList = $this->getAttributeOptionValues($aItem['attribute_id'], $bForAdmin?0:$iStoreId, $aItem['value']);
                            if ($aValueList)
                            {
                                $sValue = $aValueList[0];
                            }
                        break;    
                        
                        case 'multiselect':
                        case 'checkbox':
                            $aValueList = $this->getAttributeOptionValues($aItem['attribute_id'], $bForAdmin?0:$iStoreId, explode(',', $aItem['value']));
                            if ($aValueList)
                            {
                                $sValue = implode(', ', $aValueList);
                            }
                        break;    
                    }
                    
                    $aCustomData = array
                    (
                        'label' => $this->getAttributeLabel($aItem['attribute_id'], $bForAdmin?0:$iStoreId),
                        'value' => $sValue,
                        'code'  => $aAttrData['attribute_code'],
                        'type'  => $aAttrData['frontend_input'],
                        'rawval'=> $aItem['value'],
                    	'id'	=> $aItem['attribute_id'],
                    );
                    $aCustomAtrrList[$aCustomData['id']] = $aCustomData;
                }
            	}
            }
        }        
        return $aCustomAtrrList;
    }
    
      public function getInvoiceCustomData($iOrderId, $iStoreId = null , $unsetNullValue = false)
    {
        $aCustomAtrrList = array();
        
        $aItemList = $this->_getItemsByOrderId($iOrderId);
        if(!$aItemList)  return false;                  
        
        $aAttrIdHash = array();
        foreach ($aItemList as $aItem)
        {
            $aAttrIdHash[] = $aItem['attribute_id'];
        }
        $aAttrDataHash = $this->_getAttributesDataHash($aAttrIdHash);

        foreach($aItemList as $data)
        {
            $attrData = $aAttrDataHash[$data['attribute_id']];
            if($this->_checkIsUseForInvoice($attrData))
            {
                 $aCustomData = array
                (
                    'label' => $this->getAttributeLabel($data['attribute_id'], $iStoreId),
                    'value' => $this->_getAttributeValueByType($data['attribute_id'],$data['value'], $attrData['frontend_input']),
                    'code'  => $attrData['attribute_code'],
                    'type'  => $attrData['frontend_input'],
                    'rawval'=> $data['value'],
                    'id'    => $data['attribute_id'],
                ); 
                if(empty($aCustomData['value']) && $unsetNullValue)
                {
                    continue;      
                }            
                else
                {
                    $aCustomAtrrList[$aCustomData['id']] =  $aCustomData;
                }     
            }
        }
        return  $aCustomAtrrList;     
    }
    /*to refactor*/
    protected function _getAttributeValueByType($sFieldId , $sValue, $sType = 'text' , $bForAdmin = true, $iStoreId = 0)
    {
        switch ($sType)
        {
            case 'text':
            case 'date': // to check?
            case 'textarea':
                $sValue = $sValue;
            break;
                
            case 'boolean':
                
                if ($sValue == 1)
                {
                    $sValue = Mage::helper('catalog')->__('Yes');
                }
                elseif ($sValue) 
                {
                    $sValue = '';
                }
            break;    
            
            case 'multiselect':
                if(version_compare(Mage::getVersion(), '1.6.0.0', '>='))
                {
                    if(is_array($sValue))
                    {
                        $tempArray = array();
                        foreach ($sValue as $val)
                        {
                            $explodedArr = explode(',', $val);

                            foreach($explodedArr as $expVal)
                            {
                                array_push($tempArray, $expVal);
                            }
                        }
                    $aValueList = $this->getAttributeOptionValues($sFieldId, $bForAdmin?0:$iStoreId, $tempArray);
                    }
                    else
                    {
                        $aValueList = $this->getAttributeOptionValues($sFieldId, $bForAdmin?0:$iStoreId, explode(',', $sValue));
                    }
                }
                else
                    $aValueList = $this->getAttributeOptionValues($sFieldId, $bForAdmin?0:$iStoreId, $sValue);
                if ($aValueList)
                {
                    $sValue = implode(', ', $aValueList);
                }
            break;

            case 'checkbox':
                    $aValueList = $this->getAttributeOptionValues($sFieldId, $iStoreId, $sValue);
                if ($aValueList)
                {
                    $sValue = implode(', ', $aValueList);
                }
            break;
            
            case 'select':
            case 'radio':
                
                $aValueList = $this->getAttributeOptionValues($sFieldId, $bForAdmin?0:$iStoreId, $sValue);
                if ($aValueList)
                {
                    $sValue = $aValueList[0];
                }
            break;    
            
            case 'multiselect':
            case 'checkbox':
                $aValueList = $this->getAttributeOptionValues($sFieldId, $bForAdmin?0:$iStoreId, explode(',', $sValue));
                if ($aValueList)
                {
                    $sValue = implode(', ', $aValueList);
                }
            break;    
        } 
        return  $sValue;      
    }
    
    protected function _getAttributesDataHash(array $aAttrIdHash)
    {
        if(empty($aAttrIdHash)) 
        {
            return array();
        }
        $collection = $this->getAttributeCollecton();
                
        $collection->getSelect()->where('main_table.attribute_id IN (' . implode(',', $aAttrIdHash) . ')');                
        if ($aAttrList = $collection->getData())
        {
            foreach ($aAttrList as $aData)
            {
                $aAttrDataHash[$aData['attribute_id']] = $aData;                
            }
        } 
        return $aAttrDataHash;         
    }
    
    protected function _checkIsUseForInvoice(array $attr)
    {
        return $attr['is_display_in_invoice'] ? $attr['is_display_in_invoice'] : false;
    }
    /*to refactor*/
    public function getSessionCustomData($sPageType, $iStoreId, $bForAdmin)
    {
        if (!$sPageType) return false;
        
        $aCustomAtrrList = array();
        
        if (isset($_SESSION['aitoc_checkout_used'][$sPageType]) AND $_SESSION['aitoc_checkout_used'][$sPageType])
        {
            $oAttribute = Mage::getModel('eav/entity_attribute');
            
            $allowedFields = array();
            if($steps = $this->getCheckoutAttributeList(1, 1, $sPageType, true))
            {
                foreach($steps as $stepPlaceholders)
                {
                    foreach($stepPlaceholders as $placeholderFields)
                    {
                        foreach(array_keys($placeholderFields) as $fieldId)
                        {
                            $allowedFields[]=$fieldId;
                        }
                    }
                }
            }
            
            foreach ($_SESSION['aitoc_checkout_used'][$sPageType] as $sFieldId => $sValue)
            {
                if(!in_array($sFieldId,$allowedFields))
                {
                    continue;
                }

                $oAttribute->load($sFieldId);
                $aAttrData = $oAttribute->getData();
                
                if ($aAttrData)
                {
                    $bShowAttribute = true;
                }
                else 
                {
                    $bShowAttribute = false;
                }
                
                if ($bShowAttribute)
                {
                    switch ($aAttrData['frontend_input'])
                    {
                        case 'text':
                        case 'date': // to check?
                        case 'textarea':
                            $sValue = $sValue;
                        break;
                            
                        case 'boolean':
                            
                            if ($sValue == 1)
                            {
                                $sValue = Mage::helper('catalog')->__('Yes');
                            }
                            elseif ($sValue) 
                            {
                                $sValue = '';
                            }
                            else 
                            {
                                $sValue = Mage::helper('catalog')->__('No');
                            }
                            
                        break;
                            
                        case 'select':
                        case 'radio':
                            
                            $aValueList = $this->getAttributeOptionValues($sFieldId, $iStoreId, $sValue);
                            if ($aValueList)
                            {
                                $sValue = $aValueList[0];
                            }
                        break;    
                        
                        case 'multiselect':
                        case 'checkbox':
                            $aValueList = $this->getAttributeOptionValues($sFieldId, $iStoreId, $sValue);
                            if ($aValueList)
                            {
                                $sValue = implode(', ', $aValueList);
                            }
                        break;    
                    }
                    
                    $aCustomData = array
                    (
                        'label' => $this->getAttributeLabel($sFieldId, $iStoreId),
                        'value' => $sValue,
                    );
                            
                    $aCustomAtrrList[] = $aCustomData;
                }
            }
        }
        
        return $aCustomAtrrList;
    }  
    
    public function saveAttributeDescription($iAttributeId, $aDescriptionData)
    {
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $oDb->delete($this->_sDescAttrTable, 'attribute_id = ' . $iAttributeId);
        
        if ($aDescriptionData)
        {
            foreach ($aDescriptionData as $iStoreId => $sValue)
            {
                $aDBInfo = array
                (
                    'attribute_id'  => $iAttributeId,
                    'store_id'     => $iStoreId,
                    'value'         => $sValue,
                );
        
                $oDb->insert($this->_sDescAttrTable, $aDBInfo);
            }
        }
        
        return true;
    }
    
    public function getAttributeDescription($iAttributeId)
    {

        if (!$iAttributeId) return false;
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $oDb->select()
                      ->from(array('c' => $this->_sDescAttrTable), array('store_id', 'value'))
                      ->where('c.attribute_id=?', $iAttributeId);
        
        $aItemList = $oDb->fetchPairs($select);
        
        return $aItemList;
    }
    
    
    public function delete()
    {
        parent::delete();
        $id = $this->getId();
        Mage::getModel('aitcheckoutfields/attributecustomergroups')->deleteGroups($id);
        Mage::getModel('aitcheckoutfields/attributecatalogrefs')->deleteRefs($id,'category')->deleteRefs($id,'product');
    }
   
    
    // new functions
    
    public function saveAttributeNeedSelect($iAttributeId, $aNeedSelectData)
    {
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        $oDb->delete($this->_sNeedSelectTable, 'attribute_id = ' . $iAttributeId);
        
        if ($aNeedSelectData)
        {
            foreach ($aNeedSelectData as $iStoreId => $sValue)
            {
                $aDBInfo = array
                (
                    'attribute_id'  => $iAttributeId,
                    'store_id'      => $iStoreId,
                    'value'         => $sValue,
                );
        
                $oDb->insert($this->_sNeedSelectTable, $aDBInfo);
            }
        }
        
        return true;
    }
    
    public function getAttributeNeedSelect($iAttributeId)
    {
        if (!$iAttributeId) return false;
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $oDb->select()
            ->from(array('c' => $this->_sNeedSelectTable), array('store_id', 'value'))
#            ->joinInner(array('p' => $this->getTable('catalog/product')), 'o.product_id=p.entity_id', array())
            ->where('c.attribute_id=?', $iAttributeId)
            ->order('c.store_id ASC');
        
        $aItemList = $oDb->fetchPairs($select);
        
        return $aItemList;
    }
    /*to refactor*/
    public function getOrderCustomEditList($iOrderId, $iStoreId)
    {
        if (!$iOrderId) return false;

        $aItemList = $this->_getItemsByOrderId($iOrderId);        
        
        $aAttrIdHash            = array();
        $aValueIdHash           = array();
        $aOrderCustomEditList   = array();
        
        if ($aItemList)
        {
            foreach ($aItemList as $aItem)
            {
                $aAttrIdHash[$aItem['attribute_id']]    = $aItem['value'];
                $aValueIdHash[$aItem['attribute_id']]   = $aItem['value_id'];
            }
        }      
        $collection = $this->getAttributeCollecton();

             
        $aAttributeList = $collection->getData();
        
        $order = Mage::getModel('sales/order');
        $order->load($iOrderId);
        $groupId = $order->getCustomerGroupId();
        
        if ($aAttributeList)
        {
            foreach ($aAttributeList as $aItem)
            {
                
                if(!$aItem['is_used_for_price_rules'])
                {
                    continue;
                }
                
                $sValue='';
                if (isset($aAttrIdHash[$aItem['attribute_id']]))
                {
                    if ($aItem['frontend_input'] == 'multiselect' OR $aItem['frontend_input'] == 'checkbox')
                    {
                        $sValue = explode(',', $aAttrIdHash[$aItem['attribute_id']]);
                    }
                    else 
                    {
                        $sValue = $aAttrIdHash[$aItem['attribute_id']];
                    }
                }
                
                if (is_array($sValue))
                {
                	foreach ($sValue as $key=>$val)
                	{
                		$sValue[$key] = htmlspecialchars_decode($val);
                	}
                }
                else
                {
                	$sValue = htmlspecialchars_decode($sValue);
                }
                
                $aStores = explode(',', $aItem['note']);
        	
            	if(in_array($iStoreId,$aStores) || $aItem['note'] == '' || $aItem['is_visible_in_advanced_search'])
            	{
            		if($aItem['frontend_input'] != 'static')
            		{
            			$this->setCustomValue('aitoc_checkout_' . $aItem['attribute_id'], $sValue, 'abstract');
                		$aOrderCustomEditList[] = $this->getAttributeHtml($aItem, 'order', 'abstract', $iStoreId, true);
            		}
            	}
                

            }
        }        
        
        return $aOrderCustomEditList;
    } 
    
    public function saveEditedCustomOrderData($aData, $iOrderId)
    {
        if (!$iOrderId OR !$aData) return false;
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        $oDb->delete($this->_sCustomAttrTable, "entity_id = {$iOrderId}");
        
        if ($aData)
        {
            foreach ($aData as $sFieldName => $sValue)
            {
                
                if (strpos($sFieldName, 'itoc_checkout_'))
                {
                    $aNameParts = explode('_', $sFieldName);
                    
                    $sFieldId = $aNameParts[2];
                }
                else 
                {
                    return false;
                }
                
                if (is_array($sValue))
                {
                    $sValue = implode(',', $sValue);                    
                }
                $sValue = $this->_updateAttributeValue($sFieldId,$sValue); 
                if($sValue !== '')
                {
                    $aDBInfo = array
                    (
                        'attribute_id'	=> $sFieldId,
                        'entity_id'		=> $iOrderId,
                        'value'         => $sValue,
                    );
                	
                    $oDb->insert($this->_sCustomAttrTable, $aDBInfo);
                }
            }
        }
        return true;
    }
    
    public function getAttributeCollecton(){
        $oResource = Mage::getResourceModel('eav/entity_attribute');
        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter( Mage::getModel('eav/entity')
            ->setType($this->_sEntityTypeCode)->getTypeId() );
        $collection->getSelect()->join(
            array('additional_table' => $oResource->getTable('catalog/eav_attribute')),
            'additional_table.attribute_id=main_table.attribute_id'
        );
        return $collection;
    }
    
    public function copyOrderCustomData($iOldOrderId, $iNewOrderId)
    {
        if (!$iOldOrderId OR !$iNewOrderId) return false;
        
        $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
        
	        // insert inventory items
	        
	    $sSql = "INSERT INTO
	                `" . $this->_sCustomAttrTable . "`
                (SELECT
                    null as 'value_id',
                    `p`.`attribute_id`,
                    " . $iNewOrderId ." as 'entity_id',
                    `p`.`value`
                FROM `" . $this->_sCustomAttrTable . "` AS `p`
                WHERE (p.entity_id  = " . $iOldOrderId ."))";
    
	    $oDb->query($sSql);
        
        return true;
    }
    /**
	* get module inner setting
	*
	* @param string $setting
	* @return string setting value
	*/
    public function getSetting($setting){
        return $this->{'_s'.$setting};
    }
    public function getYesNo()
    {
        $yesno[] = array
        (
            'value' => 0,
            'label' => Mage::helper('eav')->__('No')
        );
        $yesno[] = array
        (
            'value' => 1,
            'label' => Mage::helper('eav')->__('Yes')
        );
        return $yesno;
    }
    
    /**
     * Form generator for admin area
     * @param object $fieldset - Varien_Data_Form_Element_Fieldset object
     * @param object $collection - array of Mage_Eav_Model_Mysql4_Entity_Attribute
     * @param array $attributeValues - array of values as [attribute_code]-> [mixed:value]
     */
    
    public function prepareAdminForm($fieldset, $collection, $suffix, $attributeValues='', $bById=false)
    {
        foreach ($collection as $attribute)
    	{
        	if ($inputType = $attribute->getFrontend()->getInputType())
        	{
                $yesNo = false;
                if($inputType!=='static')
                {
                    $class='';
                    $exclReq = false;
                    if ($inputType==='boolean')
                    {
                        $inputType="select";
                        $yesNo = $this->getYesNo();
                    }
                    
                    if ($inputType==='radio'){
                        $inputType='radios';
                        $exclReq = true;
                    }
                    
                    if ($inputType==='checkbox'){
                        $inputType='checkboxes';
                        $exclReq = true;
                    }
                    $elementName = $bById?('aitoc_checkout_'.$attribute->getId()):$attribute->getAttributeCode();
                    
                    if (in_array($inputType, array('select','multiselect','radios','checkboxes')) && !$yesNo)
                    {
                        $options = $attribute->getSource()->getAllOptions(false);
                    }
                    
                    if ($inputType!=='checkboxes'){
	                    $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
	                        array(
	                            'name'      => $suffix.'['.$elementName.']'.(($inputType==='checkboxes')?'[]':''),
	                            'label'     => $attribute->getFrontend()->getLabel(),
	                            'required'  => $exclReq?0:$attribute->getIsRequired(),
	                            'value'		=> isset($attributeValues[$attribute->getAttributeCode()]) ? (is_array($attributeValues[$attribute->getAttributeCode()]) ? $attributeValues[$attribute->getAttributeCode()] : htmlspecialchars_decode($attributeValues[$attribute->getAttributeCode()])) : $attribute->getDefaultValue(),
	                         	'note'      => current($this -> getAttributeDescription($attribute->getId())),
	                        )
	                    );
                    }
                    
                    // fix for internal magento bug with required radios
                    if ($inputType==='radios'){
                    	$element->setData('separator', '</td></tr><tr><td class="label"></td><td class="value">');
                    	if($attribute->getIsRequired())
                    	{
                    	    $element->setData('label',$element->getData('label').'__*__');
                    	    $element->addClass('validate-one-required-by-name');
                    	}
                    	$element->addClass('product-custom-option');
                    }
                    
                    // fix for internal magento bug with required checkboxes
                    if ($inputType==='checkboxes')
                    {
                        $i = 0;
                        foreach ($options as $option)
                        {
                        	if (!isset($attributeValues[$attribute->getAttributeCode()]))
                        	{
                        		$defaultValues = explode(',', $attribute->getDefaultValue());
                        		$attributeValues[$attribute->getAttributeCode()] = $defaultValues;
                        	}
                        	
                            $label = $attribute->getFrontend()->getLabel() . ( $attribute->getIsRequired() ? '__*__' : '' );
                            $fieldset->addField($attribute->getAttributeCode().'_'.$option['value'], 'checkbox', array(
                                'label'     => $i == 0 ? $label : '',
                                'name'      => $suffix.'['.$elementName.'][]',
                                'checked'   => in_array($option['value'], $attributeValues[$attribute->getAttributeCode()]) ? 'checked' : '',
                                'value'     => $option['value'],
                                'disabled'  => false,
                                'class'     => $attribute->getIsRequired() ? 'validate-one-required-by-name' : '',
                                'after_element_html' => '<label for="'.$attribute->getAttributeCode().'_'.$option['value'].'">'.$option['label'].'</label>'
                            ));
                            $i++;
                        }
                    }
                    
                    if ($inputType==='date')
                    {
                        $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                        $element->setImage(Mage::getDesign()->getSkinBaseUrl() . 'images/grid-cal.gif');
                        $element->setValue(
                            isset($attributeValues[$attribute->getAttributeCode()])?$attributeValues[$attribute->getAttributeCode()]:$attribute->getDefaultValue(),
                            Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
                        );
                    }
                    
                    if (in_array($inputType, array('select','multiselect','radios','checkboxes')))
                    {
                        if(is_array($yesNo))
                        {
                            $element->setValues($yesNo);
                        }
                        else
                        {
                            if(($inputType==='radios')&&!$attribute->getIsRequired())
                            {
                                array_unshift($options, array('value'=>'','label'=>Mage::helper('catalog')->__('None')));
                            }
                            if(($inputType==='select'))
                            {
                                array_unshift($options, array('value'=>'','label'=>Mage::helper('catalog')->__('Please select')));
                            }
                            if ($inputType!=='checkboxes')
                            {
                                $element->setValues($options);
                            }
                        }
                    }
                }
            }
    	}
    }

    public function  multiSelectFilter($collection,$column)
    {
        $cond = $column->getFilter()->getCondition();
        $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
        if($field && isset($cond)) {
            // For find_in_set work with AND but not OR
            $fieldAndCond = array();
            if(is_array($cond))
            {
                foreach ($cond as $k=>$v)
                {
                     $collection->addFieldToFilter($field , $v);
                }
            }
        }
    }
}