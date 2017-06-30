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

class RatePAY_Ratepaypayment_Block_Payment_Form_Rate0 extends RatePAY_Ratepaypayment_Block_Payment_Form_RateAbstract
{
    protected $_code = 'ratepay_rate0';

    /**
     * Calls the CONFIGURATION_REQUEST to check which month is allowed and returns it
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

        return array(
            "monthAllowed" => explode(",", Mage::getStoreConfig('payment/ratepay_rate0_' . $country . '/month_allowed', $storeId)),
            "rateMin" => Mage::getStoreConfig('payment/ratepay_rate0_' . $country . '/rate_min', $storeId)
        );
    }
}
