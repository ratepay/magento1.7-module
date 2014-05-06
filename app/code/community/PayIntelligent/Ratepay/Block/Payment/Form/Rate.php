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

class PayIntelligent_Ratepay_Block_Payment_Form_Rate extends PayIntelligent_Ratepay_Block_Payment_Form_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/form/rate.phtml');
    }

     /**
     * Return Grand Total Amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->getQuote()->getGrandTotal();
    }

    /**
     * Calls the CONFIGURATION_REQUEST to check which Months are allowed and returns them
     *
     * @return boolean|array
     */
    public function getMonthAllowed()
    {
        $paymentStep = Mage::getSingleton('checkout/session')->getStepData('payment');
        if(isset($paymentStep['allow']) && $paymentStep['allow'] == 1) {
            try {
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

                $client = Mage::getSingleton('ratepay/request');
                $helper = Mage::helper('ratepay/mapping');
                $result = $client->callConfigurationRequest($helper->getRequestHead($this->getQuote(),'', $this->getMethodCode()),$helper->getLoggingInfo($this->getQuote(),$this->getMethodCode()));
                if (is_array($result) || $result == true) {
                    return explode(',', $result['monthAllowed']);
                } else {
                    return array();
                }
            } catch(Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Calls the CONFIGURATION_REQUEST to check which Months are allowed and returns them
     *
     * @return boolean|array
     */
    public function getRatePreInfo()
    {
        $paymentStep = Mage::getSingleton('checkout/session')->getStepData('payment');
        if(isset($paymentStep['allow']) && $paymentStep['allow'] == 1) {
            try {
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

                $client = Mage::getSingleton('ratepay/request');
                $helper = Mage::helper('ratepay/mapping');
                return = $client->callConfigurationRequest($helper->getRequestHead($this->getQuote(),'', $this->getMethodCode()),$helper->getLoggingInfo($this->getQuote(),$this->getMethodCode()));

                if (is_array($result) || $result == true) {
                    $return['monthsAllowed'] =  explode(',', $result['monthAllowed']);
                } else {
                    $return['monthsAllowed'] = array();
                }
                $return['monthNumberMin'] = $result['monthNumberMin'];
                $return['monthNumberMax'] = $result['monthNumberMax'];
                $return['rateMinNormal'] = $result['rateMinNormal'];

                return $return;
            } catch(Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Is dynamic due
     * 
     * @return boolean 
     */
    public function isDynamicDue() 
    {
        return Mage::helper('ratepay/mapping')->isDynamicDue();
    }
    
    /**
     * Retrieve bank data from customer
     * 
     * @return array
     */
    public function getBankData()
    {
        return Mage::helper('ratepay')->getBankData();
    }
    
    /**
     * Is ELV for ratepay rate anabled
     * 
     * @return type 
     */
    public function isElvEnabled()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        return Mage::getStoreConfig('payment/ratepay_rate/elv_enabled', $quote->getStoreId());
    }
}