<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class RatePAY_Ratepaypayment_Block_Payment_Form_RateAbstract extends RatePAY_Ratepaypayment_Block_Payment_Form_DirectdebitAbstract
{

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
     * Is invoice for ratepay rate allowed
     *
     * @return bool
     * @throws Mage_Core_Model_Store_Exception
     */
    public function isInvoiceAllowed()
    {
        if(Mage::app()->getStore()->isAdmin()){
            $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $storeId = $quote->getStoreId();
        $country = strtolower($quote->getBillingAddress()->getCountryId());

        //$return = (bool) Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/rate_invoice', $storeId);
        return (bool) Mage::getStoreConfig('payment/ratepay_rate_' . $country . '/rate_invoice', $storeId);
    }
}
