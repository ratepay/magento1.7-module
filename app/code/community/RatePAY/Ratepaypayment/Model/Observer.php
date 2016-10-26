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

    /**
     * Starts the PAYMENT QUERY if activated and saves the allowed payment methods in the RatePAY session
     *
     * @param Varien_Event_Observer $observer
     */

    public function paymentQuery(Varien_Event_Observer $observer)
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
                if (!$this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'sandbox')) {
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
            $skuInvoice = $this->getHelper()->getRpConfigData($quote, 'ratepay_rechnung', 'payment_fee');
            $skuElv = $this->getHelper()->getRpConfigData($quote, 'ratepay_directdebit', 'payment_fee');
            $skuRate = $this->getHelper()->getRpConfigData($quote, 'ratepay_directdebit', 'payment_fee');
            $paymentMethod = $observer->getEvent()->getData('input')->getData('method');
            $sku = $this->getHelper()->getRpConfigData($quote, $paymentMethod, 'payment_fee');
            if (Mage::helper('ratepaypayment/payment')->isRatepayPayment($paymentMethod)) {
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
        $storeId = Mage::app()->getStore()->getStoreId();

        if ($orderIds = $observer->getEvent()->getOrderIds()) { // frontend event
            $orderId = current($orderIds);
            if (!$orderId) {
                return;
            }
            $order = Mage::getModel('sales/order')->load($orderId);
        } else { // adminhtml event
            $order = $observer->getEvent()->getOrder();
        }

        /*if (Mage::getSingleton('ratepaypayment/session')->getBankdataAfter()) {
            $piEncryption = new Pi_Util_Encryption_MagentoEncryption();
            $bankdata = array(
                'owner' => $data[$code . '_account_holder'],
                'accountnumber' => $data[$code . '_account_number'],
                'bankcode' => $data[$code . '_bank_code_number'],
                'bankname' => $data[$code . '_bank_name']
            );
            Mage::getSingleton('ratepaypayment/session')->setBankdataAfter(false);
            $piEncryption->saveBankdata($order->getCustomerId(), $bankdata);
        }*/

        if (Mage_Sales_Model_Order::STATE_PROCESSING == $order->getState()) {
            $paymentMethod = $order->getPayment()->getMethod();
            if (Mage::helper('ratepaypayment/payment')->isRatepayPayment($paymentMethod)) {
                // save entry in sales_payment_transaction
                $message = 'PAYMENT_REQUEST SEND (authorize)';
                $payment = $order->getPayment();
                if (strstr($payment->getMethod(), "ratepay_rate")) {
                    
                    $payment->setAdditionalInformation('Rate Total Amount', Mage::getSingleton('ratepaypayment/session')->getRatepayRateTotalAmount());
                    $payment->setAdditionalInformation('Rate Total Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'TotalAmount'}());
                    
                    $payment->setAdditionalInformation('Rate Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'Amount'}());
                    $payment->setAdditionalInformation('Rate Interest Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'InterestRate'}());
                    $payment->setAdditionalInformation('Rate Interest Amount', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'InterestAmount'}());
                    $payment->setAdditionalInformation('Rate Service Charge', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'ServiceCharge'}());
                    $payment->setAdditionalInformation('Rate Annual Percentage Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'AnnualPercentageRate'}());
                    $payment->setAdditionalInformation('Rate Monthly Debit Interest', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'MonthlyDebitInterest'}());
                    $payment->setAdditionalInformation('Rate Number of Rates Full', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRatesFull'}());
                    $payment->setAdditionalInformation('Rate Number of Rates', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRates'}());
                    $payment->setAdditionalInformation('Rate Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'Rate'}());
                    $payment->setAdditionalInformation('Rate Last Rate', Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'LastRate'}());
                    $payment->setAdditionalInformation('Debit Select', Mage::getSingleton('ratepaypayment/session')->getRatepayPaymentFirstDay());

                    // unset session installment information
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'TotalAmount'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'Amount'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'InterestRate'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'InterestAmount'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'ServiceCharge'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'AnnualPercentageRate'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'MonthlyDebitInterest'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRatesFull'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRates'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'Rate'}();
                    Mage::getSingleton('ratepaypayment/session')->{'uns' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'LastRate'}();
                    Mage::getSingleton('ratepaypayment/session')->unsRatepayPaymentFirstDay();
                }

                Mage::helper('ratepaypayment/payment')->addNewTransaction($payment, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, false, $message);

                $stateBefore = $this->getHelper()->getRpConfigData($order, 'ratepay_general', 'specificstate_before', true, true);
                $statusBefore = $this->getHelper()->getRpConfigData($order, 'ratepay_general', 'specificstatus_before', true, true);

                $order->setState(constant('Mage_Sales_Model_Order::' . $stateBefore), $statusBefore, 'success')->save();
            }
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
        if ($this->getHelper()->getRpConfigData($order, 'ratepay_general', 'deliver_event', true, true) == "invoice") {
            $this->sendRatepayDeliverCall($order, $invoice);
        }
    }

    /**
     * Call CONFIRMATION_DELIVER Method if delivery event is set
     *
     * @param Varien_Event_Observer $observer
     */

    public function sendRatepayDeliverCallOnDelivery(Varien_Event_Observer $observer) {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        if ($this->getHelper()->getRpConfigData($order, 'ratepay_general', 'deliver_event', true, true) == "delivery") {
            $this->sendRatepayDeliverCall($order, $shipment);
        }
    }

    /**
     * Send a CONFIRMATION_DELIVER call with all shipped items
     *
     * @param Varien_Event_Observer $observer
     * @throws Exception Delivery was not successful.
     */
    public function sendRatepayDeliverCall($order, $shippingOrInvoice)
    {
        $client = Mage::getSingleton('ratepaypayment/request');
        $helper = Mage::helper('ratepaypayment');
        $mappingHelper = Mage::helper('ratepaypayment/mapping');
        $dataHelper = Mage::helper('ratepaypayment/data');
        $paymentHelper = Mage::helper('ratepaypayment/payment');
        if ($paymentHelper->isRatepayPayment($order->getPayment()->getMethod()) && (bool) $dataHelper->getRpConfigData($order, 'ratepay_general', 'hook_deliver', true, true)) {
            $result = $client->callConfirmationDeliver($mappingHelper->getRequestHead($order), $mappingHelper->getRequestBasket($shippingOrInvoice), $mappingHelper->getLoggingInfo($order)); // , '', $paymentHelper->getAllInvoiceItems($order)

            if (!$result) {
                Mage::throwException(Mage::helper('ratepaypayment')->__('Delivery was not successful.'));
            }

            Mage::helper('ratepaypayment/payment')->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $shippingOrInvoice, true, 'CONFIRMATION_DELIVER SEND (capture)');

            $stateAfter = constant('Mage_Sales_Model_Order::' . $dataHelper->getRpConfigData($order, 'ratepay_general', 'specificstate_after', true, true));
            $statusAfter = $dataHelper->getRpConfigData($order, 'ratepay_general', 'specificstatus_after', true, true);

            $order->setState($stateAfter, $statusAfter, 'success')->save();
        }
    }

    /**
     * Send a PAYMENT_CHANGE (full-return, partial-return, credit) call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCreditmemoCall(Varien_Event_Observer $observer)
    {
        $client = Mage::getSingleton('ratepaypayment/request');
        $mappingHelper = Mage::helper('ratepaypayment/mapping');
        $paymentHelper = Mage::helper('ratepaypayment/payment');
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::helper('ratepaypayment/payment')->isRatepayPayment($order->getPayment()->getMethod()) && (bool) $this->getHelper()->getRpConfigData($order, 'ratepay_general', 'hook_creditmemo', true, true)) {

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
            $loggingInfo = $mappingHelper->getLoggingInfo($order);

            if($this->_getItemCount($creditmemo) > 0){
                $headInfo = $mappingHelper->getRequestHead($order, 'return');
                if(!$client->callPaymentChange($headInfo, $basketInfo, $loggingInfo)){
                    Mage::throwException(Mage::helper('ratepaypayment')->__('Return was not successful.'));
                }
                $paymentHelper->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo, true, 'PAYMENT_CHANGE SEND (return)');
            }
            if ($creditmemo->getAdjustmentPositive() > 0) {
                $headInfo = $mappingHelper->getRequestHead($order, 'credit');
                if(!$client->callPaymentChange($headInfo, $basketInfo, $loggingInfo)) {
                    Mage::throwException(Mage::helper('ratepaypayment')->__('Voucher was not successful.'));
                }
                $paymentHelper->addNewTransaction($order->getPayment(), Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND, $creditmemo, true, 'PAYMENT_CHANGE SEND (credit)');
            }

            
        }
    }

    /**
     * Send a PAYMENT_CHANGE (partial-cancellation) call with all available item
     *
     * @param Varien_Event_Observer $observer
     */
    public function sendRatepayCancelCall(Varien_Event_Observer $observer)
    {
        $client = Mage::getSingleton('ratepaypayment/request');
        $mappingHelper = Mage::helper('ratepaypayment/mapping');
        $order = $observer->getEvent()->getOrder();
        if (Mage::helper('ratepaypayment/payment')->isRatepayPayment($order->getPayment()->getMethod()) && (bool) $this->getHelper()->getRpConfigData($order, 'ratepay_general', 'hook_creditmemo', true, true)) {
            $amount = 0;
            $items = array();

            $basketInfo   = $mappingHelper->getRequestBasket($order, $amount, $items);
            $headInfo     = $mappingHelper->getRequestHead($order, 'cancellation');
            $loggingInfo  = $mappingHelper->getLoggingInfo($order);

            $result = $client->callPaymentChange($headInfo, $basketInfo, $loggingInfo);

            if (!$result) {
                Mage::throwException(Mage::helper('ratepaypayment')->__('Cancellation was not successful.'));
            }
        }
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

    /**
     * Returns the payment method helper
     *
     * @return RatePAY_Ratepaypayment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('ratepaypayment');
    }

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
        $paymentMethod = $order->getPayment()->getMethod();
        $grandTotal = round(Mage::getModel('checkout/session')->getQuote()->getGrandTotal(),1);
        $rateAmount = Mage::getSingleton('ratepaypayment/session')->getRatepayRateAmount();
        if($paymentMethod == 'ratepay_rate' && $rateAmount != $grandTotal){
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
            Mage::throwException(Mage::helper('ratepaypayment')->__('rate basket difference'));
        }
    }
}
