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
class Aitoc_Aitcheckoutfields_IndexController extends Mage_Adminhtml_Controller_Action
{

    protected $_checkoutTypeId;
	
    protected $_type;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_checkoutTypeId = Mage::getModel('eav/entity')->setType('aitoc_checkout')->getTypeId();
        $this->_type = 'checkout';
    }
    
    protected function _initAction($ids=null) {
        $this->loadLayout($ids);
        return $this;
    }
	
    public function indexAction()
    {
        $this->_initAction()
                ->_setActiveMenu('system/aitoc')
                ->_addContent($this->getLayout()->createBlock('aitcheckoutfields/Grid'));
        $this->renderLayout();
    }
        
    public function _initItem()
    {
        $id     = (int)$this->getRequest()->getParam('attribute_id');

        $model = Mage::getModel('catalog/resource_eav_attribute');

        $oResource = Mage::getResourceModel('eav/entity_attribute');

        $collection = Mage::getResourceModel('eav/entity_attribute_collection');
        $collection->getSelect()->join(
            array('additional_table' => $oResource->getTable('catalog/eav_attribute')),
            'additional_table.attribute_id=main_table.attribute_id'
        );

        $collection->getSelect()->where('main_table.attribute_id = ' . $id);                

        $aAttributeList = $collection->getData();

        if ($aAttributeList and !empty($aAttributeList[0]))
        {
            $model->load($id);
            $model->addData($aAttributeList[0]);
        }

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                    $model->setData($data);
            }
            Mage::register('aitcheckoutfields_data', $model);
            Mage::register('current_product', $model);
            return true;
        }
        else
        {
            return false;
        }
    }
        
    public function categoriesAction()
    {
        $this->_initItem();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_categories')->toHtml()
        );
    }    
        
     public function categoriesJsonAction()
    {
         $this->_initItem();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }        

    
    public function relatedAction()
    {
        $this->_initItem();
        
        $grid = $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_related')
            ->setProductsRelated(Mage::getModel('aitcheckoutfields/attributecatalogrefs')->getRefs(Mage::app()->getRequest()->getParam('attribute_id'),'product'));
        $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
        $serializer ->initSerializerBlock($grid, 'getSelectedRelatedProducts', 'in_products', 'products_related');
        $this->getResponse()->setBody(
            $grid->toHtml().$serializer->toHtml()
            );        

    }    

    public function relatedGridAction()
    {
        $this->_initItem();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_related')
            ->setProductsRelated($this->getRequest()->getPost('products_related', null))->toHtml());

    }    
    
    public function editAction() 
    {
        
        if($this->_initItem()){

            $this->loadLayout();
            $this->_setActiveMenu('system/aitoc');
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('aitcheckoutfields/edit'))
                 ->_addLeft($this->getLayout()->createBlock('aitcheckoutfields/edit_tabs'));
            $this->renderLayout();
                
        } else {
            
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcheckoutfields')->__('Item does not exist'));
            $this->_redirect('*/*/');
            
        }
    }
 
    public function newAction() 
    {
            $this->_forward('edit');
    }
	
    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

