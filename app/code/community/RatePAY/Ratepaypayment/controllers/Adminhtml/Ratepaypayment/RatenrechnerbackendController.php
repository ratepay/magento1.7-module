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
    private $_helperData;
    private $_helperMapping;

    private $_paymentMethod;
    private $_calcValue;
    private $_reward;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        $this->_helperData = Mage::helper('ratepaypayment/data');
        $this->_helperMapping = Mage::helper('ratepaypayment/mapping');

        $this->_paymentMethod = $request->getParam('paymentMethod');
        $this->_calcValue = (int) $request->getParam('calcValue');
        $this->_reward = $request->getParam('rewardPoints') > 0 ? $request->getParam('rewardPoints') : 0;
    }

    private function callCalculationRequest($calculationType, $calculationValue)
    {
        $quote = $this->getQuote();
        $sandbox = (bool) $this->_helperData->getRpConfigData($quote, $this->_paymentMethod, 'sandbox');

        $request = Mage::getSingleton('ratepaypayment/libraryConnector', $sandbox);
        $head = $this->_helperMapping->getRequestHead($quote, $this->_paymentMethod);
        $content = $this->getContent($calculationType, $calculationValue, $quote->getGrandTotal(), $this->_reward);

        return $request->callCalculationRequest($head, $content, $calculationType);
    }

    /**
     * Calculates the rates by from user defined rate
     */
    public function rateAction()
    {
        try {
            if (is_numeric($this->_calcValue)) {
                $response = $this->callCalculationRequest('calculation-by-rate', floatval($this->_calcValue));

                if ($response->isSuccessful()) {
                    $this->setSessionData($response->getResult(), $this->_paymentMethod);
                    $this->getHtml($this->formatResult($response->getResult()), $response->getReasonCode());
                } else {
                    $this->unsetSessionData($this->_paymentMethod);
                    echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
                }
            } else {
                $this->unsetSessionData($this->_paymentMethod);
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData($this->_paymentMethod);
            echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_server_off') . "</div>";
        }
    }

    /**
     * Calculates the rates by from user defined runtime
     */
    public function runtimeAction()
    {
        try {
            $response = $this->callCalculationRequest('calculation-by-time', floatval($this->_calcValue));

            if ($response->isSuccessful()) {
                $this->setSessionData($response->getResult(), $this->_paymentMethod);
                $this->getHtml($this->formatResult($response->getResult()), $response->getReasonCode());
            } else {
                $this->unsetSessionData($this->_paymentMethod);
                echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_request_error_else') . "</div>";
            }
        } catch(Exception $e) {
            $this->unsetSessionData($this->_paymentMethod);
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
    private function getContent($calcType, $calcValue, $amount, $reward = 0)
    {
        $content = [
            'InstallmentCalculation' => [
                'Amount' => $amount - $reward,
            ]
        ];

        if ($calcType == 'calculation-by-rate') {
            $content['InstallmentCalculation']['CalculationRate'] = ['Rate' => $calcValue];
        } elseif ($calcType == 'calculation-by-time') {
            $content['InstallmentCalculation']['CalculationTime'] = ['Month' => $calcValue];
        } else {
            echo "<div class='pirperror'>" . $this->__('lang_error') . ":<br/>" . $this->__('lang_wrong_value') . "</div>";
        }

        return $content;
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
    public function getHtml($result, $notification = null)
    {
        $this->_helperData->getRateResultHtml($result, $notification);
    }
}
