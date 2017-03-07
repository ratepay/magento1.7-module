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
    public function getMonthAllowed()
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
            "month_allowed" => $allowedRuntimes,
            "rate_min" => $rateMinNormal
        );
    }
}
