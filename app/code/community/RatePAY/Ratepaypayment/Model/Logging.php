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

class RatePAY_Ratepaypayment_Model_Logging extends Mage_Core_Model_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        parent::_construct();
        $this->_init('ratepaypayment/logging');
    }

    /**
     * This method saves all RatePAY Requests and Responses in the database
     * 
     * @param \RatePAY\ModelBuilder $response
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote|null $quoteOrOrder
     * @param String|null $paymentMethod
     */
    public function log($response, $quoteOrOrder = null, $paymentMethod = null)
    {
        $requestXMLElement = $this->maskBankData($response->getRequestXmlElement());
        $responseXMLElement = $response->getResponseXmlElement();

        if ($quoteOrOrder instanceof Mage_Sales_Model_Order) {
            $orderNumber = $quoteOrOrder->getRealOrderId();
        } elseif ($quoteOrOrder instanceof Mage_Sales_Model_Quote) {
            $orderNumber = $quoteOrOrder->getReservedOrderId();
        } else {
            $orderNumber = "N/A";
        }

        if (!is_null($quoteOrOrder) && is_null($paymentMethod)) {
            $paymentMethod = $quoteOrOrder->getPayment()->getMethod();
        }
        
        if (!is_null($quoteOrOrder)) {
            $name = $quoteOrOrder->getBillingAddress()->getFirstname() . " " . $quoteOrOrder->getBillingAddress()->getLastname();
        } else {
            $name = "N/A";
        }

        $transaction = $response->getTransactionId();

        $this->setId(null)
            ->setOrderNumber($orderNumber)
            ->setTransactionId(!is_null($transaction) ? $response->getTransactionId() : "N/A")
            ->setPaymentMethod(!is_null($paymentMethod) ? strtoupper(Mage::helper('ratepaypayment/payment')->convertMethodToProduct($paymentMethod)) : "N/A")
            ->setPaymentType($requestXMLElement->head->{'operation'})
            ->setPaymentSubtype(isset($requestXMLElement->head->operation->attributes()->subtype) ? strtoupper((string) $requestXMLElement->head->operation->attributes()->subtype) : "N/A")
            ->setResult($response->getResultMessage())
            ->setRequest($requestXMLElement->asXML())
            ->setResponse($responseXMLElement->asXML())
            ->setResultCode($response->getResultCode())
            ->setName($name)
            ->setReason($response->getReasonMessage())
            ->save();
    }

    /**
     *
     *
     * @param SimpleXMLElement $request
     * @return SimpleXMLElement
     */
    private function maskBankData($request)
    {
        if (isset($request->content->customer->{'bank-account'})) {
            $request->content->customer->{'bank-account'}->owner = '(...)';
            if (isset($request->content->customer->{'bank-account'}->iban)) {
                $request->content->customer->{'bank-account'}->iban = '(...)';
            } else {
                $request->content->customer->{'bank-account'}->{'bank-account-number'} = '(...)';
                $request->content->customer->{'bank-account'}->{'bank-code'} = '(...)';
            }
        }

        return $request;
    }
}
