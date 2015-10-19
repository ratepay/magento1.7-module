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

class RatePAY_Ratepaypayment_Block_Checkout_Installmentplan extends Mage_Core_Block_Template
{
    /**
     * Show installment plan
     */
    public function showRateResultHtml() {
        Mage::helper('ratepaypayment')->getRateResultHtml($this->_getResult(), false);
    }

    /**
     * Returns the session saved installment plan
     * @return array
     */
    private function _getResult() {
        return array(
            'amount' =>               Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_amount()),
            'serviceCharge' =>        Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_service_charge()),
            'annualPercentageRate' => Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_annual_percentage_rate()),
            'interestRate' =>         Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_interest_rate()),
            'interestAmount' =>       Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_interest_amount()),
            'totalAmount' =>          Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_total_amount()),
            'numberOfRatesFull' =>    Mage::getSingleton('checkout/session')->getRatepay_rate_number_of_rates_full(),
            'rate' =>                 Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_rate()),
            'lastRate' =>             Mage::helper('ratepaypayment')->formatPriceWithoutCurrency(Mage::getSingleton('checkout/session')->getRatepay_rate_last_rate())
        );
    }

    /**
     * Returns the current method code
     * @return string
     */
    public function getRatepayMethodCode() {
        $quote = Mage::getModel('checkout/session')->getQuote();
        return $quote->getPayment()->getMethodInstance()->getCode();
    }
}