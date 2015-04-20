<?php
/**
 * SoftwareMedia_Wizard extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       SoftwareMedia
 * @package        SoftwareMedia_Wizard
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Question admin controller
 *
 * @category    SoftwareMedia
 * @package     SoftwareMedia_Wizard
 * @author      Ultimate Module Creator
 */
class SoftwareMedia_Wizard_Adminhtml_Wizard_QuestionController extends SoftwareMedia_Wizard_Controller_Adminhtml_Wizard
{
    /**
     * init question
     *
     * @access protected
     * @return SoftwareMedia_Wizard_Model_Question
     * @author Ultimate Module Creator
     */
    protected function _initQuestion()
    {
        $questionId = (int) $this->getRequest()->getParam('id', false);
        $question = Mage::getModel('softwaremedia_wizard/question');
        if ($questionId) {
            $question->load($questionId);
        } else {
            $question->setData($question->getDefaultValues());
        }
        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setQuestionActiveTabId($activeTabId);
        }
        Mage::register('question', $question);
        Mage::register('current_question', $question);
        return $question;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }

    /**
     * Add new question form
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function addAction()
    {
        Mage::getSingleton('admin/session')->unsQuestionActiveTabId();
        $this->_forward('edit');
    }

    /**
     * Edit question page
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function editAction()
    {
        $params['_current'] = true;
        $redirect = false;
        $parentId = (int) $this->getRequest()->getParam('parent');
        $questionId = (int) $this->getRequest()->getParam('id');
        $_prevQuestionId = Mage::getSingleton('admin/session')->getLastEditedQuestion(true);
        if ($_prevQuestionId &&
            !$this->getRequest()->getQuery('isAjax') &&
            !$this->getRequest()->getParam('clear')) {
            $this->getRequest()->setParam('id', $_prevQuestionId);
        }
        if ($redirect) {
            $this->_redirect('*/*/edit', $params);
            return;
        }
        if (!($question = $this->_initQuestion())) {
            return;
        }
        $this->_title($questionId ? $question->getQuestion() : $this->__('New Question'));
        $data = Mage::getSingleton('adminhtml/session')->getQuestionData(true);
        if (isset($data['question'])) {
            $question->addData($data['question']);
        }
        if ($this->getRequest()->getQuery('isAjax')) {
            $breadcrumbsPath = $question->getPath();
            if (empty($breadcrumbsPath)) {
                $breadcrumbsPath = Mage::getSingleton('admin/session')->getQuestionDeletedPath(true);
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }
            Mage::getSingleton('admin/session')->setLastEditedQuestion($question->getId());
            $this->loadLayout();
            $eventResponse = new Varien_Object(
                array(
                    'content' => $this->getLayout()->getBlock('question.edit')->getFormHtml().
                        $this->getLayout()->getBlock('question.tree')->getBreadcrumbsJavascript(
                            $breadcrumbsPath,
                            'editingQuestionBreadcrumbs'
                        ),
                    'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(),
                )
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($eventResponse->getData()));
            return;
        }
        $this->loadLayout();
        $this->_title(Mage::helper('softwaremedia_wizard')->__('Wizard'))
             ->_title(Mage::helper('softwaremedia_wizard')->__('Questions'));
        $this->_setActiveMenu('catalog/softwaremedia_wizard/question');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true)
            ->setContainerCssClass('question');

        $this->_addBreadcrumb(
            Mage::helper('softwaremedia_wizard')->__('Manage Questions'),
            Mage::helper('softwaremedia_wizard')->__('Manage Questions')
        );
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * Get tree node (Ajax version)
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function questionsJsonAction()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            Mage::getSingleton('admin/session')->setQuestionIsTreeWasExpanded(true);
        } else {
            Mage::getSingleton('admin/session')->setQuestionIsTreeWasExpanded(false);
        }
        if ($questionId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $questionId);
            if (!$question = $this->_initQuestion()) {
                return;
            }
            $this->getResponse()->setBody(
                $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_question_tree')
                    ->getTreeJson($question)
            );
        }
    }

    /**
     * Move question action
     * @access public
     * @author Ultimate Module Creator
     */
    public function moveAction()
    {
        $question = $this->_initQuestion();
        if (!$question) {
            $this->getResponse()->setBody(
                Mage::helper('softwaremedia_wizard')->__('Question move error')
            );
            return;
        }
        $parentNodeId   = $this->getRequest()->getPost('pid', false);
        $prevNodeId = $this->getRequest()->getPost('aid', false);
        try {
            $question->move($parentNodeId, $prevNodeId);
            $this->getResponse()->setBody("SUCCESS");
        } catch (Mage_Core_Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        } catch (Exception $e) {
            $this->getResponse()->setBody(
                Mage::helper('softwaremedia_wizard')->__('Question move error')
            );
            Mage::logException($e);
        }
    }

    /**
     * Tree Action
     * Retrieve question tree
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function treeAction()
    {
        $questionId = (int) $this->getRequest()->getParam('id');
        $question = $this->_initQuestion();
        $block = $this->getLayout()->createBlock('softwaremedia_wizard/adminhtml_question_tree');
        $root  = $block->getRoot();
        $this->getResponse()->setBody(
            Mage::helper('core')->jsonEncode(
                array(
                    'data' => $block->getTree(),
                    'parameters' => array(
                        'text'          => $block->buildNodeName($root),
                        'draggable'     => false,
                        'allowDrop'     => ($root->getIsVisible()) ? true : false,
                        'id'            => (int) $root->getId(),
                        'expanded'      => (int) $block->getIsWasExpanded(),
                        'question_id' => (int) $question->getId(),
                        'root_visible'  => (int) $root->getIsVisible()
                    )
                )
            )
        );
    }

    /**
     * Build response for refresh input element 'path' in form
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function refreshPathAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            $question = Mage::getModel('softwaremedia_wizard/question')->load($id);
            $this->getResponse()->setBody(
                Mage::helper('core')->jsonEncode(
                    array(
                       'id' => $id,
                       'path' => $question->getPath(),
                    )
                )
            );
        }
    }

    /**
     * Delete question action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function deleteAction()
    {
        if ($id = (int) $this->getRequest()->getParam('id')) {
            try {
                $question = Mage::getModel('softwaremedia_wizard/question')->load($id);
                Mage::getSingleton('admin/session')->setQuestionDeletedPath($question->getPath());

                $question->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('The question has been deleted.')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('softwaremedia_wizard')->__('An error occurred while trying to delete the question.')
                );
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('_current'=>true)));
                Mage::logException($e);
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/', array('_current'=>true, 'id'=>null)));
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author Ultimate Module Creator
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/softwaremedia_wizard/question');
    }

    /**
     * Question save action
     *
     * @access public
     * @return void
     * @author Ultimate Module Creator
     */
    public function saveAction()
    {
        if (!$question = $this->_initQuestion()) {
            return;
        }
        $refreshTree = 'false';
        if ($data = $this->getRequest()->getPost('question')) {
            $question->addData($data);
            if (!$question->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    $parentId = Mage::helper('softwaremedia_wizard/question')->getRootQuestionId();
                }
                $parentQuestion = Mage::getModel('softwaremedia_wizard/question')->load($parentId);
                $question->setPath($parentQuestion->getPath());
            }
            try {
                $question->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('softwaremedia_wizard')->__('The question has been saved.')
                );
                $refreshTree = 'true';
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage())->setQuestionData($data);
                Mage::logException($e);
                $refreshTree = 'false';
            }
        }
        $url = $this->getUrl('*/*/edit', array('_current' => true, 'id' => $question->getId()));
        $this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("' . $url . '", {}, '.$refreshTree.');</script>'
        );
    }
}
