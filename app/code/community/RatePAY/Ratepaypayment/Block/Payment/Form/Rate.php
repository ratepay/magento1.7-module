<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Form_Rate extends RatePAY_Ratepaypayment_Block_Payment_Form_RateAbstract
{
    protected $_code = 'ratepay_rate';

    /*protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/form/rate.phtml');
    }*/

    /**
     * Calls the CONFIGURATION_REQUEST to check which months are allowed and returns them
     *
     * @return boolean|array
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

        $basketAmount = (float) $this->getAmount();
        $rateMinNormal = Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/rate_min', $storeId);
        $runtimes = explode(",",Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/month_allowed', $storeId));
        $interestrateMonth = ((float) Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/interestrate_default', $storeId) / 12) / 100;

        $allowedRuntimes = array();

        foreach ($runtimes as $runtime){
            $rateAmount = ceil($basketAmount * (($interestrateMonth * pow((1 + $interestrateMonth), $runtime)) / (pow((1 + $interestrateMonth), $runtime) - 1)));

            if($rateAmount >= $rateMinNormal){
                $allowedRuntimes[] = $runtime;
            }
        }
        return array(
            "monthAllowed" => $allowedRuntimes,
            "rateMin" => $rateMinNormal
        );
    }
}
