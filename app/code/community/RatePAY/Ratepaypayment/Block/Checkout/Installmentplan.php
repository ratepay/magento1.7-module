<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Checkout_Installmentplan extends Mage_Core_Block_Template
{
    /**
     * Returns the session saved installment plan
     * @return array
     */
    public function getDetails() {
        $paymentMethod = $this->getRatepayMethodCode();
        $returnArr = array();
        foreach (Mage::getSingleton('ratepaypayment/session')->getData() as $key => $value) {
            if (!is_array($value)) {
                $sessionNameBeginning = substr($key, 0, strlen($paymentMethod)); // session variable name prefix = payment method
                if ($sessionNameBeginning == $paymentMethod && $key[strlen($paymentMethod)] == "_") { // if session variable belongs to current payment method
                    $shortKey = lcfirst(Mage::helper('ratepaypayment')->convertUnderlineToCamelCase(substr($key, strlen($paymentMethod)))); // use postfix as array key
                    $returnArr[$shortKey] = (!strstr($shortKey, "number")) ? Mage::helper('ratepaypayment')->formatPriceWithoutCurrency($value) : $value; // change format to readable except for number of months
                }
            }
        }
        return $returnArr;
    }

    /**
     * Returns the current method code
     * @return string|null
     */
    public function getRatepayMethodCode() {
        $quote = Mage::getModel('checkout/session')->getQuote();
        return $quote->getPayment()->getMethodInstance()->getCode();
    }
}
