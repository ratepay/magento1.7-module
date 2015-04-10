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

class RatePAY_Ratepaypayment_Block_Payment_Form_Rate extends RatePAY_Ratepaypayment_Block_Payment_Form_Abstract
{
    protected $_code = 'ratepay_rate';

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/form/rate.phtml');
    }

     /**
     * Return Grand Total Amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->getQuote()->getGrandTotal();
    }

    /**
     * Calls the CONFIGURATION_REQUEST to check which Months are allowed and returns them
     *
     * @return boolean|array
     */
    public function getMonthAllowed()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote->getStoreId();
        $country = strtolower($quote->getBillingAddress()->getCountryId());

        return array(
            "month_allowed" => explode("," ,Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/month_allowed', $storeId)),
            "rate_min" => Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/rate_min', $storeId)
        );
    }

    /**
     * Is dynamic due
     * 
     * @return boolean 
     */
    public function isDynamicDue() 
    {
        return Mage::helper('ratepaypayment/mapping')->isDynamicDue();
    }
    
    /**
     * Retrieve bank data from customer
     * 
     * @return array
     */
    public function getBankData()
    {
        return Mage::helper('ratepaypayment')->getBankData();
    }
    
    /**
     * Is ELV for ratepay rate anabled
     * 
     * @return type 
     */
    public function isElvEnabled()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote->getStoreId();
        $country = strtolower($quote->getBillingAddress()->getCountryId());

        return Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/elv_enabled', $storeId);
    }
}