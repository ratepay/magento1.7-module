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
     * @param array $loggingInfo
     * @param SimpleXMLElement|null $request
     * @param SimpleXMLElement|null $response
     */
    public function log($loggingInfo, $request, $response)
    {
        $responseXML = '';
        $result = "Service offline.";
        $resultCode = "Service offline.";
        $reasonText = '';
        
        if (isset($request->content->customer->{'bank-account'})) {
            $request->content->customer->{'bank-account'}->owner = '(hidden)';
            if (isset($request->content->customer->{'bank-account'}->{'iban'})) {
                $request->content->customer->{'bank-account'}->{'iban'} = '(hidden)';
                $request->content->customer->{'bank-account'}->{'bic'} = '(hidden)';
            } else {
                $request->content->customer->{'bank-account'}->{'bank-account-number'} = '(hidden)';
                $request->content->customer->{'bank-account'}->{'bank-code'} = '(hidden)';
            }
            $request->content->customer->{'bank-account'}->{'bank-name'} = '(hidden)';
        }

        if ($response != null && isset($response) && $response->asXML() != '') {
            $result = (string) $response->head->processing->result;
            $resultCode = (string) $response->head->processing->result->attributes()->code;
            $responseXML = $response->asXML();
            $reasonText = (string) $response->head->processing->reason;
            if($loggingInfo['requestType'] == 'PAYMENT_INIT') {
                $loggingInfo['transactionId'] = (string)$response->head->{'transaction-id'};
            }
        }

        $this->setId(null)
            ->setOrderNumber(!empty($loggingInfo['orderId']) ? $loggingInfo['orderId'] : 'n/a')
            ->setTransactionId(!empty($loggingInfo['transactionId']) ? $loggingInfo['transactionId'] : 'n/a')
            ->setPaymentMethod(!empty($loggingInfo['paymentMethod']) ? $loggingInfo['paymentMethod'] : 'n/a')
            ->setPaymentType(!empty($loggingInfo['requestType']) ? $loggingInfo['requestType'] : 'n/a')
            ->setPaymentSubtype(!empty($loggingInfo['requestSubType']) ? $loggingInfo['requestSubType'] : 'n/a')
            ->setResult($result)
            ->setRequest($request->asXML())
            ->setRequest($request->asXML())
            ->setResponse($responseXML)
            ->setResultCode($resultCode)
            ->setName($loggingInfo['firstName'] . " " . $loggingInfo['lastName'])
            ->setReason($reasonText)
            ->save();
    }
}