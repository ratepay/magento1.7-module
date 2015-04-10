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
 * @category RatePAY
 * @package RatePAY_Ratepaypayment
 * @copyright Copyright (c) 2015 RatePAY GmbH (https://www.ratepay.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class RatePAY_Ratepaypayment_Adminhtml_BulkController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize bulk view
     * 
     * @return RatePAY_Ratepaypayment_Adminhtml_BulkController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('bulk/ratepay');
        return $this;
    }

    /**
     * Render the bulk layout
     * 
     * @return RatePAY_Ratepaypayment_Adminhtml_BulkController
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
    
    /**
     * Start the mass cancel and redirect to index
     */
    public function massCancelAction()
    {
        $this->_ratepayMassEvent('cancel');
                
        $this->_redirect('*/*/index');
    }
    
    /**
     * Start the mass invoice and redirect to index
     */
    public function massInvoiceAction()
    {
        $this->_ratepayMassEvent('invoice');
                
        $this->_redirect('*/*/index');
    }

    /**
     * Start the mass creditmemo and redirect to index
     */
    public function massCreditmemoAction()
    {
        $this->_ratepayMassEvent('creditmemo');
                
        $this->_redirect('*/*/index');
    }
    
    /**
     * Handle the mass events and set the messages
     * 
     * @param string $type 
     */
    private function _ratepayMassEvent($type)
    {
        $counter = 0;
        $msg = null;
        $error = null;
        foreach ($this->getRequest()->getParam('order_id') as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($this->_isEventPossible($type, $order)) {
                try {
                    $this->_ratepayEvent($type, $order);
                    $counter++;
                } catch (Exception $e) {
                    $error .= $e->getMessage() . ' Order # ' . $order->getIncrementId() . '<br/>';
                } 
            } else {
                $msg = Mage::helper('ratepaypayment')->__('The order with the number %s can not be '. $type . 'ed.<br/>', $order->getIncrementId());
            }
        }

        if (!empty($msg)) {
            Mage::getSingleton('adminhtml/session')->addNotice($msg);
        }
        
        if (!empty($error)) {
            Mage::getSingleton('adminhtml/session')->addError($error);
        }
        
        if ($counter > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ratepaypayment')->__('Total of %d record(s) were  '. $type . 'ed.', $counter));
        }
    }
    
    /**
     * Push the invoice, creditmemo or cancel event for the given order
     * 
     * @param string $type
     * @param Mage_Sales_Model_Order
     * @throws Exception Wrong operation!
     */
    private function _ratepayEvent($type, $order)
    {
        switch($type){
            case 'invoice':
                $invoice = $order->prepareInvoice();
                $invoice->register()->save();
                $order->setTotalInvoiced($order->getTotalInvoiced() + $invoice->getGrandTotal());
                $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() + $invoice->getBaseGrandTotal());
                $order->save();
                break;
            case 'creditmemo':
                $creditmemo = Mage::getModel('sales/service_order', $order)->prepareCreditmemo();
                $creditmemo->register()->save();
                $order->setTotalRefunded($order->getTotalRefunded() + $creditmemo->getGrandTotal());
                $order->setBaseTotalRefunded($order->getBaseTotalRefunded() + $creditmemo->getGrandTotal());
                $order->setBaseTotalRefunded($creditmemo->getGrandTotal());
                $order->save();
                break;
            case 'cancel':
                $order->cancel()->save();
                break;
            default:
                throw new Exception('Wrong operation!');
        }
    }
    
    /**
     * Is event for this order allowed
     * 
     * @param string $type
     * @param Mage_Sales_Model_Order $order
     * @return boolean 
     */
    private function _isEventPossible($type, $order)
    {
        if ($type == 'invoice') {
            return $order->canInvoice();
        } else if ($type == 'creditmemo') {
            return $order->canCreditmemo();
        } else if ($type == 'cancel') {
            return $order->canCancel();
        }
        
        return false;
    }
    
}