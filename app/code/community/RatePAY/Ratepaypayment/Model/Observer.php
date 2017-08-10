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
class RatePAY_Ratepaypayment_Model_Observer
{

    private $_errorMessage;

    private $_helper;
    private $_helperData;
    private $_helperMapping;
    private $_helperPayment;

    public function __construct()
    {
        $this->_helper = Mage::helper('ratepaypayment');
        $this->_helperData = Mage::helper('ratepaypayment/data');
        $this->_helperMapping = Mage::helper('ratepaypayment/mapping');
        $this->_helperPayment = Mage::helper('ratepaypayment/payment');
    }

    /**
     * Starts the PAYMENT QUERY if activated and saves the allowed payment methods in the RatePAY session
     *
     * @param Varien_Event_Observer $observer
     */
    /*public function paymentQuery(Varien_Event_Observer $observer)
    {
        $ratepayMethodHide = Mage::getSingleton('ratepaypayment/session')->getRatepayMethodHide();
        if ($ratepayMethodHide == true) {
            return false;
        }

        $quote = Mage::getModel('checkout/session')->getQuote();

        $helper_query = Mage::helper('ratepaypayment/query');

        if ($helper_query->isPaymentQueryActive($quote) &&
            $helper_query->validation($quote) &&
            $helper_query->getQuerySubType($quote)) {

            $querySubType = $helper_query->getQuerySubType($quote);

            $client = Mage::getSingleton('ratepaypayment/request');
            $helper_mapping = Mage::helper('ratepaypayment/mapping');

            $currentOrder = array("customer" => $helper_mapping->getRequestCustomer($quote),
                "basket" => $helper_mapping->getRequestBasket($quote),
                "result" => false);

            $previousOrder = Mage::getSingleton('core/session')->getPreviousQuote();

            if (is_array($previousOrder) && !$helper_query->relevantOrderChanges($currentOrder, $previousOrder)) {
                return;
            }

            if (Mage::getSingleton('ratepaypayment/session')->getTransactionId()) {
                $result['transactionId'] = Mage::getSingleton('ratepaypayment/session')->getTransactionId();
            } else {
                $result = $client->callPaymentInit($helper_mapping->getRequestHead($quote, '', 'ratepay_ibs'), $helper_mapping->getLoggingInfo($quote, 'ratepay_ibs'));
            }

            if (is_array($result) || $result == true) {
                $transactionId = $result['transactionId'];

                $payment = $quote->getPayment();
                $payment->setAdditionalInformation('transactionId', $result['transactionId']);
                $payment->save();
                $result = $client->callPaymentQuery($helper_mapping->getRequestHead($quote, $querySubType, 'ratepay_ibs'),
                    $querySubType,
                    $helper_mapping->getRequestCustomer($quote),
                    $helper_mapping->getRequestBasket($quote),
                    $helper_mapping->getLoggingInfo($quote, 'ratepay_ibs'));

                if ((is_array($result) || $result == true)) {
                    $allowedProducts = $helper_query->getProducts($result['products']['product']);
                    $currentOrder['result'] = true;

                    Mage::getSingleton('ratepaypayment/session')->setQueryActive(true);
                    Mage::getSingleton('ratepaypayment/session')->setTransactionId($transactionId);
                    Mage::getSingleton('ratepaypayment/session')->setAllowedProducts($allowedProducts);
                    Mage::getSingleton('checkout/session')->setPreviousQuote($currentOrder);
                } else {
                    $currentOrder['result'] = false;

                    Mage::getSingleton('ratepaypayment/session')->setQueryActive(false);
                    Mage::getSingleton('ratepaypayment/session')->setTransactionId($transactionId);
                    Mage::getSingleton('ratepaypayment/session')->setAllowedProducts(false);
                    Mage::getSingleton('checkout/session')->setPreviousQuote($currentOrder);
                }
            } else {
                if (!$this->_helperData->getRpConfigData($quote, 'ratepay_ibs', 'sandbox')) {
                    Mage::getSingleton('ratepaypayment/session')->setRatepayMethodHide(true);
                }
            }

        } elseif (!$helper_query->validation($quote)) {
            Mage::getSingleton('ratepaypayment/session')->setQueryActive(true);
            Mage::getSingleton('ratepaypayment/session')->setAllowedProducts(false);
        } elseif (!$helper_query->getQuerySubType($quote)) {
            Mage::getSingleton('ratepaypayment/session')->setQueryActive(false);
        } else {
            Mage::getSingleton('ratepaypayment/session')->setQueryActive(false);
        }
    }*/

