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

abstract class RatePAY_Ratepaypayment_Model_Method_RateAbstract extends RatePAY_Ratepaypayment_Model_Method_Abstract
{
    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     * @return RatePAY_Ratepaypayment_Model_Method_RateAbstract
     */
    public function assignData($data)
    {
        parent::assignData($data);
        parent::assignBankData($data);

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

        Mage::getSingleton('ratepaypayment/session')->getRatepayRateTotalAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateInterestRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateInterestAmount() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateServiceCharge() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateAnnualPercentageRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateMonthlyDebitInterest() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateNumberOfRatesFull() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateNumberOfRates() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";
        Mage::getSingleton('ratepaypayment/session')->getRatepayRateLastRate() == null ? Mage::throwException($this->_getHelper()->__('Berechnen Sie Ihre Raten!')) : "";

        return $this;
    }

    /**
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST.
     *
     * @param   Varien_Object $orderPayment
     * @param   float $amount
     * @return  RatePAY_Ratepaypayment_Model_Method_Rate
     */
    public function authorize(Varien_Object $payment, $amount = 0)
    {
        parent::authorize($payment, (float) Mage::getSingleton('ratepaypayment/session')->getRatepayRateTotalAmount());

        return $this;
    }

}

