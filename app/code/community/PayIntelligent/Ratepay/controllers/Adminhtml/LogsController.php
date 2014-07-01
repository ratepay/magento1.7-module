<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category PayIntelligent
 * @package PayIntelligent_RatePAY
 * @copyright Copyright (c) 2011 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class PayIntelligent_Ratepay_Adminhtml_LogsController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize logs view
     * 
     * @return PayIntelligent_Ratepay_Adminhtml_LogsController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('logs/ratepay');
        return $this;
    }
    
    /**
     * Render the logs layout
     * 
     * @return PayIntelligent_Ratepay_Adminhtml_LogsController
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
        $model = Mage::getModel('ratepay/logging')->load($id);
        if ($model->getId()) {
            Mage::register('ratepay_logging_data', $model);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('ratepay/adminhtml_logs_view'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ratepay')->__('Item does not exist'));
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
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ratepay')->__('Please select log entries.'));
        } else {
            try {
                foreach ($logIds as $logId) {
                    Mage::getModel('ratepay/logging')->load($logId)->delete();
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ratepay')->__('Total of %d record(s) were deleted.', count($logIds)));
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
                $countBefore = count(Mage::getModel('ratepay/logging')->getCollection());
                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                $condition = 'date < DATE_SUB(now(),INTERVAL ' . $connection->quoteInto($days) . ' DAY)';
                $table = Mage::getSingleton('core/resource')->getTableName('pi_ratepay_log');
                $connection->query('DELETE FROM ' . $table . ' WHERE ' . $condition);
                $countAfter = count(Mage::getModel('ratepay/logging')->getCollection());
                $count = $countBefore - $countAfter;
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ratepay')->__('Total of %d record(s) were deleted.', $count));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
