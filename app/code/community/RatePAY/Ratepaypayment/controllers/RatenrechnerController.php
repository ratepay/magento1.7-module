<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_RatenrechnerController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var RatePAY_Ratepaypayment_Helper_Data
     */
    private $_helperData;

    /**
     * @var RatePAY_Ratepaypayment_Helper_Mapping
     */
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

    /**
     * @param $calculationType
     * @param $calculationValue
     * @return mixed
     */
    private function callCalculationRequest($calculationType, $calculationValue)
    {
        $quote = $this->getQuote();
        $sandbox = (bool) $this->_helperData->getRpConfigData($quote, $this->_paymentMethod, 'sandbox');

        $request = Mage::getSingleton('ratepaypayment/libraryConnector', array($sandbox));
        $head = $this->_helperMapping->getRequestHead($quote, $this->_paymentMethod);
        $content = $this->getContent($calculationType, $calculationValue, $quote->getGrandTotal(), $this->_reward);

        return $request->callCalculationRequest($head, $content, $calculationType);
    }

    /**
     * Calculates the rates by from user defined rate
     */
    public function rateAction()
    {
        /** @var RatePAY_Ratepaypayment_Block_Checkout_InstallmentplanDetails $block */
        $block = Mage::getBlockSingleton('ratepaypayment/checkout_installmentplanDetails');

        try {
            if (is_numeric($this->_calcValue)) {
                $response = $this->callCalculationRequest('calculation-by-rate', floatval($this->_calcValue));

                if ($response->isSuccessful()) {
                    $this->setSessionData($response->getResult(), $this->_paymentMethod);

                    $block->setData('result', $this->formatResult($response->getResult()));
                    $block->setData('method', $this->_paymentMethod);
                    $block->setData('code', $response->getReasonCode());
                } else {
                    $this->unsetSessionData($this->_paymentMethod);
                    $block->_addError($this->__('lang_error'), $this->__('lang_request_error_else'));
                }
            } else {
                $this->unsetSessionData($this->_paymentMethod);
                $block->_addError($this->__('lang_error'), $this->__('lang_wrong_value'));
            }
        } catch(Exception $e) {
            $this->unsetSessionData($this->_paymentMethod);
            $block->_addError($this->__('lang_error'), $this->__('lang_server_off'));
        }

        $html = $block->renderView();
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/html', true)
            ->setBody($html);
        return;
    }

    /**
     * Calculates the rates by from user defined runtime
     */
    public function runtimeAction()
    {
        /** @var RatePAY_Ratepaypayment_Block_Checkout_InstallmentplanDetails $block */
        $block = Mage::getBlockSingleton('ratepaypayment/checkout_installmentplanDetails');

        try {
            $response = $this->callCalculationRequest('calculation-by-time', floatval($this->_calcValue));

            if ($response->isSuccessful()) {
                $this->setSessionData($response->getResult(), $this->_paymentMethod);

                $block->setData('result', $this->formatResult($response->getResult()));
                $block->setData('method', $this->_paymentMethod);
                $block->setData('code', $response->getReasonCode());
            } else {
                $this->unsetSessionData($this->_paymentMethod);
                $block->_addError($this->__('lang_error'), $this->__('lang_request_error_else'));
            }
        } catch(Exception $e) {
            $this->unsetSessionData($this->_paymentMethod);
            $block->_addError($this->__('lang_error'), $this->__('lang_server_off'));
        }

        $html = $block->renderView();
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/html', true)
            ->setBody($html);
        return;
    }

    /**
     * Calculates the rates by from user defined rate
     *
     * @param $calcType
     * @param float $calcValue
     * @param $amount
     * @param int $reward
     * @return array
     * @throws Exception
     */
    private function getContent($calcType, $calcValue, $amount, $reward = 0)
    {
        $content = array(
            'InstallmentCalculation' => array(
                'Amount' => $amount - $reward,
            )
        );

        if ($calcType == 'calculation-by-rate') {
            $content['InstallmentCalculation']['CalculationRate'] = array('Rate' => $calcValue);
        } elseif ($calcType == 'calculation-by-time') {
            $content['InstallmentCalculation']['CalculationTime'] = array('Month' => $calcValue);
        } else {
            throw new Exception($this->__('lang_wrong_value'));
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
        return Mage::getSingleton('checkout/session')->getQuote();
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
        $session = Mage::getSingleton('ratepaypayment/session');
        if ($session) {
            foreach ($session->getData() as $key => $value) {
                if (!is_array($value)) {
                    $sessionNameBeginning = substr($key, 0, strlen($paymentMethod));
                    if ($sessionNameBeginning == $paymentMethod && $key[strlen($paymentMethod)] == "_") {
                        $unsetFunction = "uns" . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($key);
                        $session->$unsetFunction();
                    }
                }
            }
        }
    }
}
