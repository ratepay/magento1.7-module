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

class PayIntelligent_Ratepay_Block_Payment_Info_Rate extends PayIntelligent_Ratepay_Block_Payment_Info_Abstract
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
     * Render as PDF
     *
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('ratepay/payment/info/rate_pdf.phtml');
        return $this->toHtml();
    }

    /**
     * Returns the calculated rates
     * 
     * @return array
     */
    public function getRateData() {
        $result = array();
        $result['totalAmount'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Total Amount'));
        $result['amount'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Amount'));
        $result['interestRate'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Interest Rate'));
        $result['interestAmount'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Interest Amount'));
        $result['serviceCharge'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Service Charge'));
        $result['annualPercentageRate'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Annual Percentage Rate'));
        $result['monthlyDebitInterest'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Monthly Debit Interest'));
        $result['numberOfRatesFull'] = $this->getInfo()->getAdditionalInformation('Rate Number of Rates Full');
        $result['numberOfRates'] = $this->getInfo()->getAdditionalInformation('Rate Number of Rates');
        $result['rate'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Rate'));
        $result['lastRate'] = Mage::helper('ratepay')->formatPriceWithoutCurrency($this->getInfo()->getAdditionalInformation('Rate Last Rate'));
        return $result;
    }
    
    /**
     * Rate result render wrapper
     */
    public function getResultHtml($admin = false)
    {
        Mage::helper('ratepay')->getRateResultHtml($this->getRateData(), $admin);
    }
}