#        $this->_entityTypeId=$this->_checkoutTypeId;
        
        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('catalog/resource_eav_attribute')
                ->loadByCode($this->_checkoutTypeId, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
        
    }
    /* to refactor*/
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {

		    $model = Mage::getModel('catalog/resource_eav_attribute');		    
            /* @var $model Mage_Catalog_Model_Entity_Attribute */

            if ($id = $this->getRequest()->getParam('attribute_id'))
            {
                $model->load($id);
                $data['attribute_code'] = $model->getAttributeCode();
                $data['frontend_input'] = $model->getFrontendInput();
            }
			
            $sRealInput = $data['frontend_input'];
            
            if ($data['frontend_input'] == 'checkbox')
            {
                $data['frontend_input'] = 'multiselect';
            }
            
            if ($data['frontend_input'] == 'radio')
            {
                $data['frontend_input'] = 'select';
            }
            
            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if($data['frontend_input']==='static')
            {
                $defaultValueField = 'default_value_static';
            }
            
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }
			
			// process website/store assign

			if (!isset($data['is_visible_in_advanced_search']))
			{
			    $data['is_visible_in_advanced_search'] = 0;
			}
			
            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }
            if (!isset($data['is_filterable'])) {
                $data['is_filterable'] = 0;
            }
            if (!isset($data['is_filterable_in_search'])) {
                $data['is_filterable_in_search'] = 0;
            }
			
			if (!$data['is_visible_in_advanced_search']) // is not global
			{
    			if (isset($data['assign_website']) AND $data['assign_website'])
    			{
    			    $data['apply_to'] = implode(',', array_keys($data['assign_website']));
    			}
    			else 
    			{
    			    $data['apply_to'] = '';
    			}
    			
    			if (isset($data['assign_store']) AND $data['assign_store'])
    			{
    			    $sCommonData = '';
    			    
    			    $aStoreHash = array();
    			    
    			    foreach ($data['assign_store'] as $iWebsiteKey => $aStoreData)
    			    {
    			        $aStoreHash[] = implode(',', $aStoreData);
    			    }
    			    
    			    $data['note'] = implode(',', $aStoreHash);
    			}
    			else 
    			{
    			    $data['note'] = '';
    			}
			}
			

            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);

            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }
            
            if (!isset($data['is_global'])) {
                $data['is_global'] = 0;
            }
            
            if (!isset($data['is_unique'])) {
                $data['is_unique'] = 0;
            }
            
            if (!isset($data['is_wysiwyg_enabled'])) {
                $data['is_wysiwyg_enabled'] = 0;
            }
            
            if (!isset($data['is_html_allowed_on_front'])) {
                $data['is_html_allowed_on_front'] = 1;
            }
            
            if (!isset($data['is_visible_on_front'])) {
                $data['is_visible_on_front'] = 1;
            }
            
            if (!isset($data['used_for_sort_by'])) {
                $data['used_for_sort_by'] = 1;
            }
            if (in_array($data['frontend_input'], array('multiselect', 'select', 'radio', 'checkbox')))
            {
                $data['source_model']='eav/entity_attribute_source_table';
            }
            if ($data['frontend_input']==='date')
            {
                $data['backend_type']='varchar';   
            }
            #print_r($data);
            #exit;
            $model->addData($data);


            $iProductEntityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId(); // to imitate product attribute saving
            $model->setEntityTypeId($iProductEntityTypeId);
            $model->setIsUserDefined(1);

			
			try {

//                if (in_array($data['frontend_input'], array('multiselect', 'select', 'radio', 'checkbox')))
//                {
//                    echo '<pre>';print_r($data); echo '</pre>';
//                    if (!empty($data['default']))
//                    {
//                        $sDefValue = $data['default'];
//                    }
//                    else 
//                    {
//                        $sDefValue = '';
//                    }
//                }
//                else 
//                {
//        			$sDefValue = $model->getDefaultValue();
//                }


				$model->save();

				$id=$model->getId();
                                                                
                if (isset($data['frontend_desc']))
				{
                    Mage::getModel('aitcheckoutfields/attributecustomergroups')->saveGroups($id, $data['customer_group_ids']);
                }
                                                                
                if (isset($data['category_ids']))
				{
                    $category_ids = explode(',',$data['category_ids']);
                    $category_ids = array_unique($category_ids);
                    Mage::getModel('aitcheckoutfields/attributecatalogrefs')->saveRefs($id, 'category',$category_ids);
                }
                                                                
                if (isset($data['in_products']))
				{  
                    $product_ids = explode('&',$data['in_products']);
                    $product_ids = array_unique($product_ids);                    
                    Mage::getModel('aitcheckoutfields/attributecatalogrefs')->saveRefs($id, 'product',$product_ids);
                }
                                                                
                // save descs
				
				$aDescription = array();
				
				if (isset($data['frontend_desc']) AND $data['frontend_desc'])
				{
				    $aDescription = $data['frontend_desc'];
				}
				
				Mage::getModel('aitcheckoutfields/aitcheckoutfields')->saveAttributeDescription($id, $aDescription);

				//$oUpdateModel = Mage::getModel('catalog/entity_attribute');

				$oUpdateModel = Mage::getModel('catalog/resource_eav_attribute');
			    $oUpdateModel->load($id);

				if ($data['frontend_input'] != $sRealInput)
				{
    				$oUpdateModel->setFrontendInput($sRealInput);
				}
				
//				if ($sDefValue AND is_array($sDefValue))
//				{
//				    $sDefValue = implode(',', $sDefValue);
//				}
//				
//
//				$oUpdateModel->setDefaultValue($sDefValue);
				$oUpdateModel->setAitocflag(true);
                $oUpdateModel->setEntityTypeId($this->_checkoutTypeId);
			    $oUpdateModel->save();

				
				// save need select
				
				$aNeedSelect = array();
				
				if (isset($data['frontend_need_sel']) AND $data['frontend_need_sel'])
				{
				    $aNeedSelect = $data['frontend_need_sel'];
				}
				
				Mage::getModel('aitcheckoutfields/aitcheckoutfields')->saveAttributeNeedSelect($id, $aNeedSelect);
				
				
                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::getSingleton('adminhtml/session')->setAttributeData(false);
				
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('aitcheckoutfields')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('attribute_id' => $id));
					return;
				}
				
				$this->_redirect('*/*/index/filter//');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitcheckoutfields')->__('Unable to find item to save'));
        $this->_redirect('*/*/index/filter//');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('attribute_id') > 0 ) {
			try {
				$model = Mage::getModel('eav/entity_attribute');
				$id = $this->getRequest()->getParam('attribute_id');
				$model->setId($id)->delete();
                Mage::getModel('aitcheckoutfields/aitcheckoutfields')
                      ->setId($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/index/filter//');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
			}
		}
		$this->_redirect('*/*/index/filter//');
	}


    public function ordereditAction()
    {
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order');

        if ($id) {
            
            $model->load($id);
            
            Mage::register('aitoc_order_saved_object', $model);
            
            if (!$model->getId()) 
            {
                $this->_redirect('adminhtml/sales_order');
                return;
            }
        }
        else 
        {
            $this->_redirect('adminhtml/sales_order');
            return;
        }

       
        $block = $this->getLayout()->createBlock('aitcheckoutfields/orderedit_edit')
                      ->setData('action', $this->getUrl('*/*/save'));

        $this->_initAction();
        $this->getLayout()->getBlock('head')
             ->setCanLoadExtJs(true)
             ->setCanLoadRulesJs(true);

        $this->_addContent($block)
             ->renderLayout();

    }    
    
    
	public function ordersaveAction() {
	    
        $id = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('sales/order');

        if ($id)
        {
            $model->load($id);
            if (!$model->getId()) 
            {
                $this->_redirect('adminhtml/sales_order');
                return;
            }
        }
        else 
        {
            $this->_redirect('adminhtml/sales_order');
            return;
        }
	    
	    
		if ($data = $this->getRequest()->getPost('order'))
        {
	    //saveEditedCustomOrderData
            try
            {
                Mage::getModel('aitcheckoutfields/aitcheckoutfields')->saveEditedCustomOrderData($data, $id);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('salesrule')->__('Order custom data were successfully saved'));
                $this->_redirect('adminhtml/sales_order/view', array('order_id' => $id));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/orderedit', array('order_id' => $id));
                return;
            }
        }
        $this->_redirect('*/*/');
	}    
	
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/aitcheckoutfields');
    }


    public function exportexcelcfmAction()
    {
        $fileName   = 'checkoutfields.xml';
        Mage::register('aitcheckoutfields_excel', 1);
        $grid       = $this->getLayout()->createBlock('adminhtml/sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }
	
    
}

?>