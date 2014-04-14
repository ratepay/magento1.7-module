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
class PayIntelligent_Ratepay_Model_Observer
{

    private $_errorMessage;

    /**
     * Starts the PAYMENT QUERY if activated and saves the allowed payment methods in the RatePAY session
     *
     * @param Varien_Event_Observer $observer
     */

    public function paymentQuery(Varien_Event_Observer $observer)
    {
        $paymentMethod = 'ratepay_paymentquery';

        $quote = Mage::getModel('checkout/cart')->getQuote();
        $payment = $quote->getPayment();
        $payment->setMethod($paymentMethod);
        $payment->save();

        $helper_query = Mage::helper('ratepay/query');

        if ($helper_query->isPaymentQueryActive($quote) && $helper_query->isCustomerComplete($quote)) {
            $querySubType = $helper_query->getQuerySubType($quote);

            $client = Mage::getSingleton('ratepay/request');
            $helper_mapping = Mage::helper('ratepay/mapping');
            $result = $client->callPaymentInit($helper_mapping->getRequestHead($quote), $helper_mapping->getLoggingInfo($quote));

            if (is_array($result) || $result == true) {
                $transactionId = $result['transactionId'];
                $transactionShortId = $result['transactionShortId'];

                $payment = $quote->getPayment();
                $payment->setAdditionalInformation('transactionId', $result['transactionId']);
                $payment->setAdditionalInformation('transactionShortId', $result['transactionShortId']);
                $payment->save();
                $result = $client->callPaymentQuery($helper_mapping->getRequestHead($quote, $querySubType),
                                                    $querySubType,
                                                    $helper_mapping->getRequestCustomer($quote),
                                                    $helper_mapping->getRequestBasket($quote),
                                                    $helper_mapping->getLoggingInfo($quote));

                //if ((is_array($result) || $result == true)) {
                    $allowedProducts = array("INVOICE", "ELV", "PREPAYMENT"); // $helper_query->getProducts($result['products']['product']);

                    Mage::getSingleton('ratepay/session')->setQueryActive(true);
                    Mage::getSingleton('ratepay/session')->setTransactionId($transactionId);
                    Mage::getSingleton('ratepay/session')->setTransactionShortId($transactionShortId);
                    Mage::getSingleton('ratepay/session')->setAllowedProducts($allowedProducts);
                //}
            } else {
                if (!Mage::getStoreConfig('payment/' . $paymentMethod . '/sandbox', $quote->getStoreId())) {
                    $this->_hidePaymentMethod();
                }
            }

        } elseif (!$helper_query->isCustomerComplete($quote)) {
            Mage::getSingleton('ratepay/session')->setQueryActive(true);
            Mage::getSingleton('ratepay/session')->setAllowedProducts(false);

        } else {
            Mage::getSingleton('ratepay/session')->setQueryActive(false);
        }
        $a = 2;

    }

    /**
     * Add payment fee if payment fee is set for RatePAY and removes it again if another payment method was choosen
     *
     * @param Varien_Event_Observer $observer
     */
    public function handlePaymentFee(Varien_Event_Observer $observer)
    {
        try {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $skuInvoice = Mage::getStoreConfig('payment/ratepay_rechnung/payment_fee', $quote->getStoreId());
            $skuElv = Mage::getStoreConfig('payment/ratepay_directdebit/payment_fee', $quote->getStoreId());
            $skuRate = Mage::getStoreConfig('payment/ratepay_rate/payment_fee', $quote->getStoreId());
            $paymentMethod = $observer->getEvent()->getData('input')->getData('method');
            $sku = Mage::getStoreConfig('payment/' . $paymentMethod . '/payment_fee', $quote->getStoreId());
            if (Mage::helper('ratepay/payment')->isRatepayPayment($paymentMethod)) {
                $flag = true;
                foreach ($quote->getAllItems() as $item) {
                    if (($item->getSku() == $skuInvoice || $item->getSku() == $skuElv || $item->getSku() == $skuRate) && $item->getSku() != $sku) {
                        $quote->removeItem($item->getId());
                    }
                    
                    if ($item->getSku() == $sku) {
                        $item->calcRowTotal();
                        $flag = false;
                    }
                }

                if ($flag) {
                    $product = Mage::getModel('catalog/product');
                    $id = $product->getIdBySku($sku);
                    if (!empty($id)) {
                        $product->load($id);
                        $item = $quote->addProduct($product);
                        $item->calcRowTotal();
                    }
                }
            } else {
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getSku() == $skuInvoice || $item->getSku() == $skuElv || $item->getSku() == $skuRate) {
                        $quote->removeItem($item->getId());
                    }
                }
            }
            
