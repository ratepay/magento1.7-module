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

class RatePAY_Ratepaypayment_Model_Method_Rate extends RatePAY_Ratepaypayment_Model_Method_Abstract
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
    protected $_formBlockType = 'ratepaypayment/payment_form_rate';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepaypayment/payment_info_rate';
    
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

        // dob
        $dob = (isset($params[$this->_code . '_day'])) ? $this->getDob($data) : false;

        if(!$this->getHelper()->isDobSet($quote) ||
            $quote->getCustomerDob() != $dob) {
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
            if ($this->getHelper()->isValidTaxvat($params[$this->_code . '_taxvat'])) {
                $this->getHelper()->setTaxvat($quote, $params[$this->_code . '_taxvat']);
            } else {
                Mage::throwException($this->_getHelper()->__('VatId Error'));
            }
        }
        
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return  RatePAY_Ratepaypayment_Model_Method_Rate
     */
    public function validate()
    {
        parent::validate();

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
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST and PAYMENT_CONFIRM.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  RatePAY_Ratepaypayment_Model_Method_Rate
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $client = Mage::getSingleton('ratepaypayment/request');

        $order = $this->getQuoteOrOrder();
        $helper = Mage::helper('ratepaypayment/mapping');
        if (Mage::getSingleton('ratepaypayment/session')->getQueryActive() &&
            Mage::getSingleton('ratepaypayment/session')->getTransactionId()) {
            $result['transactionId'] = Mage::getSingleton('ratepaypayment/session')->getTransactionId();
            $result['transactionShortId'] = Mage::getSingleton('ratepaypayment/session')->getTransactionShortId();
        } else {
            $result = $client->callPaymentInit($helper->getRequestHead($order), $helper->getLoggingInfo($order));
        }

        if (is_array($result) || $result == true) {
            $payment->setAdditionalInformation('transactionId', $result['transactionId']);
            $payment->setAdditionalInformation('transactionShortId', $result['transactionShortId']);
            $result = $client->callPaymentRequest($helper->getRequestHead($order), 
                                                    $helper->getRequestCustomer($order), 
                                                    $helper->getRequestBasket($order), 
                                                    $helper->getRequestPayment($order,
                                                        (float)Mage::getSingleton('checkout/session')->getRatepayRateTotalAmount(),
                                                        'PAYMENT_REQUEST'
                                                    ),
                                                    $helper->getLoggingInfo($order)
                        );
            if (is_array($result) || $result == true) {
                $payment->setAdditionalInformation('descriptor', $result['descriptor']);

                $resultConfirm = $client->callPaymentConfirm($helper->getRequestHead($order), $helper->getLoggingInfo($order));

                if (!is_array($resultConfirm) && !$resultConfirm == true) {
                    $this->_abortBackToPayment('PAYMENT_REQUEST Declined');
                }
            } else {
                $this->_abortBackToPayment('PAYMENT_REQUEST Declined');
            }
        } else {
            $this->_abortBackToPayment('Gateway Offline');
        }

        return $this;
    }

}

