<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class RatePAY_Ratepaypayment_Model_Method_Abstract
 */
abstract class RatePAY_Ratepaypayment_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Method code
     *
     * @var string
     */
    protected $_code = 'ratepay_abstract';

    /**
     * Is payment a gateway
     *
     * @var boolean
     */
    protected $_isGateway = false;

    /**
     * Can payment authorize
     *
     * @var boolean
     */
    protected $_canAuthorize = false;

    /**
     * Can payment void
     *
     * @var boolean
     */
    protected $_canVoid = false;

    /**
     * Can payment use internal
     *
     * @var boolean
     */
    protected $_canUseInternal = true;

    /**
     * Can payment use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can payment capture
     *
     * @var boolean
     */
    protected $_canCapture  = true;

    /**
     * Can payment partial capture
     *
     * @var boolean
     */
    protected $_canCapturePartial = true;

    /**
     * Is payment possible for multishipping
     *
     * @var boolen
     */
    protected $_canUseForMultishipping = false;

    /**
     * Is init needed
     *
     * @var boolean
     */
    protected $_isInitializeNeeded = false;

    /**
     * Can payment refund
     *
     * @var boolean
     */
    protected $_canRefund                   = true;

    /**
     * Is invoice refund possible
     *
     * @var boolean
     */
    protected $_canRefundInvoicePartial     = true;

    /**
     * Can payment manage recurring profiles
     *
     * @var boolean
     */
    protected $_canManageRecurringProfiles  = false;

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return RatePAY_Ratepaypayment_Model_Method_Abstract
     * @throws Mage_Core_Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function assignData($data)
    {
        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(false);
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();

        // dob
        $dob = (isset($params[$this->_code . '_day'])) ? $this->getDob($data) : false;

        if (!$this->getHelper()->isCompanySet($quote) &&
            (!$this->getHelper()->isDobSet($quote) ||
                $quote->getCustomerDob() != $dob)) {
            if ($dob) {
                $validAge = $this->getHelper()->isValidAge($dob);
                switch($validAge) {
                    case 'old':
                        Mage::throwException($this->_getHelper()->__('Date Error'));
                        break;
                    case 'young':
                        Mage::throwException($this->_getHelper()->__('Age Error'));
                        break;
                    case 'wrongdate':
                        Mage::throwException($this->_getHelper()->__('Date Error'));
                        break;
                    case 'success':
                        $this->getHelper()->setDob($quote, $dob);
                        break;
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Date Error'));
            }
        }

        // phone
        if (!$this->getHelper()->isPhoneSet($quote)) {
            if (isset($params[$this->_code . '_phone'])) {
                $phone = $data->getData($this->_code . '_phone');
                if ($phone && $this->getHelper()->isValidPhone($phone)) {
                    $this->getHelper()->setPhone($quote, $phone);
                } else {
                    // M1-22 : use default phone number if missing
                    if (!empty($phone)) {
                        Mage::throwException($this->_getHelper()->__('Phone Error'));
                    }
                }
            } else {
                // M1-22 : use default phone number if missing
                $params[$this->_code . '_phone'] = '03033988560';
                $this->getHelper()->setPhone($quote, '03033988560');
            }
        } else {
            $phoneCustomer = $this->getHelper()->getPhone($quote);
            $phoneParams = (isset($params[$this->_code . '_phone'])) ? $params[$this->_code . '_phone'] : false;
            if ($phoneCustomer != $phoneParams && !empty($phoneParams)) {
                if ($this->getHelper()->isValidPhone($phoneParams)) {
                    $this->getHelper()->setPhone($quote, $phoneParams);
                } else {
                    Mage::throwException($this->_getHelper()->__('Phone Error'));
                }
            } elseif (!$this->getHelper()->isValidPhone($phoneCustomer)) {
                Mage::throwException($this->_getHelper()->__('Phone Error'));
            }
        }

        // taxvat
        if (isset($params[$this->_code . '_taxvat'])) {
            if ($this->getHelper()->isValidTaxvat($quote, $params[$this->_code . '_taxvat'])) {
                $this->getHelper()->setTaxvat($quote, $params[$this->_code . '_taxvat']);
            } else {
                // M1-23 : VatId became optional
//                Mage::throwException($this->_getHelper()->__('VatId Error'));
            }
        }

        //customer balance (store credit)
        if(key_exists('use_customer_balance', $params) && $params['use_customer_balance'] == 1){
            Mage::throwException($this->getHelper()->__('StoreCredit Error'));
        }

        return $this;
    }

    /**
     * Assign bank data to info model instance
     *
     * @param mixed $data
     * @return RatePAY_Ratepaypayment_Model_Method_Abstract
     */
    protected function assignBankData($data)
    {
        parent::assignData($data);
        $params = $data->getData();

        if (key_exists($params['method'] . '_method_invoice', $params) && (bool) $params[$params['method'] . '_method_invoice']) {
            return $this;
        }
        #if ($params['method'] == 'ratepay_rate0') {
        #    return $this;
        #}

        // Bank data
        if (!empty($params[$this->_code . '_iban'])) {
            $iban = $this->_clearIban($params[$this->_code . '_iban']);
            $countryPrefix = substr($iban, 0, 2);
            $length = strlen($iban);
            $ibanValid = ($length >= 15 && $length <= 34);
            switch ($countryPrefix) {
                case 'DE':
                    if (strlen($iban) <> 22) $ibanValid = false;
                    break;
                case 'AT':
                    if (strlen($iban) <> 20) $ibanValid = false;
                    break;
                case 'CH':
                    if (strlen($iban) <> 21) $ibanValid = false;
                    break;
                case 'NL':
                    if (strlen($iban) <> 18) $ibanValid = false;
                    break;
                case 'BE':
                    if (strlen($iban) <> 16) $ibanValid = false;
                    break;
            }
            if (!$ibanValid) {
                Mage::throwException($this->_getHelper()->__('IBAN invalid Error'));
            }
        } elseif (!empty($params[$this->_code . '_account_number'])) {
            $accountnumber = $params[$this->_code . '_account_number'];
            $bankcode = $params[$this->_code . '_bank_code_number'];
            if (!is_numeric($accountnumber)) {
                Mage::throwException($this->_getHelper()->__('insert account number'));
            } elseif (empty($bankcode) || !is_numeric($bankcode)) {
                Mage::throwException($this->_getHelper()->__('insert bank code'));
            }
        } else {
            Mage::throwException($this->_getHelper()->__('insert bank data'));
        }

        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(true);
        if ((isset($params[$this->_code . '_account_number']) && (!empty($params[$this->_code . '_account_number']) && !empty($params[$this->_code . '_bank_code_number'])) || !empty($params[$this->_code . '_iban']))) {
            $this->getHelper()->setBankData($params, $this->_code);
        }

        return $this;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else{
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $availableCountries = explode(',', $this->getHelper()->getRpConfigData($quote, $this->_code, 'specificcountry_billing'));
        if(!in_array($country, $availableCountries)){
            return false;
        }
        return true;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountryDelivery($country)
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else{
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $availableCountries = explode(',', $this->getHelper()->getRpConfigData($quote, $this->_code, 'specificcountry_delivery'));
        if(!in_array($country, $availableCountries)){
            return false;
        }
        return true;
    }

    /**
     * Check if currency is available for this payment
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else{
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $availableCurrencies = explode(',', $this->getHelper()->getRpConfigData($quote, $this->_code, 'specificcurrency'));
        if (!in_array($currencyCode, $availableCurrencies)) {
            return false;
        }
        return true;
    }

    /**
     * Check if payment method is available
     *
     * 1) If quote is not null
     * 2) If a session variable is set, which indicates that the customer was declined by RatePAY within the PAYMENT_REQUEST
     * 3) If customer has set an age and he is under 18 or above 100 years old.
     * 4) If the basket amount is less then min order total amount or more than max order total amount
     * 5) If shipping address doesnt equals billing address
     * 6) If b2b is not allowed and billing address contains an company name
     * 7) If the current customer belongs to a excluded customer group
     * 8) If one of the cart items belongs to a excluded product category
     * 9) If the selected shipping method is excluded
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        if (is_null($quote)) {
            return false;
        }

        $isActive = (bool)(int)$this->getHelper()->getRpConfigData($quote, $this->_code, 'active');
        $checkResult = new StdClass;
        $checkResult->isAvailable = $isActive;
        $checkResult->isDeniedInConfig = !$isActive; // for future use in observers
        Mage::dispatchEvent('payment_method_is_active', array(
            'result'          => $checkResult,
            'method_instance' => $this,
            'quote'           => $quote,
        ));

        if (!$checkResult->isAvailable) {
            return false;
        }

        // M1-10 Ban ratepay for 48h if reason code is 703, 720, 721
        /** @var RatePAY_Ratepaypayment_Model_PaymentBan $paymentBanModel */
        $paymentBanModel = Mage::getModel('ratepaypayment/paymentBan');

        $loggedCustomerId = Mage::getSingleton('customer/session')->getId();
        if ($loggedCustomerId) {
            $customerIdentifier = $loggedCustomerId;
        } else {
            $customerIdentifier = $quote->getCustomerEmail();
        }

        $paymentBan = $paymentBanModel->loadByCustomerIdPaymentMethod($customerIdentifier, $this->getCode());
        if (!empty($paymentBan->getId())) {
            $dtToday = new DateTime();
            $dtBanStartDate = new DateTime($paymentBan->getFromDate());
            $dtBanEndDate = new DateTime($paymentBan->getToDate());

            if (
                $dtToday->getTimestamp() > $dtBanStartDate->getTimestamp()
                && $dtToday->getTimestamp() < $dtBanEndDate->getTimestamp()
            ) {
                return false;
            }
        }

        $ratepayMethodHide = Mage::getSingleton('ratepaypayment/session')->getRatepayMethodHide();
        if ($ratepayMethodHide == true) {
            return false;
        }

        /*$queryActive = Mage::helper('ratepaypayment/query')->isPaymentQueryActive($quote);
        $allowedProducts = Mage::getSingleton('ratepaypayment/session')->getAllowedProducts();
        if ($queryActive && (!$allowedProducts || !in_array($this->_code, $allowedProducts))) {
            return false;
        }*/

        if ($this->getHelper()->isDobSet($quote)) {
            $validAge = $this->getHelper()->isValidAge($quote->getCustomerDob());
            switch($validAge) {
                case 'young':
                    return false;
                case 'old':
                    return false;
                case 'wrongdate':
                    return false;
            }
        }

        $totalAmount = $quote->getGrandTotal();
        $minAmount = $this->getHelper()->getRpConfigData($quote, $this->_code, 'min_order_total');
        $maxAmount = $this->getHelper()->getRpConfigData($quote, $this->_code, 'max_order_total');
        if (!empty($quote->getBillingAddress()->getCompany())) {
            $maxAmount = $this->getHelper()->getRpConfigData($quote, $this->_code, 'limit_max_b2b');
        }


        if ($totalAmount < $minAmount || $totalAmount > $maxAmount) {
            return false;
        }

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();
        $diff = array_diff($this->getHelper()->getImportantAddressData($shippingAddress), $this->getHelper()->getImportantAddressData($billingAddress));

        if (!$this->getHelper()->getRpConfigData($quote, $this->_code, 'delivery_address') && count($diff)) {
            return false;
        }

        if (!$this->canUseForCountryDelivery($shippingAddress->getCountryId())) {
            return false;
        }

        $company = $quote->getBillingAddress()->getCompany();
        if (!$this->getHelper()->getRpConfigData($quote, $this->_code, 'b2b') && !empty($company)) {
            return false;
        }

        $specificRoles = explode(",", $this->getHelper()->getRpConfigData($quote, $this->_code, 'specificgroups', true));
        $customerRole = $quote->getCustomerGroupId();
        if (!in_array("ALL", $specificRoles) && !in_array($customerRole, $specificRoles)) {
            return false;
        }

        foreach($quote->getAllItems() as $item){
            $productCategoryIds = $item->getProduct()->getCategoryIds();
            $configCategoryIds = explode(",", $this->getHelper()->getRpConfigData($quote, 'ratepay_general', 'specificcategories', true, true));
            if (!in_array("NO", $configCategoryIds) && count(array_intersect($productCategoryIds, $configCategoryIds)) > 0) {
                return false;
            }
        }

        $specificShippingMethods = explode(",", $this->getHelper()->getRpConfigData($quote, 'ratepay_general', 'specificshipping', true, true));
        $quoteShippingMethod = $shippingAddress->getShippingMethod();
        if (!in_array("NO", $specificShippingMethods) && in_array($quoteShippingMethod, $specificShippingMethods)) {
            return false;
        }

        $status = $this->getHelper()->getRpConfigData($quote, $this->_code, 'status');
        if($status != 2){
            return false;
        }
        return true;
    }

    /**
     * Return Quote or Order Object depending what the Payment is
     *
     * @return Mage_Sales_Model_Order|Mage_Sales_Model_Quote
     */
    public function getQuoteOrOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $quoteOrOrder = $paymentInfo->getOrder();
        } else {
            $quoteOrOrder = $paymentInfo->getQuote();
        }

        return $quoteOrOrder;
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

    /**
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST and PAYMENT_CONFIRM.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  RatePAY_Ratepaypayment_Model_Method_Rechnung
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount = 0)
    {
        /* @var \RatePAY_Ratepaypayment_Helper_Data $helperData */
        $helperData = Mage::helper('ratepaypayment/data');
        /* @var \RatePAY_Ratepaypayment_Helper_Mapping $helperMapping */
        $helperMapping = Mage::helper('ratepaypayment/mapping');

        $quote = $this->getQuoteOrOrder();
        $paymentMethod = $quote->getPayment()->getMethod();

        $sandbox = (bool) $helperData->getRpConfigData($quote, $paymentMethod, 'sandbox');
        $logging = (bool) $helperData->getRpConfigData($quote, 'ratepay_general', 'logging', true, true);

        $useFallbackShippingItem = $helperData->shouldUseFallbackShippingItem($quote, true);

        $requestInit = Mage::getSingleton('ratepaypayment/libraryConnector', array($sandbox));

        $head = $helperMapping->getRequestHead($quote);
        // Calling PAYMENT INIT
        $responseInit = $requestInit->callPaymentInit($head);
        if ($logging) {
            Mage::getSingleton('ratepaypayment/logging')->log($responseInit, $quote);
        }

        if ($responseInit->isSuccessful()) {
            $requestRequest = Mage::getSingleton('ratepaypayment/libraryConnector', array($sandbox));

            // Add transaction id to head
            $head['TransactionId'] = $responseInit->getTransactionId();

            // Add DFP token to head
            $dfpToken = Mage::getSingleton('ratepaypayment/session')->getDeviceIdentToken();
            if (!empty($dfpToken)) {
                $head['CustomerDevice']['DeviceToken'] = $dfpToken;
            }

            $helperMapping->setUseFallbackShippingItem($useFallbackShippingItem);
            $content = $helperMapping->getRequestContent($quote, "PAYMENT_REQUEST");

            $payment->setAdditionalInformation('transactionId', $head['TransactionId']);
            $payment->setAdditionalInformation('profileId', $head['Credential']['ProfileId']);
            $payment->setAdditionalInformation('securityCode', $head['Credential']['Securitycode']);
            $payment->setAdditionalInformation('api', 'API_1.8');

            // Calling PAYMENT REQUEST
            $responseRequest = $requestRequest->callPaymentRequest($head, $content);
            if ($logging) {
                Mage::getSingleton('ratepaypayment/logging')->log($responseRequest, $quote);
            }

            if ($responseRequest->isSuccessful()) {
                $payment->setAdditionalInformation('descriptor', $responseRequest->getDescriptor());
                $quote->setRatepayUseShippingFallback((int)$useFallbackShippingItem);
                $quote->save();

                $confirm = (bool) $helperData->getRpConfigData($quote, 'ratepay_general', 'confirm', true, true);
                if ($confirm == true) {
                    // Calling PAYMENT CONFIRM
                    unset($head['CustomerDevice']['DeviceToken']);
                    $content = $helperMapping->getRequestContent($quote, "PAYMENT_CONFIRM");
                    $responseRequest = $requestRequest->callPaymentConfirm($head, $content);
                }
            } else {
                if ($responseRequest->isRetryAdmitted()) {
                    $this->_abortBackToPayment($responseRequest->getCustomerMessage(), "soft");
                } else {
                    // M1-10 Ban ratepay for 48h if reason code is 703, 720, 721
                    if (in_array($responseRequest->getReasonCode(), array(703, 720, 721))) {
                        /** @var RatePAY_Ratepaypayment_Model_PaymentBan $paymentBan */
                        $paymentBan = Mage::getModel('ratepaypayment/paymentBan');

                        $loggedCustomerId = Mage::getSingleton('customer/session')->getId();
                        if ($loggedCustomerId) {
                            $customerIdentifier = $loggedCustomerId;
                        } else {
                            $customerIdentifier = $quote->getCustomerEmail();
                        }

                        $paymentBan = $paymentBan->loadByCustomerIdPaymentMethod(
                            $customerIdentifier,
                            $quote->getPayment()->getMethod()
                        );
                        $paymentBan->setCustomerId($customerIdentifier);
                        $paymentBan->setPaymentMethod($quote->getPayment()->getMethod());
                        $paymentBan->setFromDate((new DateTime())->format(DATE_ISO8601));
                        $paymentBan->setToDate((new DateTime('+2day'))->format(DATE_ISO8601));
                        $paymentBan->save();
                    }
                    $this->_cleanSession();
                    $this->_abortBackToPayment($responseRequest->getCustomerMessage(), "hard", $responseRequest->getReasonCode());
                }
            }
        } else {
            $this->_cleanSession();
            $this->_abortBackToPayment('Gateway Offline', "hard"); // @ToDO: Find better response text
        }

        $this->_resetDeviceFingerprint();

        return $this;
    }

    public function _cleanSession()
    {
        $this->_resetDeviceFingerprint();

        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(null);
        Mage::getSingleton('ratepaypayment/session')->setAccountHolder(null);
        Mage::getSingleton('ratepaypayment/session')->setIban(null);
        Mage::getSingleton('ratepaypayment/session')->setAccountNumber(null);
        Mage::getSingleton('ratepaypayment/session')->setBankCodeNumber(null);
        Mage::getSingleton('ratepaypayment/session')->setBankName(null);

        Mage::getSingleton('ratepaypayment/session')->setQueryActive(false);
        Mage::getSingleton('ratepaypayment/session')->setTransactionId(false);
        Mage::getSingleton('ratepaypayment/session')->setAllowedProducts(false);
        Mage::getSingleton('ratepaypayment/session')->setPreviousQuote(null);
    }

    private function _resetDeviceFingerprint()
    {
        // Reset DFP token after every call for authorization
        Mage::getSingleton('ratepaypayment/session')->setDeviceIdentToken(null);
        Mage::getSingleton('checkout/session')->setUpdateSection('payment-method');
    }

    protected function _abortBackToPayment($exception, $type = null, $errorCode = null)
    {
        $order = $this->getQuoteOrOrder();

        if(strpos($exception, 'zusaetzliche-geschaeftsbedingungen-und-datenschutzhinweis') !== false){
            $exception = $this->getExceptionWithReadableLink($exception, $order, $this->_code);
        }

        if (!$this->getHelper()->getRpConfigData($order, $this->_code, 'sandbox')
            && !Mage::app()->getStore()->isAdmin() && $type == 'hard') {
            $this->_hidePaymentMethod();
        } elseif ($type == 'soft' && empty($exception)) {
            $exception = $this->_getHelper()->__('Soft Error');
        }

        $this->_cleanSession();

        // M1-10 redirect to billing to force refresh payment list
        if (in_array($errorCode , array(703, 720, 721))) {
            Mage::getSingleton('checkout/session')->setGotoSection('billing');
        } else {
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
        }
        Mage::throwException($this->_getHelper()->__((strip_tags($exception))));
    }

    /**
     * @param $exception
     * @param $order
     * @param $code
     *
     * @return string
     */
    private function getExceptionWithReadableLink($exception, $order, $code)
    {
        $matches = array();
        preg_match('/href="([\w:\.\/\-]+)"/', $exception, $matches);

        if (empty($matches)) {
            return $exception . "\n\n" . $this->getHelper()->getRpConfigData($order, $code, 'privacy_policy');
        }

        return $exception . "\n\n www.ratepay.com/legal";
    }

    /**
     *  Sets Sessionvariable to go back to the payment overview and to reload the payment-method block
     *  Additionally setting a variable to hide RatePAY if the Riskcheck was negative
     */
    protected function _hidePaymentMethod()
    {
        Mage::getSingleton('ratepaypayment/session')->setRatepayMethodHide(true);
    }

    /**
     * Get the title of every RatePAY payment option with payment fee if available
     *
     * @return string
     */
    public function getTitle()
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else{
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        $title = $this->getHelper()->getRpConfigData($quote, $this->_code, 'title');
        $paymentFee = '';
        try {
            $sku = $this->getHelper()->getRpConfigData($quote, $this->_code, 'payment_fee');
            if(!empty($sku)) {
                $product = Mage::getModel('catalog/product');
                $id = $product->getIdBySku($sku);
                if(!empty($id)) {
                    $product->load($id);
                    $paymentFee = Mage::helper('core')->currency($paymentFee,true,false);
                    $paymentFee = ' (+' . $paymentFee . ')';
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $title . $paymentFee;
    }

    /**
     * Returns date object from dob params
     *
     * @param   mixed $data
     * @return  Zend_Date
     */
    protected function getDob($data) {
        $day   = $data->getData($this->_code . '_day');
        $month = $data->getData($this->_code . '_month');
        $year  = $data->getData($this->_code . '_year');

        $datearray = array('year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => 0,
            'minute' => 0,
            'second' => 0);
        return new Zend_Date($datearray);
    }

    public function _clearIban($iban)
    {
        $iban = ltrim(strtoupper($iban));
        $iban = preg_replace('/^IBAN/','',$iban);
        $iban = preg_replace('/[^a-zA-Z0-9]/','',$iban);
        $iban = strtoupper($iban);
        return $iban;
    }
}
