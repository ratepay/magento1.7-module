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

class RatePAY_Ratepaypayment_Adminhtml_Ratepaypayment_RatenrechnerbackendController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Calculates the rates by from user defined rate
     */
    public function rateAction()
    {
        $paymentMethod = $this->getRequest()->getParam('paymentMethod');
        $calcValue = $this->getRequest()->getParam('calcValue');
        try {
            if (preg_match('/^[0-9]+(\.[0-9][0-9][0-9])?(,[0-9]{1,2})?$/', $calcValue)) {
                $calcValue = str_replace(".", "", $calcValue);
                $calcValue = str_replace(",", ".", $calcValue);
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $result = $client->callCalculationRequest(
                    $helper->getRequestHead($this->getQuote(), 'calculation-by-rate', $paymentMethod),
                    $helper->getLoggingInfo($this->getQuote(), $paymentMethod),
                    $this->getCalculationInfo('calculation-by-rate',$calcValue, $debitSelect)
                );
                if (is_array($result) || $result == true) {
                    $this->setSessionData($result, $paymentMethod);
                    $this->getHtml($this->formatResult($result));
                } else {
                    $this->unsetSessionData($paymentMethod);
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else if (preg_match('/^[0-9]+(\,[0-9][0-9][0-9])?(.[0-9]{1,2})?$/', $calcValue)) {
                $calcValue = str_replace(".", "", $calcValue);
                $calcValue = str_replace(",", "", $calcValue);
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $result = $client->callCalculationRequest(
                    $helper->getRequestHead($this->getQuote(), 'calculation-by-rate', $paymentMethod),
                    $helper->getLoggingInfo($this->getQuote(), $paymentMethod),
                    $this->getCalculationInfo('calculation-by-rate',$calcValue,$debitSelect)
                );
                if (is_array($result) || $result == true) {
                    $this->setSessionData($result, $paymentMethod);
                    $this->getHtml($this->formatResult($result));
                } else {
                    $this->unsetSessionData($paymentMethod);
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else {
                $this->unsetSessionData($paymentMethod);
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData($paymentMethod);
            echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_server_off') . "</div>";
        }
    }

    /**
     * Calculates the rates by from user defined runtime
     */
    public function runtimeAction()
    {
        $paymentMethod = $this->getRequest()->getParam('paymentMethod');

        try {
            $calcValue = $this->getRequest()->getParam('calcValue');
            if (preg_match('/^[0-9]{1,3}$/', $calcValue)) {
                $client = Mage::getSingleton('ratepaypayment/request');
                $helper = Mage::helper('ratepaypayment/mapping');
                $debitSelect = $this->getRequest()->getParam('dueDate');
                $result = $client->callCalculationRequest(
                    $helper->getRequestHead(
                        $this->getQuote(),
                        'calculation-by-time',
                        $paymentMethod),
                    $helper->getLoggingInfo(
                        $this->getQuote(),
                        $paymentMethod),
                    $this->getCalculationInfo(
                        'calculation-by-time',
                        $calcValue,
                        $debitSelect)
                );

                if (is_array($result) || $result == true) {
                    $this->setSessionData($result, $paymentMethod);
                    $this->getHtml($this->formatResult($result), (bool) $this->getRequest()->getParam('notification'));
                } else {
                    $this->unsetSessionData($paymentMethod);
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else {
                $this->unsetSessionData($paymentMethod);
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData($paymentMethod);
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
        return Mage::getSingleton('adminhtml/session_quote')->getQuote();
    }

    /**
     * Formats the result
     * @param array $result
     * @return array
     */
    private function formatResult($result) 
    {
        foreach ($result as $key => $value) {
            $result[$key] = (!strstr($key, "number")) ? Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($value) : $value;
        }
        return $result;
    }

    /**
     * Set the calculated rates into the session
     *
     * @param array $result
     */
    private function setSessionData($result, $paymentMethod)
    {
        foreach ($result as $key => $value) {
            $setFunction = "set". Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . ucfirst($key);
            Mage::getSingleton('ratepaypayment/session')->$setFunction($value);
        }
    }

    /**
     * Unsets the calculated rates from the session
     */
    private function unsetSessionData($paymentMethod)
    {
        foreach (Mage::getSingleton('ratepaypayment/session')->getData() as $key => $value) {
            if (!is_array($value)) {
                $sessionNameBeginning = substr($key, 0, strlen($paymentMethod));
                if ($sessionNameBeginning == $paymentMethod && $key[strlen($paymentMethod)] == "_") {
                    $unsetFunction = "uns" . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($key);
                    Mage::getSingleton('ratepaypayment/session')->$unsetFunction();
                }
            }
        }
    }

    /**
     * Printout of rates result
     * @param array $result
     */
    public function getHtml($result, $notification = true)
    {
        Mage::helper('ratepaypayment')->getRateResultHtml($result, $notification);
    }
}