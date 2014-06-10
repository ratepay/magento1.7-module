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

class PayIntelligent_Ratepay_Model_Method_Rate extends PayIntelligent_Ratepay_Model_Method_Abstract
{
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_rate';
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'ratepay/payment_form_rate';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepay/payment_info_rate';
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();

        // phone
        if (isset($params[$this->_code . '_phone'])) {
            $phone = $data->getData($this->_code . '_phone');
            if ($phone) {
                $this->getHelper()->setPhone($quote, $phone);
            }
        }

        // company
        if (isset($params[$this->_code . '_company'])) {
            $company = $data->getData($this->_code . '_company');
            if ($company) {
                $this->getHelper()->setCompany($quote, $company);
            }
        }

        // taxvat
        if (isset($params[$this->_code . '_taxvat'])) {
            $taxvat = $data->getData($this->_code . '_taxvat'); //@todo warum nicht via $params
            if ($taxvat) {
                $this->getHelper()->setTaxvat($quote, $taxvat);
            }
        }
        
        // Bank details
        Mage::getSingleton('core/session')->setDirectDebitFlag(false);
        if (array_key_exists($this->_code . '_type', $params) && $params[$this->_code . '_type'] == 'direct_debit') {
            if (!empty($params[$this->_code . '_bank_code_number']) && !empty($params[$this->_code . '_account_holder']) && !empty($params[$this->_code . '_account_number'])) {
                $this->getHelper()->setBankData($params, $quote, $this->_code);
            }
        }

        // dob
        if (isset($params[$this->_code . '_day'])) {
            $day   = $data->getData($this->_code . '_day');
            $month = $data->getData($this->_code . '_month');
            $year  = $data->getData($this->_code . '_year');

            $datearray = array('year' => $year,
                'month' => $month,
                'day' => $day,
                'hour' => 0,
                'minute' => 0,
                'second' => 0);
            $date = new Zend_Date($datearray);

            $this->getHelper()->setDob($quote, $date);
        }

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return  PayIntelligent_Ratepay_Model_Method_Rate
     */
    public function validate()
    {
        parent::validate();

        $quoteOrOrder = $this->getQuoteOrOrder();

        if (!$this->getHelper()->isPhoneSet($quoteOrOrder)) {
            Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
        }

        if($this->getHelper()->isDobSet($quoteOrOrder)) {
            $validAge = $this->getHelper()->isValidAge($quoteOrOrder->getCustomerDob());
            switch($validAge) {
                case 'old':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
                case 'young':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Age Error'));
                    break;
                case 'wrongdate':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
            }
        } else {
            Mage::throwException($this->_getHelper()->__('Pi Date Error'));
        }
        Mage::getSingleton('checkout/session')->getRatepayRateTotalAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateInterestRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateInterestAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateServiceCharge() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateAnnualPercentageRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateMonthlyDebitInterest() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateNumberOfRatesFull() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateNumberOfRates() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('checkout/session')->getRatepayRateLastRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";

        return $this;
    }

    /**
     * Authorize the transaction by calling PAYMENT_INIT and PAYMENT_REQUEST.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  PayIntelligent_Ratepay_Model_Method_Rate
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
                                                    $helper->getRequestPayment($this->getQuoteOrOrder(),
                                                        (float)Mage::getSingleton('checkout/session')->getRatepayRateTotalAmount(),
                                                        'PAYMENT_REQUEST'
                                                    ),
                                                    $helper->getLoggingInfo($this->getQuoteOrOrder())
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

        return $this;
    }

}

