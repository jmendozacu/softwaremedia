<?php
class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit_Tab_Notes extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('quotedispatch_form', array('legend'=>Mage::helper('quotedispatch')->__('Internal Notes')));
     
      $fieldset->addField('note', 'editor', array(
          'name'      => 'note',
          'label'     => Mage::helper('quotedispatch')->__('Note'),
          'title'     => Mage::helper('quotedispatch')->__('Note'),
          'style'     => 'width:300px; height:100px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getQuotedispatchData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQuotedispatchData());
          Mage::getSingleton('adminhtml/session')->setQuotedispatchData(null);
      } elseif ( Mage::registry('quotedispatch_data') ) {
          $form->setValues(Mage::registry('quotedispatch_data')->getData());
      }
      return parent::_prepareForm();
  }
  
  
    protected function _afterToHtml($html)
    {
    
        $split = '<div class="hor-scroll">';
        
        $form_parts = explode($split,$html);
        
        array_splice($form_parts,1,0,$this->getNotesHtml());
        
        $new_html = implode($split, $form_parts);
    
        return $new_html;
    }
    
    
    protected function getNotesHtml() {
    
        $id = Mage::app()->getRequest()->getParam('id');
    
        if ($id) {
            $html = '<table cellspacing="0" class="form-list"><tbody>';
            $collection = Mage::getModel('quotedispatch/quotedispatch_notes')->getCollection()
                ->addFieldToFilter('quotedispatch_id',$id)
                ->setOrder('quotedispatch_note_id','ASC');
            
            foreach ($collection as $note) {
                $html .= '<tr><td class="label">'.$note->getCreatedBy().'</td><td class="value"><div style="background:#eaeaea;padding:5px">'.$note->getContent().'</div></td></tr>';
            }
            
            $html .= '</tbody></table>';
        }
        return $html;
    }
    
}
