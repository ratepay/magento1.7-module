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
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        $availableCountries = explode(',', $this->getConfigData('specificcountry'), $storeId);
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

        if ($totalAmount <= $minAmount || $totalAmount >= $maxAmount) {
            return false;
        }

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();
        $diff = array_diff($this->getHelper()->getImportantAddressData($shippingAddress), $this->getHelper()->getImportantAddressData($billingAddress));

        if (!$this->getConfigData('delivery_address', $storeId) && count($diff)) {
            return false;
        }

        $company = $quote->getBillingAddress()->getCompany();
        $vatId = $quote->getCustomerTaxvat();
        if (!$this->getConfigData('b2b', $storeId) && (!empty($vatId) || !empty($company))) {

            return false;
        }

        return true;
    }

    /**
     *  Sets Sessionvariable to go back to the payment overview and to reload the payment-method block
     *  Additionally setting a variable to hide RatePAY if the Riskcheck was negative
     */
    protected function _hidePaymentMethod()
    {
        Mage::getSingleton('checkout/session')->setRatepayMethodHide(true);
        Mage::getSingleton('checkout/session')->setUpdateSection('payment-method');
        Mage::getSingleton('checkout/session')->setGotoSection('payment');
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
     * Authorize the transaction by calling PAYMENT_INIT and PAYMENT_REQUEST.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  PayIntelligent_Ratepay_Model_Method_Rechnung
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $client = Mage::getSingleton('ratepay/request');
        $helper = Mage::helper('ratepay/mapping');
        $result = $client->callPaymentInit($helper->getRequestHead($this->getQuoteOrOrder()), $helper->getLoggingInfo($this->getQuoteOrOrder()));
        if (is_array($result) || $result == true) {
            $payment->setAdditionalInformation('transactionId', $result['transactionId']);
            $payment->setAdditionalInformation('transactionShortId', $result['transactionShortId']);
            $result = $client->callPaymentRequest($helper->getRequestHead($this->getQuoteOrOrder()),
                                                    $helper->getRequestCustomer($this->getQuoteOrOrder()),
                                                    $helper->getRequestBasket($this->getQuoteOrOrder()),
                                                    $helper->getRequestPayment($this->getQuoteOrOrder()),
                                                        $helper->getLoggingInfo($this->getQuoteOrOrder()
                                                    )
                        );
            if (is_array($result) || $result == true) {
                $payment->setAdditionalInformation('descriptor', $result['descriptor']);

            } else {
                $this->_hidePaymentMethod();
                Mage::throwException($this->_getHelper()->__('Pi PAYMENT_REQUEST Declined'));
            }
        } else {
            $this->_hidePaymentMethod();
            Mage::throwException($this->_getHelper()->__('Pi Gateway Offline'));
        }
        $this->_cleanSession();
        return $this;
    }

    private function _cleanSession()
    {
        Mage::getSingleton('core/session')->setDirectDebitFlag(null);
        Mage::getSingleton('core/session')->setAccountHolder(null);
        Mage::getSingleton('core/session')->setAccountNumber(null);
        Mage::getSingleton('core/session')->setBankCodeNumber(null);
        Mage::getSingleton('core/session')->setBankName(null);
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
}