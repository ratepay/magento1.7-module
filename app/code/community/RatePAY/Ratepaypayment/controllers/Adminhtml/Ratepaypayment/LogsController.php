<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Adminhtml_Ratepaypayment_LogsController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize logs view
     * 
     * @return RatePAY_Ratepaypayment_Adminhtml_LogsController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('logs/ratepay');
        return $this;
    }
    
    /**
     * Render the logs layout
     * 
     * @return RatePAY_Ratepaypayment_Adminhtml_LogsController
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * View single xml request or response
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        !empty($id) ? $id = $this->getRequest()->getParam('id') : $id = $this->getRequest()->getParam('log_id');
        $model = Mage::getModel('ratepaypayment/logging')->load($id);
        if ($model->getId()) {
            Mage::register('ratepay_logging_data', $model);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('ratepaypayment/adminhtml_logs_view'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ratepaypayment')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Normal Magento delete mass action for selected entries
     */
    public function massDeleteAction()
    {
        $logIds = $this->getRequest()->getParam('log_id');

        if (!is_array($logIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ratepaypayment')->__('Please select log entries.'));
        } else {
            try {
                foreach ($logIds as $logId) {
                    Mage::getModel('ratepaypayment/logging')->load($logId)->delete();
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ratepaypayment')->__('Total of %d record(s) were deleted.', count($logIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Extended delete mass action for entries older than x days
     */
    public function massDeleteExtendedAction()
    {
        $days = (int) $this->getRequest()->getParam('days', 0);

        if ($days) {
            try {
                $countBefore = count(Mage::getModel('ratepaypayment/logging')->getCollection());
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $condition = 'date < DATE_SUB(now(),INTERVAL ' . $connection->quoteInto($days) . ' DAY)';
                $table = Mage::getSingleton('core/resource')->getTableName('ratepay_log');
                $connection->query('DELETE FROM ' . $table . ' WHERE ' . $condition);
                $countAfter = count(Mage::getModel('ratepaypayment/logging')->getCollection());
                $count = $countBefore - $countAfter;
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ratepaypayment')->__('Total of %d record(s) were deleted.', $count));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
