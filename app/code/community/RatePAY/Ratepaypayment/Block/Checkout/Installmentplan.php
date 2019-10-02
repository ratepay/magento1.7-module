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