            $quote->collectTotals();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * If the order was successfull sending the PAYMENT_CONFIRM Call to RatePAY
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayConfirmCall(Varien_Event_Observer $observer)
    {
        if ($orderIds = $observer->getEvent()->getOrderIds()) { // frontend event
            $orderId = current($orderIds);
            if (!$orderId) {
                return;
            }
            $order = Mage::getModel('sales/order')->load($orderId);
        } else { // adminhtml event
            $order = $observer->getEvent()->getOrder();
        }

        if (Mage::getSingleton('core/session')->getBankdataAfter()) {
            $piEncryption = new Pi_Util_Encryption_MagentoEncryption();
            $bankdata = array(
                'owner' => $data[$code . '_account_holder'],
                'accountnumber' => $data[$code . '_account_number'],
                'bankcode' => $data[$code . '_bank_code_number'],
                'bankname' => $data[$code . '_bank_name']
            );
            Mage::getSingleton('core/session')->setBankdataAfter(false);
            $piEncryption->saveBankdata($order->getCustomerId(), $bankdata);
        }

        if (Mage_Sales_Model_Order::STATE_PROCESSING == $order->getState()) {
            if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
                $client = Mage::getSingleton('ratepay/request');
                $helper = Mage::helper('ratepay/mapping');
                $result = $client->callPaymentConfirm($helper->getRequestHead($order), $helper->getLoggingInfo($order));

                if (is_array($result) || $result == true) {

                    // save entry in sales_payment_transaction
                    $message = 'PAYMENT_REQUEST SEND (authorize)';
                    $payment = $order->getPayment();
                    if ($payment->getMethod() == 'ratepay_rate') {
                        $payment->setAdditionalInformation('Rate Total Amount', Mage::getSingleton('checkout/session')->getRatepayRateTotalAmount());
                        $payment->setAdditionalInformation('Rate Amount', Mage::getSingleton('checkout/session')->getRatepayRateAmount());
                        $payment->setAdditionalInformation('Rate Interest Rate', Mage::getSingleton('checkout/session')->getRatepayRateInterestRate());
                        $payment->setAdditionalInformation('Rate Interest Amount', Mage::getSingleton('checkout/session')->getRatepayRateInterestAmount());
                        $payment->setAdditionalInformation('Rate Service Charge', Mage::getSingleton('checkout/session')->getRatepayRateServiceCharge());
                        $payment->setAdditionalInformation('Rate Annual Percentage Rate', Mage::getSingleton('checkout/session')->getRatepayRateAnnualPercentageRate());
                        $payment->setAdditionalInformation('Rate Monthly Debit Interest', Mage::getSingleton('checkout/session')->getRatepayRateMonthlyDebitInterest());
                        $payment->setAdditionalInformation('Rate Number of Rates Full', Mage::getSingleton('checkout/session')->getRatepayRateNumberOfRatesFull());
                        $payment->setAdditionalInformation('Rate Number of Rates', Mage::getSingleton('checkout/session')->getRatepayRateNumberOfRates());
                        $payment->setAdditionalInformation('Rate Rate', Mage::getSingleton('checkout/session')->getRatepayRateRate());
                        $payment->setAdditionalInformation('Rate Last Rate', Mage::getSingleton('checkout/session')->getRatepayRateLastRate());
                        $payment->setAdditionalInformation('Debit Select', Mage::getSingleton('checkout/session')->getRatepayPaymentFirstDay());

                        Mage::getSingleton('checkout/session')->setRatepayRateTotalAmount(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateAmount(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateInterestRate(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateInterestAmount(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateServiceCharge(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateAnnualPercentageRate(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateMonthlyDebitInterest(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRatesFull(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRates(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateRate(null);
                        Mage::getSingleton('checkout/session')->setRatepayRateLastRate(null);
                        Mage::getSingleton('checkout/session')->getRatepayPaymentFirstDay(null);
                    }

                    Mage::helper('ratepay/payment')->addNewTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, $message);

                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_success', 'success')->save();
                } else {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_failed', 'failure')->save();
                }
            }
        }
    }

    /**
     * Send a CONFIRMATION_DELIVER call with all invoiced items
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception Pi Delivery was not successful.
     */
    public function sendRatepayDeliverCall(Varien_Event_Observer $observer)
    {
        $client = Mage::getSingleton('ratepay/request');
        $helper = Mage::helper('ratepay/mapping');
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
            $result = $client->callConfirmationDeliver($helper->getRequestHead($order), $helper->getRequestBasket($invoice), $helper->getLoggingInfo($order));

            if (!$result) {
                Mage::throwException(Mage::helper('ratepay')->__('Pi Delivery was not successful.'));
            }

            //Mage::helper('ratepay/payment')->convertInvoiceToShipment($invoice);
            Mage::helper('ratepay/payment')->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true, 'CONFIRMATION_DELIVER SEND (capture)');
            if (!$order->canInvoice()) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_processing', 'success')->save();
            } else {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'payment_complete', 'success')->save();
            }
        }
    }

    /**
     * Observer for the controller_action_postdispatch_adminhtml_sales_order_invoice_save event
     * sent the RatePAY specific invoice mail
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayInvoiceEmail(Varien_Event_Observer $observer)
    {
        $invoiceController = $observer->getEvent()->getControllerAction();

        $request = $invoiceController->getRequest();
        $orderId = $request->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
            $invoiceArray = $request->getParam('invoice');
            if (isset($invoiceArray['send_email']) && $invoiceArray['send_email']) {
                Mage::helper('ratepay/email')->sendNewInvoiceEmail($order);
            }
        }
    }

    /**
     * Send a PAYMENT_CHANGE (full-return, partial-return, credit) call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCreditmemoCall(Varien_Event_Observer $observer)
    {
        $client = Mage::getSingleton('ratepay/request');
        $mappingHelper = Mage::helper('ratepay/mapping');
        $paymentHelper = Mage::helper('ratepay/payment');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
            if (!$this->isCreditmemoAllowed($creditmemo)) {
                Mage::throwException(Mage::helper('ratepay')->__($this->_errorMessage));
            }

            $data = array(
                'creditmemo' => $paymentHelper->getAllCreditmemoItems($order),
                'temp_creditmemo' => $paymentHelper->getTempCreditmemoItems($creditmemo)
            );

            $items = array();
            if ($paymentHelper->isOrderCanceled($order)) {
                $items = $paymentHelper->getAllInvoiceItems($order);
            } else {
                $items = $mappingHelper->getArticles($order);
            }

            $availableProducts = $paymentHelper->getAvailableProducts($items, $data);
            $amount = $paymentHelper->getShoppingBasketAmount($order, $creditmemo);

            $basketInfo = $mappingHelper->getRequestBasket($creditmemo, $amount, $availableProducts);
            $customerInfo = $mappingHelper->getRequestCustomer($order);
            $paymentInfo = $mappingHelper->getRequestPayment($order, $amount);
            $loggingInfo = $mappingHelper->getLoggingInfo($order);


            if ($creditmemo->getAdjustmentPositive() > 0) {
                $headInfo = $mappingHelper->getRequestHead($order, 'credit');
                $result = $client->callPaymentChange($headInfo, $customerInfo, $basketInfo, $paymentInfo, $loggingInfo);
                $msg = Mage::helper('ratepay')->__('Pi Voucher was not successful.');
            } else {
                $headInfo = $mappingHelper->getRequestHead($order, $this->_getSubtype($creditmemo, 'return'));
                $result = $client->callPaymentChange($headInfo, $customerInfo, $basketInfo, $paymentInfo, $loggingInfo);
                $msg = Mage::helper('ratepay')->__('Pi Return was not successful.');
            }

            if (!$result) {
                Mage::throwException($msg);
            }

            Mage::helper('ratepay/email')->sendNewCreditmemoEmail($creditmemo);

            $paymentHelper->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo, true, 'PAYMENT_CHANGE SEND (refund)');
        }
    }

    /**
     * Send a PAYMENT_CHANGE (full-cancellation, partial-cancellation) call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCancelCall(Varien_Event_Observer $observer)
    {
        $client = Mage::getSingleton('ratepay/request');
        $mappingHelper = Mage::helper('ratepay/mapping');
        $paymentHelper = Mage::helper('ratepay/payment');

        $order = $observer->getEvent()->getOrder();
        if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
            $orderItems = array();

            $amount = 0;
            if (!$this->_isFullCancel($order)) {
                $orderItems = $paymentHelper->getAllInvoiceItems($order);
                $amount = $order->getTotalInvoiced() - $order->getTotalRefunded();
            }

            $data = array(
                'creditmemo' => $paymentHelper->getAllCreditmemoItems($order)
            );

            $availableProducts = $paymentHelper->getAvailableProducts($orderItems, $data);

            $basketInfo = $mappingHelper->getRequestBasket($order, $amount, $availableProducts);
            $headInfo = $mappingHelper->getRequestHead($order, $this->_getSubtype($order, 'cancellation'));
            $customerInfo = $mappingHelper->getRequestCustomer($order);
            $paymentInfo = $mappingHelper->getRequestPayment($order, $amount);
            $loggingInfo = $mappingHelper->getLoggingInfo($order);

            $result = $client->callPaymentChange($headInfo, $customerInfo, $basketInfo, $paymentInfo, $loggingInfo);

            if (!$result) {
                Mage::throwException(Mage::helper('ratepay')->__('Pi Cancellation was not successful.'));
            }
        }
    }

    public function isInvoiceCreated(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getShipment()->getOrder();
        if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
            foreach ($observer->getEvent()->getShipment()->getAllItems() as $item) {
                if ($item->getOrderItem()->getQtyInvoiced() < $item->getQty()) {
                    Mage::throwException(Mage::helper('ratepay')->__('Pi Please invoice the articles you want to ship.'));
                }
            }
        }
    }

    /**
     * Is the given creditmemo allowed
     *
     * @param Mage_Sales_Model_Order_Creditmemo
     */
    private function isCreditmemoAllowed(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if ($creditmemo->getShippingAmount() < $creditmemo->getOrder()->getShippingAmount() && $creditmemo->getShippingAmount() > 0) {
            $this->_errorMessage = 'Pi Only full return of shipping is possible.';
            return false;
        }
        
        if ($creditmemo->getAdjustmentPositive() > 0 && $this->_getItemCount($creditmemo) > 0) {
            $this->_errorMessage = 'Pi Please create product returns and positive adjustments separately.';
            return false;
        }
        
        return true;
    }

    /**
     * Retrieve subtype
     *
     * @param Mage_Sales_Model_Creditmemo | Mage_Sales_Model_Order $object
     * @return string
     */
    private function _getSubtype($object, $type)
    {
        $subType = 'partial-' . $type;
        ($type == 'return') ? $method = '_isFullReturn' : $method = '_isFullCancel';
        if ($this->$method($object)) {
            $subType = 'full-' . $type;
        }

        return $subType;
    }

    /**
     * Is full return
     *
     * @param Mage_Sales_Model_Order_Creditmemo $object
     * @return boolean
     */
    private function _isFullReturn(Mage_Sales_Model_Order_Creditmemo $object)
    {
        return $this->_getItemCount($object) == $this->_getItemCount($object->getOrder());
    }

    /**
     * Is full cancel
     *
     * @param Mage_Sales_Model_Order $object
     * @return boolean
     */
    private function _isFullCancel(Mage_Sales_Model_Order $order)
    {
        return $this->getCancelItemCount($order) == $this->_getItemCount($order);
    }

    /**
     * Retrieve the number of canceled positions
     *
     * @param Mage_Sales_Model_Order $order
     * @return integer
     */
    private function getCancelItemCount(Mage_Sales_Model_Order $order)
    {
        $counter = 0;
        foreach ($order->getAllItems() as $orderItem) {
            $counter = $counter + $orderItem->getQtyCanceled();
        }
        return $counter;
    }

    /**
     * Retrieve the number of all positions in the given object
     *
     * @param Mage_Sales_Model_Order | Mage_Sales_Model_Order_Creditmemo | Mage_Sales_Model_Order_Invoice $object
     * @return integer
     */
    private function _getItemCount($object)
    {
        $counter = 0;
        if ($object instanceof Mage_Sales_Model_Order) {
            foreach ($object->getAllVisibleItems() as $item) {
                $counter = $counter + $item->getQtyOrdered();
            }
        } else {
            foreach ($object->getAllItems() as $item) {
                $counter = $counter + $item->getQty();
            }
        }
        return $counter;
    }

}
