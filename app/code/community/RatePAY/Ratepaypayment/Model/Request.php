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

class RatePAY_Ratepaypayment_Model_Request extends Mage_Core_Model_Abstract
{

    /**
     * Xml response instance
     * 
     * @var SimpleXMLElement
     */
    private $response = null;
    
    /**
     * Xml request instance
     * 
     * @var SimpleXMLElement
     */
    private $request = null;
    
    /**
     * Error string
     * 
     * @var string
     */
    private $error = '';

    /**
     * Returns the Request
     *
     * @return SimpleXML
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Validates the Response
     *
     * @param string $requestType
     * @return boolean|array
     */
    public function validateResponse($requestType = '')
    {
        $statusCode = '';
        $resultCode = '';
        if($this->response != null) {
            $statusCode = (string) $this->response->head->processing->status->attributes()->code;
            $resultCode = (string) $this->response->head->processing->result->attributes()->code;
            $reasonCode = (string) $this->response->head->processing->reason->attributes()->code;
        }
        switch($requestType) {
            case 'PAYMENT_INIT':
                if($statusCode == "OK" &&  $resultCode == "350") {
                    $result = array();
                    $result['transactionId'] = (string)$this->response->head->{'transaction-id'};
                    $result['transactionShortId'] = (string)$this->response->head->{'transaction-short-id'};
                    $this->error = '';
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'PAYMENT_QUERY':
                if($statusCode == "OK" && $resultCode == "402") {
                    $result = array();
                    $result['products'] = (array) $this->response->content->products;
                    $this->error = '';
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'PAYMENT_REQUEST':
                if($statusCode == "OK" && $resultCode == "402") {
                    $result = array();
                    $result['descriptor'] = (string) $this->response->content->payment->descriptor;
                    $result['address'] = (array) $this->response->content->customer->addresses->address;
                    $this->error = '';
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'PAYMENT_CONFIRM':
                if($statusCode == "OK" && $resultCode == "400") {
                    $this->error = '';
                    return true;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'CONFIRMATION_DELIVER':
                if($statusCode == "OK" && $resultCode == "404") {
                    $this->error = '';
                    return true;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'PAYMENT_CHANGE':
                if($statusCode == "OK" && $resultCode == "403") {
                    $this->error = '';
                    return true;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'CONFIGURATION_REQUEST':
                if($statusCode == "OK" && $resultCode == "500") {
                    $result = array();
                    $result['interestrateMin'] = (string) $this->response->content->{'installment-configuration-result'}->{'interestrate-min'};
                    $result['interestrateDefault'] = (string) $this->response->content->{'installment-configuration-result'}->{'interestrate-default'};
                    $result['interestrateMax'] = (string) $this->response->content->{'installment-configuration-result'}->{'interestrate-max'};
                    $result['monthNumberMin'] = (string) $this->response->content->{'installment-configuration-result'}->{'month-number-min'};
                    $result['monthNumberMax'] = (string) $this->response->content->{'installment-configuration-result'}->{'month-number-max'};
                    $result['monthLongrun'] = (string) $this->response->content->{'installment-configuration-result'}->{'month-longrun'};
                    $result['monthAllowed'] = (string) $this->response->content->{'installment-configuration-result'}->{'month-allowed'};
                    $result['paymentFirstday'] = (string) $this->response->content->{'installment-configuration-result'}->{'payment-firstday'};
                    $result['paymentAmount'] = (string) $this->response->content->{'installment-configuration-result'}->{'payment-amount'};
                    $result['paymentLastrate'] = (string) $this->response->content->{'installment-configuration-result'}->{'payment-lastrate'};
                    $result['rateMinNormal'] = (string) $this->response->content->{'installment-configuration-result'}->{'rate-min-normal'};
                    $result['rateMinLongrun'] = (string) $this->response->content->{'installment-configuration-result'}->{'rate-min-longrun'};
                    $result['serviceCharge'] = (string) $this->response->content->{'installment-configuration-result'}->{'service-charge'};
                    $this->error = '';
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'PROFILE_REQUEST':
                if($statusCode == "OK" && $resultCode == "500") {
                    $resultMasterData = (array) $this->response->content->{'master-data'};
                    $resultInstallmentConfiguration = (array) $this->response->content->{'installment-configuration-result'};
                    $result['merchant_config'] = $resultMasterData;
                    $result['installment_config'] = $resultInstallmentConfiguration;
                    $this->error = '';
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
                break;
            case 'CALCULATION_REQUEST':
                $successCodes = array('603', '671', '688', '689', '695', '696', '697', '698', '699');
                if($statusCode == "OK" && in_array($reasonCode, $successCodes) && $resultCode == "502") {
                    $result = array();
                    $result['totalAmount'] = (string) $this->response->content->{'installment-calculation-result'}->{'total-amount'};
                    $result['amount'] = (string) $this->response->content->{'installment-calculation-result'}->{'amount'};
                    $result['interestRate'] = (string) $this->response->content->{'installment-calculation-result'}->{'interest-rate'};
                    $result['interestAmount'] = (string) $this->response->content->{'installment-calculation-result'}->{'interest-amount'};
                    $result['serviceCharge'] = (string) $this->response->content->{'installment-calculation-result'}->{'service-charge'};
                    $result['annualPercentageRate'] = (string) $this->response->content->{'installment-calculation-result'}->{'annual-percentage-rate'};
                    $result['monthlyDebitInterest'] = (string) $this->response->content->{'installment-calculation-result'}->{'monthly-debit-interest'};
                    $result['numberOfRatesFull'] = (string) $this->response->content->{'installment-calculation-result'}->{'number-of-rates'};
                    $result['numberOfRates'] = $result['numberOfRatesFull']-1;
                    $result['rate'] = (string) $this->response->content->{'installment-calculation-result'}->{'rate'};
                    $result['lastRate'] = (string) $this->response->content->{'installment-calculation-result'}->{'last-rate'};
                    $result['debitSelect'] = (string) $this->response->content->{'installment-calculation-result'}->{'payment-firstday'};
                    $result['code'] = $reasonCode;
                    return $result;
                } else {
                    $this->error = 'FAIL';
                    return false;
                }
            default:
                $this->error = 'FAIL';
                return false;
        }
    }

    /**
     * Calls the PAYMENT_INIT
     *
     * @param array $head
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callPaymentInit($head, $loggingInfo)
    {
        $this->constructXml();
        $requestType = "PAYMENT_INIT";
        $this->setRequestHead($requestType, $head);
        $loggingInfo['requestType'] = $requestType;
        $loggingInfo['requestSubType'] = 'n/a';
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the PAYMENT_QUERY
     *
     * @param array $headInfo
     * @param string $subType
     * @param array $customerInfo
     * @param array $itemInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callPaymentQuery($headInfo, $subType, $customerInfo, $itemInfo, $loggingInfo)
    {
        $this->constructXml();
        $requestType = "PAYMENT_QUERY";
        $this->setRequestHead($requestType,$headInfo);
        $this->setRequestContent($customerInfo,$itemInfo, false, $requestType);
        $loggingInfo['requestType'] = $requestType;
        $loggingInfo['requestSubType'] = $subType;
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the PAYMENT_REQUEST
     *
     * @param array $headInfo
     * @param array $customerInfo
     * @param array $itemInfo
     * @param array $paymentInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callPaymentRequest($headInfo, $customerInfo, $itemInfo, $paymentInfo, $loggingInfo)
    {
        $this->constructXml();
        $requestType = "PAYMENT_REQUEST";
        $this->setRequestHead($requestType, $headInfo);
        $this->setRequestContent($customerInfo,$itemInfo, $paymentInfo, $requestType);
        $loggingInfo['requestType'] = $requestType;
        $loggingInfo['requestSubType'] = 'n/a';
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the PAYMENT_CONFIRM
     *
     * @param array $headInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callPaymentConfirm($headInfo, $loggingInfo)
    {
        $this->constructXml();
        $requestType = "PAYMENT_CONFIRM";
        $this->setRequestHead($requestType, $headInfo);
        $loggingInfo['requestType'] = $requestType;
        $loggingInfo['requestSubType'] = 'n/a';
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the CONFIRMATION_DELIVER
     *
     * @param array $headInfo
     * @param array $itemInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callConfirmationDeliver($headInfo, $itemInfo ,$loggingInfo)
    {
        $this->constructXml();
        $requestType = "CONFIRMATION_DELIVER";
        $this->setRequestHead($requestType, $headInfo);
        $this->setRequestContent(array(), $itemInfo, $headInfo, $requestType);
        $loggingInfo['requestType'] = $requestType;;
        $loggingInfo['requestSubType'] = 'n/a';
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the PAYMENT_CHANGE
     *
     * @param array $headInfo
     * @param array $customerInfo
     * @param array $itemInfo
     * @param array $paymentInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callPaymentChange($headInfo,$customerInfo,$itemInfo, $paymentInfo, $loggingInfo)
    {
        $this->constructXml();
        $requestType = "PAYMENT_CHANGE";
        $this->setRequestHead($requestType, $headInfo);
        $this->setRequestContent($customerInfo, $itemInfo, $paymentInfo);
        $loggingInfo['requestType'] = $requestType;
        $loggingInfo['requestSubType'] = $headInfo['subtype'];
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the CONFIGURATION_REQUEST
     *
     * @param array $headInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callConfigurationRequest($headInfo,$loggingInfo)
    {
        $this->constructXml();
        $requestType = "CONFIGURATION_REQUEST";
        $this->setRequestHead($requestType, $headInfo);
        $loggingInfo['requestType'] = $requestType;
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the PROFILE_REQUEST
     *
     * @param array $headInfo
     * @param array $loggingInfo
     * @return boolean|array
     */
    public function callProfileRequest($headInfo,$loggingInfo)
    {
        $this->constructXml();
        $requestType = "PROFILE_REQUEST";
        $this->setRequestHead($requestType, $headInfo);
        $loggingInfo['requestType'] = $requestType;
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Calls the CALCULATION_REQUEST
     *
     * @param array $headInfo
     * @param array $calculationInfo
     * @return boolean|array
     */
    public function callCalculationRequest($headInfo,$loggingInfo,$calculationInfo)
    {
        $this->constructXml();
        $requestType = "CALCULATION_REQUEST";
        $this->setRequestHead($requestType, $headInfo);
        $this->setRatepayContentCalculation($calculationInfo);
        $loggingInfo['requestType'] = $requestType;
        $this->sendXmlRequest($loggingInfo);
        return $this->validateResponse($requestType);
    }

    /**
     * Sets the Head Tag with all Informations based on, of which type the Request will be
     *
     * @param string $operationInfo
     * @param array $headInfo
     */
    private function setRequestHead($operationInfo, $headInfo)
    {
        $head = $this->request->addChild('head');
        
        $head->addChild('system-id', Mage::helper('core/http')->getServerAddr(false));
        if($operationInfo != 'PAYMENT_INIT') {
            if($headInfo['transactionId'] != '') {
                $head->addChild('transaction-id', $headInfo['transactionId']);
            }
            if($headInfo['transactionShortId'] != '') {
                $head->addChild('transaction-short-id', $headInfo['transactionShortId']);
            }
        }

        $operation = $head->addChild('operation', $operationInfo);
        if(isset($headInfo['subtype']) && $headInfo['subtype'] != '') {
            $operation->addAttribute('subtype', $headInfo['subtype']);
        }

        $credential = $head->addChild('credential');
        $credential->addChild('profile-id', $headInfo['profileId']);
        $credential->addChild('securitycode', $headInfo['securityCode']);

        if ($operationInfo != 'PAYMENT_INIT') {
            if($headInfo['orderId'] != '') {
                $external = $head->addChild('external');
                $external->addChild('order-id', $headInfo['orderId']);
                if ($headInfo['customerId'] != '' &&
                    ($operationInfo == 'PAYMENT_REQUEST' || $operationInfo == 'PAYMENT_QUERY')) {
                    $external->addChild('merchant-consumer-id', $headInfo['customerId']);
                }
            }
        }

        if ($operationInfo == 'PAYMENT_REQUEST' || $operationInfo == 'PAYMENT_QUERY') {
            $this->setRatepayHeadCustomerDevice($head);
        }
        
        $this->_setRequestVersions($head);
    }

    /**
     * Sets the Customer Device Tag to the Head Tag with all Informations to the Request
     *
     * @param SimpleXMLElement $head
     */
    private function setRatepayHeadCustomerDevice($head)
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $DeviceIdentSite = Mage::getStoreConfig("payment/ratepay_general/device_ident_id", $storeId);
        $DeviceIdentToken = Mage::getSingleton('ratepaypayment/session')->getDeviceIdentToken();

        if (!empty($DeviceIdentToken) && $DeviceIdentSite) {
            $customerDevice = $head->addChild('customer-device');

            $customerDevice->addChild('device-site', $DeviceIdentSite);
            $customerDevice->addChild('device-token', $DeviceIdentToken);
        }
    }

    /**
     * Sets the Content Tag with all Informations to the Request
     *
     * @param array $customerInfo
     * @param array $itemInfo
     * @param array $paymentInfo
     * @param string $requestInfo
     */
    private function setRequestContent($customerInfo, $itemInfo, $paymentInfo = '', $requestInfo = '')
    {
        $content = $this->request->addChild('content');
        if ($requestInfo == 'PAYMENT_REQUEST' || $requestInfo == 'PAYMENT_QUERY') {
            $this->setRatepayContentCustomer($content, $customerInfo);
        }

        $this->setRatepayContentBasket($content, $itemInfo);
        
        if ($requestInfo != 'CONFIRMATION_DELIVER' && $requestInfo != 'PAYMENT_QUERY') {
            $this->setRatepayContentPayment($content,$paymentInfo);
        }
    }
    
    /**
     * Set the shop version, the shop edition and the module version for the request
     * 
     * @param SimpleXMLElement $head 
     */
    private function _setRequestVersions($head)
    {
        $meta = $head->addChild('meta');
        $systems = $meta->addChild('systems');
        $system = $systems->addChild('system');
        $system->addAttribute('name', 'Magento_' . Mage::helper('ratepaypayment')->getEdition());
        $system->addAttribute(
            'version',  
            Mage::getVersion() . '_' . (string) Mage::getConfig()->getNode()->modules->RatePAY_Ratepaypayment->version
        );
    }

    /**
     * Sets the customer tag to the content tag with all Informations to the Request
     *
     * @param SimpleXMLElement $content
     * @param array $customerInfo
     */
    private function setRatepayContentCustomer($content, $customerInfo)
    {
        $customer = $content->addChild('customer');

        $customer->addCDataChild('first-name', $customerInfo['firstName']);
        $customer->addCDataChild('last-name', $customerInfo['lastName']);
        $customer->addChild('gender', $customerInfo['gender']);
        if(empty($customerInfo['company'])) {
            $customer->addChild('date-of-birth', $customerInfo['dob']);
        }
        $customer->addChild('ip-address', $customerInfo['ip']);
        if(!empty($customerInfo['company'])) {
            $customer->addCDataChild('company-name', $customerInfo['company']);
            $customer->addChild('vat-id', $customerInfo['vatId']);
        }

        $contacts = $customer->addChild('contacts');
        $contacts->addChild('email', $customerInfo['contacts']['email']);
        $phone = $contacts->addChild('phone');
        $phone->addChild('direct-dial', $customerInfo['contacts']['phone']);

        if(isset($customer['contacts']['fax'])) {
            $fax = $contacts->addChild('fax');
            $fax->addChild('direct-dial', $customerInfo['contacts']['fax']);
        }

        $addresses = $customer->addChild('addresses');

        $billingAddress = $addresses->addChild('address');
        $billingAddress->addAttribute('type', 'BILLING');
        $billingAddress->addCDataChild('street', $customerInfo['billing']['street']);
        $billingAddress->addChild('zip-code', $customerInfo['billing']['zipCode']);
        $billingAddress->addCDataChild('city', $customerInfo['billing']['city']);
        $billingAddress->addChild('country-code', $customerInfo['billing']['countryId']);

        $shippingAddress = $addresses->addChild('address');
        $shippingAddress->addAttribute('type', 'DELIVERY');
        $shippingAddress->addCDataChild('first-name', $customerInfo['shipping']['firstName']);
        $shippingAddress->addCDataChild('last-name', $customerInfo['shipping']['lastName']);
        $shippingAddress->addCDataChild('street', $customerInfo['shipping']['street']);
        $shippingAddress->addChild('zip-code', $customerInfo['shipping']['zipCode']);
        $shippingAddress->addCDataChild('city', $customerInfo['shipping']['city']);
        $shippingAddress->addChild('country-code', $customerInfo['shipping']['countryId']);
        if(!empty($customerInfo['shipping']['company'])) {
            $shippingAddress->addCDataChild('company', $customerInfo['shipping']['company']);
        }
        
        if (Mage::getSingleton('core/session')->getDirectDebitFlag()) {
            $data = Mage::helper('ratepaypayment')->getBankData();
            $bankData = $customer->addChild('bank-account');
            $bankData->addChild('owner', $data['owner']);
            if(!empty($data['accountnumber']) && empty($data['iban'])) {
                $bankData->addChild('bank-account-number', $data['accountnumber']);
                $bankData->addChild('bank-code', $data['bankcode']);
            }
            if(!empty($data['iban'])) {
                $bankData->addChild('iban', $data['iban']);
                if(!empty($data['bic'])) {
                    $bankData->addChild('bic-swift', $data['bic']);
                }
            }
        }
        
        $customer->addChild('nationality', $customerInfo['nationality']);
        $customer->addChild('customer-allow-credit-inquiry', 'yes');
    }

    /**
     * Sets the shopping-basket tag to the content tag with all Informations to the Request
     *
     * @param SimpleXMLElement $content
     * @param array $basketInfo
     */
    private function setRatepayContentBasket($content, $basketInfo)
    {
        $shoppingBasket = $content->addChild('shopping-basket');
        $shoppingBasket->addAttribute('amount', number_format($basketInfo['amount'], 2, ".", ""));
        $shoppingBasket->addAttribute('currency', $basketInfo['currency']);

        $items = $shoppingBasket->addChild('items');

        foreach ($basketInfo['items'] as $itemInfo) {
            $item = $items->addCDataChild('item', $this->removeSpecialChars($itemInfo['articleName']));
            $item->addAttribute('article-number', $this->removeSpecialChars($itemInfo['articleNumber']));
            $item->addAttribute('quantity', number_format($itemInfo['quantity'], 0, '.', ''));
            $item->addAttribute('unit-price-gross', number_format(round($itemInfo['unitPriceGross'],2), 2, ".", ""));
            $item->addAttribute('tax-rate', number_format($itemInfo['taxPercent'], 0, ".", ""));
        }
    }

    /**
     * Sets the payment tag to the content tag with all Informations to the Request
     *
     * @param SimpleXMLElement $content
     * @param array $paymentInfo
     */
    private function setRatepayContentPayment($content, $paymentInfo)
    {
        $payment = $content->addChild('payment');
        $payment->addAttribute('method', $paymentInfo['method']);
        $payment->addAttribute('currency', $paymentInfo['currency']);
        if ($this->getRequest()->head->operation == 'PAYMENT_REQUEST') {
            $payment->addChild('amount', number_format($paymentInfo['amount'], 2, ".", ""));
            if(isset($paymentInfo['debitType'])) {
                $payment->addChild('debit-pay-type', $paymentInfo['debitType']);
                $installment = $payment->addChild('installment-details');
                if(isset($paymentInfo['installmentNumber'])) {
                    $installment->addChild('installment-number', $paymentInfo['installmentNumber']);
                    $installment->addChild('installment-amount', $paymentInfo['installmentAmount']);
                    $installment->addChild('last-installment-amount', $paymentInfo['lastInstallmentAmount']);
                    $installment->addChild('interest-rate', $paymentInfo['interestRate']);
                    $installment->addChild('payment-firstday', $paymentInfo['paymentFirstDay']);
                }
            }
        }
    }

    /**
     * This method set's the installment-calculation element of the request xml
     */
    private function setRatepayContentCalculation($calculation)
    {
        $content = $this->request->addChild('content');
        $installment = $content->addChild('installment-calculation');

        $installment->addChild('amount', $calculation['amount']);

        if ($calculation['method'] == 'calculation-by-rate') {
            $calc_rate = $installment->addChild('calculation-rate');
            $calc_rate->addChild('rate', $calculation['value']);
        } else if ($calculation['method'] == 'calculation-by-time') {
            $calc_time = $installment->addChild('calculation-time');
            $calc_time->addChild('month', $calculation['value']);
        }
        if (!empty($calculation['debitSelect'])) {
            $installment->addChild('payment-firstday', $calculation['debitSelect']);
        }
    }

    /**
     * Sending request to the RatePAY Server and returning the response.
     *
     * @param array $loggingInfo
     * @return SimpleXML
     */
    private function sendXmlRequest($loggingInfo)
    {
        $sandbox = $loggingInfo['sandbox'];
        $client = Mage::getSingleton('ratepaypayment/request_communication', array($sandbox));
        $client->resetParameters();
        $client->setRawData(trim($this->request->asXML(), "\xef\xbb\xbf"), "text/xml; charset=UTF-8");
        $response = $client->request('POST');
        $this->response = new SimpleXMLElement($response->getBody());
        if($loggingInfo['logging'] && $loggingInfo['requestType'] != 'CALCULATION_REQUEST' && $loggingInfo['requestType'] != 'CONFIGURATION_REQUEST') {
            Mage::getSingleton('ratepaypayment/logging')->log($loggingInfo, $this->request, $this->response);
        }
        return $this->response;
    }

    /**
     * Creates new empty XML Object for Requests
     */
    public function constructXml()
    {
        $xmlString = '<request version="1.0" xmlns="urn://www.ratepay.com/payment/1_0"></request>';
        $this->request = null;
        $this->request = new RatePAY_Ratepaypayment_Model_Request_Xml($xmlString);
    }

    /**
     * This method replaced all zoot signs
     *
     * @param string $str
     * @return string
     */
    private function removeSpecialChars($str)
    {
        $search = array("–", "´", "‹", "›", "‘", "’", "‚", "“", "”", "„", "‟", "•", "‒", "―", "—", "™", "¼", "½", "¾");
        $replace = array("-", "'", "<", ">", "'", "'", ",", '"', '"', '"', '"', "-", "-", "-", "-", "TM", "1/4", "1/2", "3/4");
        return $this->removeSpecialChar($search, $replace, $str);
    }

    /**
     * This method replaced one zoot sing from a string
     *
     * @param array $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    private function removeSpecialChar($search, $replace, $subject)
    {
        $str = str_replace($search, $replace, $subject);
        return $str;
    }

}
