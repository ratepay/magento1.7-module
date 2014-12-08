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

abstract class PayIntelligent_Ratepay_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
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
    protected $_canUseInternal = false;

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
     * To check billing country is allowed for the payment method
     *
     * @return bool
     */
    public function canUseForCountry($country)
    {
        $availableCountries = explode(',', $this->getConfigData('specificcountry'));
        if(!in_array($country, $availableCountries)){
            return false;
        }

        return true;
    }

    /**
     * Check if currency is avaible for this payment
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        $availableCurrencies = explode(',', $this->getConfigData('specificcurrency', $storeId));
        if (!in_array($currencyCode, $availableCurrencies)) {
            return false;
        }
        return true;
    }

    /**
     * Check if payment method is available
     *
     * 1) If a session variable is set, which indicates that the customer was declined by RatePAY within the PAYMENT_REQUEST
     * 2) If customer has set an age and he is under 18 or above 100 years old.
     * 3) If the basket amount is less then min order total amount or more than max order total amount
     * 4) If shipping address doesnt equals billing address
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        $storeId = $quote ? $quote->getStoreId() : null;

        $ratepayMethodHide = Mage::getSingleton('checkout/session')->getRatepayMethodHide();
        if ($ratepayMethodHide == true) {
            return false;
        }

        $queryActive = Mage::helper('ratepay/query')->isPaymentQueryActive($quote);
        $allowedProducts = Mage::getSingleton('ratepay/session')->getAllowedProducts();
        if ($queryActive && (!$allowedProducts || !in_array($this->_code, $allowedProducts))) {
            return false;
        }

        if (!$this->getConfigData('active', $storeId)) {
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
        $minAmount = $this->getConfigData('min_order_total', $storeId);
        $maxAmount = $this->getConfigData('max_order_total', $storeId);

        if ($totalAmount < $minAmount || $totalAmount > $maxAmount) {
            return false;
        }

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();
        $diff = array_diff($this->getHelper()->getImportantAddressData($shippingAddress), $this->getHelper()->getImportantAddressData($billingAddress));

        if (!$this->getConfigData('delivery_address', $storeId) && count($diff)) {
            return false;
        }

        $taxvat = $quote->getCustomerTaxvat();

        $company = $quote->getBillingAddress()->getCompany();
        if (!$this->getConfigData('b2b', $storeId) && !empty($company)) {
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
     * @return PayIntelligent_Ratepay_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper("ratepay");
    }

    /**
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST and PAYMENT_CONFIRM.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  PayIntelligent_Ratepay_Model_Method_Rechnung
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $client = Mage::getSingleton('ratepay/request');

        $order = $this->getQuoteOrOrder();
        $helper = Mage::helper('ratepay/mapping');
        if (Mage::getSingleton('ratepay/session')->getQueryActive() &&
            Mage::getSingleton('ratepay/session')->getTransactionId()) {
            $resultInit['transactionId'] = Mage::getSingleton('ratepay/session')->getTransactionId();
            $resultInit['transactionShortId'] = Mage::getSingleton('ratepay/session')->getTransactionShortId();
        } else {
            $resultInit = $client->callPaymentInit($helper->getRequestHead($order), $helper->getLoggingInfo($order));
        }
        if (is_array($resultInit) || $resultInit == true) {
            $payment->setAdditionalInformation('transactionId', $resultInit['transactionId']);
            $payment->setAdditionalInformation('transactionShortId', $resultInit['transactionShortId']);
            $resultRequest = $client->callPaymentRequest($helper->getRequestHead($order),
                                                  $helper->getRequestCustomer($order),
                                                  $helper->getRequestBasket($order),
                                                  $helper->getRequestPayment($order),
                                                  $helper->getLoggingInfo($order));
            if (is_array($resultRequest) || $resultRequest == true) {
                $payment->setAdditionalInformation('descriptor', $resultRequest['descriptor']);

                if ($this->getConfigData('address_normalization', $order->getStoreId())) {
                    $billingAddress = $order->getBillingAddress();
                    $shippingAddress = $order->getShippingAddress();

                    $billingAddress->setStreet(implode(' ', array($resultRequest['address']['street'], $resultRequest['address']['street-number'])));
                    $billingAddress->setPostcode($resultRequest['address']['zip-code']);
                    $billingAddress->setCity($resultRequest['address']['city']);

                    if ($billingAddress->getCustomerAddressId() == $shippingAddress->getCustomerAddressId()) {
                        $shippingAddress->setStreet(implode(' ', array($resultRequest['address']['street'], $resultRequest['address']['street-number'])));
                        $shippingAddress->setPostcode($resultRequest['address']['zip-code']);
                        $shippingAddress->setCity($resultRequest['address']['city']);
                    }
                }

                $resultConfirm = $client->callPaymentConfirm($helper->getRequestHead($order), $helper->getLoggingInfo($order));

                if (!is_array($resultConfirm) && !$resultConfirm == true) {
                    $this->_abortBackToPayment('Pi PAYMENT_REQUEST Declined');
                }
            } else {
                $this->_abortBackToPayment('Pi PAYMENT_REQUEST Declined');
            }
        } else {
            $this->_abortBackToPayment('Pi Gateway Offline');
        }

        $this->_cleanSession();
        return $this;
    }

    public function _cleanSession()
    {
        Mage::getSingleton('core/session')->setDirectDebitFlag(null);
        Mage::getSingleton('core/session')->setAccountHolder(null);
        Mage::getSingleton('core/session')->setIban(null);
        Mage::getSingleton('core/session')->setBic(null);
        Mage::getSingleton('core/session')->setAccountNumber(null);
        Mage::getSingleton('core/session')->setBankCodeNumber(null);
        Mage::getSingleton('core/session')->setBankName(null);

        Mage::getSingleton('ratepay/session')->setQueryActive(false);
        Mage::getSingleton('ratepay/session')->setTransactionId(null);
        Mage::getSingleton('ratepay/session')->setTransactionShortId(null);
        Mage::getSingleton('ratepay/session')->setAllowedProducts(false);
        Mage::getSingleton('ratepay/session')->setPreviousQuote(null);
    }

    protected function _abortBackToPayment($exception) {
        $order = $this->getQuoteOrOrder();

        if (!$this->getConfigData('sandbox', $order->getStoreId())) {
            $this->_hidePaymentMethod();
        }
        $this->_cleanSession();
        Mage::getSingleton('checkout/session')->setGotoSection('payment');
        Mage::throwException($this->_getHelper()->__($exception));
    }

    /**
     *  Sets Sessionvariable to go back to the payment overview and to reload the payment-method block
     *  Additionally setting a variable to hide RatePAY if the Riskcheck was negative
     */
    protected function _hidePaymentMethod()
    {
        Mage::getSingleton('checkout/session')->setRatepayMethodHide(true);
        Mage::getSingleton('checkout/session')->setUpdateSection('payment-method');
    }

    /**
     * Get the title of every RatePAY payment option with payment fee if available
     *
     * @return string
     */
    public function getTitle()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        $title = $this->getConfigData('title', $storeId);
        $paymentFee = '';
        try {
            $sku = $this->getConfigData('payment_fee', $storeId);
            if(!empty($sku)) {
                $product = Mage::getModel('catalog/product');
                $id = $product->getIdBySku($sku);
                if(!empty($id)) {
                    $product->load($id);
                    $paymentFee = Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true);
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
}