<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

        $helper = Mage::helper('ratepaypayment');

        // @ToDo: reduce values
        $necessaryValues = array(
            'TotalAmount',
            'Amount',
            'InterestRate',
            'InterestAmount',
            'ServiceCharge',
            'AnnualPercentageRate',
            'MonthlyDebitInterest',
            'NumberOfRatesFull',
            'NumberOfRates',
            'Rate',
            'LastRate'
        );

        foreach ($necessaryValues as $key) {
            if (is_null(Mage::getSingleton('ratepaypayment/session')->{'get' . $helper->convertUnderlineToCamelCase($this->getCode()) . $key}())) {
                Mage::throwException($helper->__('Berechnen Sie Ihre Raten!'));
            }
        }

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

