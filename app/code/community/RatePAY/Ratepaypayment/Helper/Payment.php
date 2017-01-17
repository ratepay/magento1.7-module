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

    /**
     * Retrieve the available products
     *
     * @param array $orderItems
     * @param array $creditmemoItems
     * @param array $cancelledItems
     * @param string $type
     * @param array $tempCreditmemoItems
     * @return array
     */
    public function getAvailableProducts(array $orderItems, array $data)
    {
        $items = array();
        foreach ($orderItems as $orderItem) {
            $tempArray = array();
            $tempArray['quantity'] = $orderItem['quantity'];
            $tempArray['unitPriceGross'] = $orderItem['unitPriceGross'];
            $tempArray['taxPercent'] = !isset($orderItem['taxPercent']) ? 0 : $orderItem['taxPercent'];
            $tempArray['articleNumber'] = $orderItem['articleNumber'];
            $tempArray['articleName'] = $orderItem['articleName'];
            $tempArray['discountId'] = $orderItem['discountId'];

            foreach ($data as $subtractItems) {
                $tempArray = $this->_calcProductArrays($tempArray, $subtractItems);
            }

            if ((int) $tempArray['quantity'] > 0) {
                $items[] = $tempArray;
            }
            elseif ($tempArray['articleNumber'] == 'REWARDPOINTS' && $tempArray['unitPriceGross'] < 0){
                $tempArray['quantity'] = 1;
                $items[] = $tempArray;
            }
        }

        if(isset($data['creditmemo'])) $this->_addAdjustment($items, $data['creditmemo']);
        if(isset($data['temp_creditmemo'])) $this->_addAdjustment($items, $data['temp_creditmemo']);

        return $items;
    }

    /**
     * Add adjustment item to the given item list
     *
     * @param array $items
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemoItems
     */
    private function _addAdjustment(array &$items, array $creditmemoItems)
    {
        foreach ($creditmemoItems as $creditmemoItem) {
            if (($creditmemoItem['articleNumber'] == 'adj-fee' || $creditmemoItem['articleNumber'] == 'adj-fee-new' || $creditmemoItem['articleNumber'] == 'adj-ref' || $creditmemoItem['articleNumber'] == 'adj-ref-new') && $creditmemoItem['quantity'] != 0) {
                array_push($items, $creditmemoItem);
            }
        }
    }

    /**
     * Retrieve the substracted product array
     *
     * @param array $tempArray
     * @param array $itemArray
     * @return array
     */
    private function _calcProductArrays(array $tempArray, array $itemArray)
    {
        //$condition = (strstr($tempArray['articleNumber'], 'DISCOUNT')) ? $tempArray['articleName'] : $tempArray['articleNumber'];
        $condition = $tempArray['articleNumber'];
        if (array_key_exists($condition, $itemArray)) {
            $tempArray['quantity'] = $tempArray['quantity'] - $itemArray[$condition]['quantity'];
        }
        if ($condition == 'REWARDPOINTS'){
            $tempArray['unitPriceGross'] = $tempArray['unitPriceGross'] - $itemArray[$condition]['unitPriceGross'];
        }
        return $tempArray;
    }

    /**
     * Retrieve the actual grand total of the order
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Invoice $tempCancelInvoice
     * @param Mage_Sales_Model_Order_Creditmemo $tempCreditmemo
     *
     * @return float
     */
    public function getShoppingBasketAmount(Mage_Sales_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $tempCreditmemo = null)
    {
        $grandTotal = Mage::app()->getStore()->roundPrice($order->getGrandTotal());

        if ($this->isOrderCanceled($order)) {
            $invoices = $order->getInvoiceCollection();
            $grandTotal = 0;
            foreach ($invoices as $invoice) {
                $grandTotal = $grandTotal + Mage::app()->getStore()->roundPrice($invoice->getGrandTotal());
            }
        }

        if (isset($tempCreditmemo)) {
            $grandTotal = $grandTotal - Mage::app()->getStore()->roundPrice($tempCreditmemo->getGrandTotal());
        }

        $creditmemos = $order->getCreditmemosCollection();
        foreach ($creditmemos as $creditmemo) {
            $grandTotal = $grandTotal - Mage::app()->getStore()->roundPrice($creditmemo->getGrandTotal());
        }



        return $grandTotal;
    }

    /**
     * Is one of the order items canceled
     *
     * @param Mage_Sales_Model_Order
     * @return boolean
     */
    public function isOrderCanceled(Mage_Sales_Model_Order $order)
    {
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getQtyCanceled() > 0) {
                return true;
            }
        }
        return false;
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

