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

class RatePAY_Ratepaypayment_Block_Checkout_Installmentplan extends Mage_Checkout_Block_Agreements
{
    /**
     * Override block template
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_getCode() == "ratepay_rate") {
            $this->setTemplate('ratepay/checkout/installmentplan.phtml');
        }
        return parent::_toHtml();
    }

    public function showRateResultHtml() {
        Mage::helper('ratepaypayment')->getRateResultHtml($this->_getResult(), true);
    }

    private function _getResult() {
        return array(
            'amount' => Mage::getSingleton('checkout/session')->getRatepay_rate_amount(),
            'serviceCharge' => Mage::getSingleton('checkout/session')->getRatepay_rate_service_charge(),
            'annualPercentageRate' => Mage::getSingleton('checkout/session')->getRatepay_rate_annual_percentage_rate(),
            'interestRate' => Mage::getSingleton('checkout/session')->getRatepay_rate_interest_rate(),
            'interestAmount' => Mage::getSingleton('checkout/session')->getRatepay_rate_interest_amount(),
            'totalAmount' => Mage::getSingleton('checkout/session')->getRatepay_rate_total_amount(),
            'numberOfRatesFull' => Mage::getSingleton('checkout/session')->getRatepay_rate_number_of_rates_full(),
            'rate' => Mage::getSingleton('checkout/session')->getRatepay_rate_rate(),
            'lastRate' => Mage::getSingleton('checkout/session')->getRatepay_rate_last_rate()
        );
    }

    private function _getCode() {
        $quote = Mage::getModel('checkout/session')->getQuote();
        return $quote->getPayment()->getMethodInstance()->getCode();
    }
}