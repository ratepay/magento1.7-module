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
     * @return  Mage_Payment_Model_Info
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
                        Mage::throwException($this->_getHelper()->__('Date Error'));
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
                    Mage::throwException($this->_getHelper()->__('Phone Error'));
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Phone Error'));
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
                Mage::throwException($this->_getHelper()->__('VatId Error'));
            }
        }

        //customer balance (store credit)
        if($params['use_customer_balance'] == 1){
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
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();
        $country = $this->getHelper()->getCountryCode($quote);

        if ((bool) $params['ratepay_rate_method_invoice']) {
            return $this;
        }

        // Bank data
        if (!empty($params[$this->_code . '_iban'])) {
            if ($country != "DE") {
                $bic = $params[$this->_code . '_bic'];

                if (strlen($bic) <> 8 && strlen($bic) <> 11) {
                    Mage::throwException($this->_getHelper()->__('insert bank bic'));
                }
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

        $ratepayMethodHide = Mage::getSingleton('ratepaypayment/session')->getRatepayMethodHide();
        if ($ratepayMethodHide == true) {
            return false;
        }

        $queryActive = Mage::helper('ratepaypayment/query')->isPaymentQueryActive($quote);
        $allowedProducts = Mage::getSingleton('ratepaypayment/session')->getAllowedProducts();
        if ($queryActive && (!$allowedProducts || !in_array($this->_code, $allowedProducts))) {
            return false;
        }

        if (!$this->getHelper()->getRpConfigData($quote, $this->_code, 'active')) {
            return false;
        }

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
        $customerRole = Mage::getSingleton('customer/session')->getCustomerGroupId();
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
     * @return Mage_Sales_Model_Order|Mage_Sales_Model_Ouote
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
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  RatePAY_Ratepaypayment_Model_Method_Rechnung
     */
    public function authorize(Varien_Object $payment, $amount = 0)
    {
        $client = Mage::getSingleton('ratepaypayment/request');

        $order = $this->getQuoteOrOrder();
        $helper = Mage::helper('ratepaypayment/mapping');
        $head = $helper->getRequestHead($order);
        if (Mage::getSingleton('ratepaypayment/session')->getQueryActive() &&
            Mage::getSingleton('ratepaypayment/session')->getTransactionId()) {
            $resultInit['transactionId'] = Mage::getSingleton('ratepaypayment/session')->getTransactionId();
        } else {
            $resultInit = $client->callPaymentInit($helper->getRequestHead($order), $helper->getLoggingInfo($order));
        }
        if (is_array($resultInit) || $resultInit == true) {
            $payment->setAdditionalInformation('transactionId', $resultInit['transactionId']);
            $payment->setAdditionalInformation('profileId', $head['profileId']);
            $payment->setAdditionalInformation('securityCode', $head['securityCode']);

            $resultRequest = $client->callPaymentRequest($helper->getRequestHead($order),
                $helper->getRequestCustomer($order),
                $helper->getRequestBasket($order),
                ($amount > 0) ? $helper->getRequestPayment($order, $amount) : $helper->getRequestPayment($order),
                $helper->getLoggingInfo($order));
            if (is_array($resultRequest) || $resultRequest == true) {
                if (!isset($resultRequest['customer_message'])) {
                    $payment->setAdditionalInformation('descriptor', $resultRequest['descriptor']);
                } else {
                $this->_abortBackToPayment($resultRequest['customer_message'], $resultRequest['type']);
                }
            } else {
                $this->_abortBackToPayment('PAYMENT_REQUEST Declined', 'hard');
            }
        } else {
            $this->_abortBackToPayment('Gateway Offline', 'hard' );
        }
        $this->_cleanSession();
        return $this;
    }

    public function _cleanSession()
    {
        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(null);
        Mage::getSingleton('ratepaypayment/session')->setAccountHolder(null);
        Mage::getSingleton('ratepaypayment/session')->setIban(null);
        Mage::getSingleton('ratepaypayment/session')->setBic(null);
        Mage::getSingleton('ratepaypayment/session')->setAccountNumber(null);
        Mage::getSingleton('ratepaypayment/session')->setBankCodeNumber(null);
        Mage::getSingleton('ratepaypayment/session')->setBankName(null);

        Mage::getSingleton('ratepaypayment/session')->setQueryActive(false);
        Mage::getSingleton('ratepaypayment/session')->setTransactionId(false);
        Mage::getSingleton('ratepaypayment/session')->setAllowedProducts(false);
        Mage::getSingleton('ratepaypayment/session')->setPreviousQuote(null);

        Mage::getSingleton('ratepaypayment/session')->setDeviceIdentToken(false);
    }

    protected function _abortBackToPayment($exception, $type = null) {
        $order = $this->getQuoteOrOrder();

        if (!$this->getHelper()->getRpConfigData($order, $this->_code, 'sandbox') && !Mage::app()->getStore()->isAdmin() && $type == 'hard') {
            if(strpos($exception, 'zusaetzliche-geschaeftsbedingungen-und-datenschutzhinweis') !== false){
                $exception = $exception . "\n\n" . $this->getHelper()->getRpConfigData($order, $this->_code, 'privacy_policy');
            }
            $this->_hidePaymentMethod();
        }
        $this->_cleanSession();
        Mage::getSingleton('checkout/session')->setGotoSection('payment');
        Mage::throwException($this->_getHelper()->__((strip_tags($exception))));
    }

    /**
     *  Sets Sessionvariable to go back to the payment overview and to reload the payment-method block
     *  Additionally setting a variable to hide RatePAY if the Riskcheck was negative
     */
    protected function _hidePaymentMethod()
    {
        Mage::getSingleton('ratepaypayment/session')->setRatepayMethodHide(true);
        Mage::getSingleton('checkout/session')->setUpdateSection('payment-method');
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