    /**
     * Add payment fee if payment fee is set for RatePAY and removes it again if another payment method was choosen
     *
     * @param Varien_Event_Observer $observer
     */
    public function handlePaymentFee(Varien_Event_Observer $observer)
    {
        try {
            $paymentMethod = $observer->getEvent()->getData('input')->getData('method');
            if (!$this->_helperPayment->isRatepayPayment($paymentMethod)) {
                return;
            }

            $quote = Mage::getSingleton('checkout/session')->getQuote();
            $skuInvoice = $this->_helperData->getRpConfigData($quote, 'ratepay_rechnung', 'payment_fee');
            $skuElv = $this->_helperData->getRpConfigData($quote, 'ratepay_directdebit', 'payment_fee');
            $skuRate = $this->_helperData->getRpConfigData($quote, 'ratepay_directdebit', 'payment_fee');
            $sku = $this->_helperData->getRpConfigData($quote, $paymentMethod, 'payment_fee');
            // ToDo: Refactor this commented out part
            //if ($this->_helperPayment->isRatepayPayment($paymentMethod)) {
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
            /*} else {
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getSku() == $skuInvoice || $item->getSku() == $skuElv || $item->getSku() == $skuRate) {
                        $quote->removeItem($item->getId());
                    }
                }
            }*/
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * If the order was successfull finalize the Ratepay Order
     *
     * @param Varien_Event_Observer $observer
     */
    public function finalizeRatepayOrder(Varien_Event_Observer $observer)
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

        if (!$this->_helperPayment->isRatepayPayment($order->getPayment()->getMethod())) {
            return;
        }

        if (Mage_Sales_Model_Order::STATE_PROCESSING == $order->getState()) {
            $paymentMethod = $order->getPayment()->getMethod();
            // save entry in sales_payment_transaction
            $message = 'PAYMENT_REQUEST SEND (authorize)';
            $payment = $order->getPayment();
            if (strstr($payment->getMethod(), "ratepay_rate")) {

                $payment->setAdditionalInformation('Rate Total Amount', Mage::getSingleton('ratepaypayment/session')->getRatepayRateTotalAmount());
                $payment->setAdditionalInformation('Rate Total Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'TotalAmount'}());

                $payment->setAdditionalInformation('Rate Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'Amount'}());
                $payment->setAdditionalInformation('Rate Interest Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'InterestRate'}());
                $payment->setAdditionalInformation('Rate Interest Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'InterestAmount'}());
                $payment->setAdditionalInformation('Rate Service Charge', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'ServiceCharge'}());
                $payment->setAdditionalInformation('Rate Annual Percentage Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'AnnualPercentageRate'}());
                $payment->setAdditionalInformation('Rate Monthly Debit Interest', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'MonthlyDebitInterest'}());
                $payment->setAdditionalInformation('Rate Number of Rates Full', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRatesFull'}());
                $payment->setAdditionalInformation('Rate Number of Rates', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRates'}());
                $payment->setAdditionalInformation('Rate Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'Rate'}());
                $payment->setAdditionalInformation('Rate Last Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'LastRate'}());
                $payment->setAdditionalInformation('Debit Select', Mage::getSingleton('ratepaypayment/session')->getRatepayPaymentFirstDay());

                // unset session installment information
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'TotalAmount'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'Amount'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'InterestRate'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'InterestAmount'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'ServiceCharge'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'AnnualPercentageRate'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'MonthlyDebitInterest'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRatesFull'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRates'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'Rate'}();
                Mage::getSingleton('ratepaypayment/session')->{'uns' . $this->_helper->convertUnderlineToCamelCase($paymentMethod) . 'LastRate'}();
                Mage::getSingleton('ratepaypayment/session')->unsRatepayPaymentFirstDay();
            }

            $this->_helperPayment->addNewTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, $message);

            $stateBefore = $this->_helperData->getRpConfigData($order, 'ratepay_general', 'specificstate_before', true, true);
            $statusBefore = $this->_helperData->getRpConfigData($order, 'ratepay_general', 'specificstatus_before', true, true);

            $order->setState(constant('Mage_Sales_Model_Order::' . $stateBefore), $statusBefore, 'success')->save();
        }
    }

    /**
     * Call CONFIRMATION_DELIVER Method if invoice event is set
     *
     * @param Varien_Event_Observer $observer
     */

    public function sendRatepayDeliverCallOnInvoice(Varien_Event_Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        // Check whether RatePAY method is selected and backend operation is hooked
        if (!$this->_helperPayment->isRatepayPayment($paymentMethod)) {
            return;
        }

        $hookDeliver = (bool) $this->_helperData->getRpConfigData($order, 'ratepay_general', 'hook_deliver', true, true);

        // Check whether backend operation is hooked
        if (!$hookDeliver) {
            return;
        }

        // Check whether backend operation is admitted
        if ($this->_helperData->getRpConfigData($order, $paymentMethod, 'status') == 1) {
            Mage::throwException($this->_helper->__('Processing failed'));
        }

        if ($this->_helperData->getRpConfigData($order, 'ratepay_general', 'deliver_event', true, true) == "invoice") {
            $this->sendRatepayDeliverCall($order, $invoice);
        } /*else {
            Mage::throwException($this->_helper->__('Processing failed'));
        }*/
    }

    /**
     * Call CONFIRMATION_DELIVER Method if delivery event is set
     *
     * @param Varien_Event_Observer $observer
     */

    public function sendRatepayDeliverCallOnDelivery(Varien_Event_Observer $observer) {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        // Check whether RatePAY method is selected
        if (!$this->_helperPayment->isRatepayPayment($paymentMethod)) {
            return;
        }

        $hookDeliver = (bool) $this->_helperData->getRpConfigData($order, 'ratepay_general', 'hook_deliver', true, true);

        // Check whether backend operation is hooked
        if (!$hookDeliver) {
            return;
        }

        // Check whether backend operation is admitted
        if ($this->_helperData->getRpConfigData($order, $paymentMethod, 'status') == 1) {
            Mage::throwException($this->_helper->__('Processing failed'));
        }

        if ($this->_helperData->getRpConfigData($order, 'ratepay_general', 'deliver_event', true, true) == "delivery") {
            $this->sendRatepayDeliverCall($order, $shipment);
        }
    }

    /**
     * Send a CONFIRMATION_DELIVER call with all shipped items
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception Delivery was not successful.
     */
    public function sendRatepayDeliverCall(Mage_Sales_Model_Order $order, $shippingOrInvoice)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        $sandbox = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'sandbox');
        $logging = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'logging');

        $request = Mage::getSingleton('ratepaypayment/libraryConnector', $sandbox);
        $head = $this->_helperMapping->getRequestHead($order);
        $content = $this->_helperMapping->getRequestContent($shippingOrInvoice, "CONFIRMATION_DELIVER");

        $response = $request->callConfirmationDeliver($head, $content);

        if ($logging) {
            Mage::getSingleton('ratepaypayment/logging')->log($response, $order);
        }

        if (!$response->isSuccessful()) {
            Mage::throwException($this->_helper->__('Delivery was not successful.') . " - " . $response->getReasonMessage());
        }

        $this->_helperPayment->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $shippingOrInvoice, true, 'CONFIRMATION_DELIVER SEND (capture)');

        $stateAfter = constant('Mage_Sales_Model_Order::' . $this->_helperData->getRpConfigData($order, 'ratepay_general', 'specificstate_after', true, true));
        $statusAfter = $this->_helperData->getRpConfigData($order, 'ratepay_general', 'specificstatus_after', true, true);

        $order->setState($stateAfter, $statusAfter, 'success')->save();
    }

    /**
     * Send a PAYMENT_CHANGE return call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCreditmemoCall(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

        // Check whether RatePAY method is selected
        if (!$this->_helperPayment->isRatepayPayment($paymentMethod)) {
            return;
        }

        $hookCreditmemo = (bool) $this->_helperData->getRpConfigData($order, 'ratepay_general', 'hook_creditmemo', true, true);

        // Check whether backend operation is hooked
        if (!$hookCreditmemo) {
            return;
        }

        // Setting an adjustment fee is not permitted with RatePAY Installment
        if ($creditmemo->getAdjustmentNegative() > 0 && strstr($paymentMethod, "_rate")) {
            Mage::throwException($this->_helper->__('Setting an adjustment fee is not permitted with RatePAY Installment'));
        }

        $sandbox = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'sandbox');
        $logging = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'logging');

        $request = Mage::getSingleton('ratepaypayment/libraryConnector', $sandbox);
        $head = $this->_helperMapping->getRequestHead($order);

        // Identify adjustments and set subtotal without adjustments as amount
        if ($creditmemo->getAdjustmentPositive() > 0 || $creditmemo->getAdjustmentNegative() > 0) {
            $amount = (float) $creditmemo->getSubtotalInclTax();
        } else {
            $amount = (float) $creditmemo->getGrandTotal();
        }

        $content = $this->_helperMapping->getRequestContent($creditmemo, "PAYMENT_CHANGE", null, $amount);

        $content = $this->_helperMapping->getRequestContent($creditmemo, "PAYMENT_CHANGE", null, $amount);

        // Check whether backend operation is admitted
        if ($this->_helperData->getRpConfigData($order, $paymentMethod, 'status') == 1) {
            Mage::throwException($this->_helper->__('Processing failed'));
        }

        // If any adjustment is set, a PAYMENT CHANGE credit call will be done
        if ($creditmemo->getAdjustmentPositive() > 0 || $creditmemo->getAdjustmentNegative() > 0) {
            $requestCredit = Mage::getSingleton('ratepaypayment/libraryConnector', $sandbox);
            $contentCredit = $this->_helperMapping->getRequestContent($order, "PAYMENT_CHANGE", $this->_helperMapping->addAdjustments($creditmemo));

            $responseCredit = $requestCredit->callPaymentChange($head, $contentCredit, 'credit');

            if ($logging) {
                Mage::getSingleton('ratepaypayment/logging')->log($responseCredit, $order);
            }

            if ($responseCredit->isSuccessful()) {
                $this->_helperPayment->addNewTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo, true, 'PAYMENT_CHANGE SEND (credit)');
            } else {
                Mage::throwException($this->_helper->__('Credit was not successful') . " - " . $responseCredit->getReasonMessage());
            }
        }

        // Call PAYMENT CHANGE return
        $responseReturn = $request->callPaymentChange($head, $content, 'return');

        if ($logging) {
            Mage::getSingleton('ratepaypayment/logging')->log($responseReturn, $order);
        }

        if ($responseReturn->isSuccessful()) {
            $this->_helperPayment->addNewTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo, true, 'PAYMENT_CHANGE SEND (return)');
        } else {
            Mage::throwException($this->_helper->__('Return was not successful') . " - " . $responseReturn->getReasonMessage());
        }
    }

    /**
     * Send a PAYMENT_CHANGE cancellation call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCancelCall(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();

        // Check whether RatePAY method is selected
        if (!$this->_helperPayment->isRatepayPayment($paymentMethod)) {
            return;
        }

        $hookCancel = (bool) $this->_helperData->getRpConfigData($order, 'ratepay_general', 'hook_cancel', true, true);

        // Check whether backend operation is hooked
        if (!$hookCancel) {
            return;
        }

        $sandbox = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'sandbox');
        $logging = (bool) $this->_helperData->getRpConfigData($order, $paymentMethod, 'logging');

        $request = Mage::getSingleton('ratepaypayment/libraryConnector', $sandbox);
        $head = $this->_helperMapping->getRequestHead($order);
        $content = $this->_helperMapping->getRequestContent($order, "PAYMENT_CHANGE", [], 0); // Set zero amount and empty basket. Works as (full) cancellation of all remaining items

        // Check whether backend operation is admitted
        if ($this->_helperData->getRpConfigData($order, $order->getPayment()->getMethod(), 'status') == 1) {
            Mage::throwException($this->_helper->__('Processing failed'));
        }

        $response = $request->callPaymentChange($head, $content, 'cancellation');
        if ($logging) {
            Mage::getSingleton('ratepaypayment/logging')->log($response, $order);
        }

        if (!$response->isSuccessful()) {
            Mage::throwException($this->_helper->__('Cancellation was not successful.') . " - " . $response->getReasonMessage());
        }
    }

    /**
     * Checks if reward points are added after installment plan is created
     *
     * @param Varien_Event_Observer $observer
     */
    public function rewardCheck(Varien_Event_Observer $observer)
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


        if ($order->getPayment()->getMethod() != 'ratepay_rate') {
            return;
        }

        if (Mage::app()->getStore()->isAdmin()) {
            $grandTotal = round(Mage::getModel('adminhtml/session_quote')->getQuote()->getGrandTotal(), 2);
        }else {
            $grandTotal = round(Mage::getModel('checkout/session')->getQuote()->getGrandTotal(), 2);
        }

        $rateAmount = round(Mage::getSingleton('ratepaypayment/session')->getRatepayRateAmount(), 2);

        if($rateAmount != $grandTotal){
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
            Mage::throwException($this->_helper->__('rate basket difference'));
        }
    }
}
