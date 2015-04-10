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

class RatePAY_Ratepaypayment_RatenrechnerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Calculates the rates by from user defined rate
     */
    public function rateAction()
    {
        try {
            if (preg_match('/^[0-9]+(\.[0-9][0-9][0-9])?(,[0-9]{1,2})?$/', $this->getRequest()->getParam('calcValue'))) {
                $calcValue = str_replace(".", "", $this->getRequest()->getParam('calcValue'));
                $calcValue = str_replace(",", ".", $calcValue);
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $result = $client->callCalculationRequest($helper->getRequestHead($this->getQuote(),
                                                                'calculation-by-rate', 'ratepay_rate'),
                                                            $helper->getLoggingInfo($this->getQuote(),'ratepay_rate'),
                                                            $this->getCalculationInfo('calculation-by-rate',$calcValue, $debitSelect)
                            );
                if (is_array($result) || $result == true) {
                    $this->setSessionData($result);
                    $this->getHtml($this->formatResult($result));
                } else {
                    $this->unsetSessionData();
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else if (preg_match('/^[0-9]+(\,[0-9][0-9][0-9])?(.[0-9]{1,2})?$/', $this->getRequest()->getParam('calcValue'))) {
                $calcValue = $this->getRequest()->getParam('calcValue');
                $calcValue = str_replace(",", "", $calcValue);
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $result = $client->callCalculationRequest($helper->getRequestHead(
                                                                $this->getQuote(),
                                                                'calculation-by-rate', 
                                                                'ratepay_rate'),
                                                            $helper->getLoggingInfo($this->getQuote(),'ratepay_rate'),
                                                            $this->getCalculationInfo('calculation-by-rate',$calcValue,$debitSelect)
                            );
                if (is_array($result) || $result == true) {
                    $this->setSessionData($result);
                    $this->getHtml($this->formatResult($result));
                } else {
                    $this->unsetSessionData();
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else {
                $this->unsetSessionData();
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData();
            echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_server_off') . "</div>";
        }
    }

    /**
     * Calculates the rates by from user defined runtime
     */
    public function runtimeAction()
    {
        try {
            if (preg_match('/^[0-9]{1,3}$/', $this->getRequest()->getParam('calcValue'))) {
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $result = $client->callCalculationRequest($helper->getRequestHead(
                                                                $this->getQuote(),
                                                                'calculation-by-time', 
                                                                'ratepay_rate'),
                                                            $helper->getLoggingInfo(
                                                                    $this->getQuote(),
                                                                    'ratepay_rate'),
                                                            $this->getCalculationInfo(
                                                                    'calculation-by-time',
                                                                    $this->getRequest()->getParam('calcValue'),
                                                                    $debitSelect)
                            );
                
                if (is_array($result) || $result == true) {
                    $this->setSessionData($result);
                    $this->getHtml($this->formatResult($result));
                } else {
                    $this->unsetSessionData();
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else {
                $this->unsetSessionData();
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData();
            echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_server_off') . "</div>";
        }
    }

    /**
     * Calculates the rates by from user defined rate
     *
     * @param string $method
     * @param float $calcValue
     * @return array
     */
    private function getCalculationInfo($method = '', $calcValue = '', $debitSelect = '') 
    {
        $calculation = array();
        $calculation['method'] = $method;
        $calculation['value'] = $calcValue;
        $calculation['amount'] = round($this->getQuote()->getGrandTotal(),2);
        $calculation['debitSelect'] = $debitSelect;
        return $calculation;
    }

    /**
     * Retrieve quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    private function getQuote() 
    {
        return Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Formats the result
     * @param array $result
     * @return array
     */
    private function formatResult($result) 
    {
        $result['totalAmount'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['totalAmount']);
        $result['amount'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['amount']);
        $result['interestRate'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['interestRate']);
        $result['interestAmount'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['interestAmount']);
        $result['serviceCharge'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['serviceCharge']);
        $result['annualPercentageRate'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['annualPercentageRate']);
        $result['monthlyDebitInterest'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['monthlyDebitInterest']);
        $result['rate'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['rate']);
        $result['lastRate'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($result['lastRate']);

        return $result;
    }

    /**
     * Set the calculated rates into the session
     *
     * @param array $result
     */
    private function setSessionData($result) 
    {
        Mage::getSingleton('checkout/session')->setRatepayRateTotalAmount($result['totalAmount']);
        Mage::getSingleton('checkout/session')->setRatepayRateAmount($result['amount']);
        Mage::getSingleton('checkout/session')->setRatepayRateInterestRate($result['interestRate']);
        Mage::getSingleton('checkout/session')->setRatepayRateInterestAmount($result['interestAmount']);
        Mage::getSingleton('checkout/session')->setRatepayRateServiceCharge($result['serviceCharge']);
        Mage::getSingleton('checkout/session')->setRatepayRateAnnualPercentageRate($result['annualPercentageRate']);
        Mage::getSingleton('checkout/session')->setRatepayRateMonthlyDebitInterest($result['monthlyDebitInterest']);
        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRatesFull($result['numberOfRatesFull']);
        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRates($result['numberOfRates']);
        Mage::getSingleton('checkout/session')->setRatepayRateRate($result['rate']);
        Mage::getSingleton('checkout/session')->setRatepayRateLastRate($result['lastRate']);
        Mage::getSingleton('checkout/session')->setRatepayPaymentFirstDay($result['debitSelect']);
    }

    /**
     * Unsets the calculated rates from the session
     */
    private function unsetSessionData() 
    {
        Mage::getSingleton('checkout/session')->setRatepayRateTotalAmount(null);
        Mage::getSingleton('checkout/session')->setRatepayRateAmount(null);
        Mage::getSingleton('checkout/session')->setRatepayRateInterestRate(null);
        Mage::getSingleton('checkout/session')->setRatepayRateInterestAmount(null);
        Mage::getSingleton('checkout/session')->setRatepayRateServiceCharge(null);
        Mage::getSingleton('checkout/session')->setRatepayRateAnnualPercentageRate(null);
        Mage::getSingleton('checkout/session')->setRatepayRateMonthlyDebitInterest(null);
        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRatesFull(null);
        Mage::getSingleton('checkout/session')->setRatepayRateNumberOfRates(null);
        Mage::getSingleton('checkout/session')->setRatepayRateRate(null);
        Mage::getSingleton('checkout/session')->setRatepayRateLastRate(null);
        Mage::getSingleton('checkout/session')->setRatepayPaymentFirstDay(null);
    }

    /**
     * Printout of rates result
     * @param array $result
     */
    public function getHtml($result)
    {
        Mage::helper('ratepaypayment')->getRateResultHtml($result);
    }
}