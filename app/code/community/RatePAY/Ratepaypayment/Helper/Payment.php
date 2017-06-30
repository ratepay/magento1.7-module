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

class RatePAY_Ratepaypayment_Helper_Payment extends Mage_Core_Helper_Abstract
{

    private $_productsToMethods = array(
        "invoice" => "ratepay_rechnung",
        "installment" => "ratepay_rate",
        "elv" => "ratepay_directdebit",
        "prepayment" => "ratepay_vorkasse",
        "PQ" => "ratepay_ibs",
        "ratepay_rechnung" => "invoice",
        "ratepay_rate" => "installment",
        "ratepay_rate0" => "installment 0%",
        "ratepay_directdebit" => "elv",
        "ratepay_vorkasse" => "prepayment",
        "ratepay_ibs" => "PQ");

    /**
     * Is ratepay payment
     *
     * @param string $code
     * @return boolean
     */
    public function isRatepayPayment($code)
    {
        switch ($code) {
            case 'ratepay_rechnung':
                return true;
            case 'ratepay_rate':
                return true;
            case 'ratepay_rate0':
                return true;
            case 'ratepay_directdebit':
                return true;
        }
        return false;
    }

    /**
     * Magento $payment->addTransaction(...) needs the transaction_id = id from the transaction entry or string!
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $type
     * @param Mage_Sales_Model_Abstract $salesDocument
     * @param bool $failsafe
     * @param string $message
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    public function addNewTransaction(Mage_Sales_Model_Order_Payment $payment, $type, $salesDocument = null, $failsafe = false, $message = false)
    {
        $transaction = null;

        try {
            $addInformation = $payment->getAdditionalInformation();

            $payment->setTransactionId($addInformation['transactionId']);
            $transaction = $payment->addTransaction($type, $salesDocument, $failsafe, $message);
            $transaction->save();

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $transaction;
    }

    public function convertInvoiceToShipment(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $convertOrder = new Mage_Sales_Model_Convert_Order();
        $order        = $invoice->getOrder();
        $shipment     = $convertOrder->toShipment($order);
        $items        = $invoice->getAllItems();
        $totalQty     = 0;

        if (count($items)) {
            foreach ($items as $eachItem) {
                if ($eachItem->getRowTotal() > 0) {
                    $_eachShippedItem = $convertOrder->itemToShipmentItem($eachItem->getOrderItem());
                    $_eachShippedItem->setQty($eachItem->getQty());
                    $shipment->addItem($_eachShippedItem);
                    $totalQty += $eachItem->getQty();

                    unset($_eachShippedItem);
                }
            }

            $shipment->setTotalQty($totalQty);
        }

        Mage::getModel('core/resource_transaction')->addObject($shipment)->addObject($shipment->getOrder())->save();
        $order->save();
    }

    public function convertMethodToProduct($id)
    {
        return $this->_productsToMethods[$id];
    }
}

