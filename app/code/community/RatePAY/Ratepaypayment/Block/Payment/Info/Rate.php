<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Info_Rate extends RatePAY_Ratepaypayment_Block_Payment_Info_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/info/rate.phtml');
    }

    /**
     * Returns the calculated rates
     * 
     * @return array
     */
    public function getRateData() {
        $result = array();
        $result['totalAmount']          = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Total Amount'));
        $result['amount']               = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Amount'));
        $result['interestRate']         = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Interest Rate'));
        $result['interestAmount']       = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Interest Amount'));
        $result['serviceCharge']        = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Service Charge'));
        $result['annualPercentageRate'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Annual Percentage Rate'));
        $result['monthlyDebitInterest'] = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Monthly Debit Interest'));
        $result['numberOfRatesFull']    = $this->getInfo()->getAdditionalInformation('Rate Number of Rates Full');
        $result['numberOfRates']        = $this->getInfo()->getAdditionalInformation('Rate Number of Rates');
        $result['rate']                 = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Rate'));
        $result['lastRate']             = Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Last Rate'));
        return $result;
    }

    /**
     * @return string
     */
    public function getResultHtml()
    {
        /** @var RatePAY_Ratepaypayment_Block_Adminhtml_Sales_InstallmentplanDetails $block */
        $block = Mage::getBlockSingleton('ratepaypayment/adminhtml_sales_installmentplanDetails');
        $block->setData('result', $this->getRateData());
        $block->setData('method', 'ratepay_rate');
        $block->setData('code', null);
        $html = $block->toHtml();

        return $html;
    }
}
