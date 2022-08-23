<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Form_Rate0 extends RatePAY_Ratepaypayment_Block_Payment_Form_RateAbstract
{
    protected $_code = 'ratepay_rate0';

    /**
     * Calls the CONFIGURATION_REQUEST to check which month is allowed and returns it
     *
     * @return boolean|array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getInstallmentCalculationData()
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $storeId = $quote->getStoreId();
        $country = strtolower($quote->getBillingAddress()->getCountryId());

        return array(
            "monthAllowed" => explode(",", Mage::getStoreConfig('payment/ratepay_rate0_' . $country . '/month_allowed', $storeId)),
            "rateMin" => Mage::getStoreConfig('payment/ratepay_rate0_' . $country . '/rate_min', $storeId)
        );
    }
}
