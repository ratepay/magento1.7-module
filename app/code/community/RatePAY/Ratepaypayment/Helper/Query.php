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
class RatePAY_Ratepaypayment_Helper_Query extends Mage_Core_Helper_Abstract
{

    /**
     * product names to method names
     *
     * @var array
     */
    private $products2Methods = array("INVOICE" => "ratepay_rechnung",
                                      "INSTALLMENT" => "ratepay_rate",
                                      "ELV" => "ratepay_directdebit",
                                      "PREPAYMENT" => "ratepay_vorkasse");

    /**
     * Returns the payment method helper
     *
     * @return RatePAY_Ratepaypayment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('ratepaypayment');
    }

    /**
     * Checks if PQ is activated and selected for the method
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isPaymentQueryActive($quote)
    {
        return $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'active') && $this->getQuerySubType($quote);
    }

    /**
     * Valids if conditions for PQ are complied
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function validation($quote)
    {
        $helper_data = Mage::helper('ratepaypayment/data');

        if (!$helper_data->isDobSet($quote) || $helper_data->isValidAge($quote->getCustomerDob()) != "success") {
            return false;
        }
        if (!$helper_data->isPhoneSet($quote)) {
            return false;
        }

        if ($quote->getBillingAddress()->getCompany() && !(bool) $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'b2b')) {
            return false;
        }
        if ($this->_differentAddresses($quote->getBillingAddress(), $quote->getShippingAddress()) && !(bool) $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'delivery')) {
            return false;
        }

        $totalAmount = $quote->getGrandTotal();
        $minAmount = $this->_getOuterLimitMin($quote);
        $maxAmount = $this->_getOuterLimitMax($quote);

        if ($totalAmount < $minAmount || $totalAmount > $maxAmount) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the current and previous quote are different
     *
     * @param Mage_Sales_Model_Quote $currentQuote, $previousQuote
     * @return boolean
     */
    public function relevantOrderChanges($currentQuote, $previousQuote)
    {
        if ($currentQuote['basket']['amount'] <> $previousQuote['basket']['amount']) {
            return true;
        }

        if ($currentQuote['customer']['firstname'] != $previousQuote['customer']['firstname']) {
            return true;
        }
        if ($currentQuote['customer']['lastname'] != $previousQuote['customer']['lastname']) {
            return true;
        }
        if ($currentQuote['customer']['dob'] != $previousQuote['customer']['dob']) {
            return true;
        }

        if ($currentQuote['customer']['billing'] != $previousQuote['customer']['billing']) {
            return true;
        }
        if ($currentQuote['customer']['shipping'] != $previousQuote['customer']['shipping']) {
            return true;
        }

        return false;
    }

    /**
     * Returns required sub type
     *
     * @param array Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getQuerySubType($quote)
    {
        return 'full';

        $subType = false;

        $status =       $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'b2c');
        $b2b =          $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'b2b');
        $b2c_delivery = $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'delivery_address_b2c');
        $b2b_delivery = $this->getHelper()->getRpConfigData($quote, 'ratepay_ibs', 'delivery_address_b2b');

        if ($quote->getBillingAddress()->getCompany()) {
            if ($this->_differentAddresses($quote->getBillingAddress(), $quote->getShippingAddress())) {
                $subType = $b2b_delivery;
            } else {
                $subType = $b2b;
            }
        } else {
            if ($this->_differentAddresses($quote->getBillingAddress(), $quote->getShippingAddress())) {
                $subType = $b2c_delivery;
            } else {
                $subType = $b2c;
            }
        }

        return (empty($subType)) ? false : $subType;
    }

    /**
     * Extracts allowed products from the response xml (associative) and returns an simple array
     *
     * @param array $result_products
     * @return array
     */
    public function getProducts(array $result_products)
    {
        if (empty($result_products) ||
            !is_array($result_products) ||
            count($result_products) == 0) {
            return false;
        }

        $products = array();
        foreach ($result_products as $element) {
            $products[] = $this->products2Methods[(string) $element->attributes()->{'method'}];
        }

        return $products;
    }

    /**
     * Checks if the billing and shipping addresses are different (just for checking changes in the postal address, not in the customer information)
     *
     * @param Mage_Sales_Model_Quote_Address $billingAddress, $shippingAddress
     * @return boolean
     */
    private function _differentAddresses($billingAddress, $shippingAddress) {
        if ($billingAddress->getFirstname() != $shippingAddress->getFirstname()) {
            return true;
        }
        if ($billingAddress->getLastname() != $shippingAddress->getLastname()) {
            return true;
        }
        if ($billingAddress->getStreetFull() != $shippingAddress->getStreetFull()) {
            return true;
        }
        if ($billingAddress->getPostcode() != $shippingAddress->getPostcode()) {
            return true;
        }
        if ($billingAddress->getCity() != $shippingAddress->getCity()) {
            return true;
        }

        return false;
    }

    /**
     * Returns the lowest amount limit (of all RatePAY payment methods)
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    private function _getOuterLimitMin($quote) {
        foreach ($this->products2Methods AS $product => $method) {
            if ($this->getHelper()->getRpConfigData($quote, $method, 'active') == "1") {
                if (!isset($outerLimitMin) || $outerLimitMin > $this->getHelper()->getRpConfigData($quote, $method, 'min_order_total')) {
                    $outerLimitMin = $this->getHelper()->getRpConfigData($quote, $method, 'min_order_total');
                }
            }
        }

        return $outerLimitMin;
    }

    /**
     * Returns the highest amount limit (of all RatePAY payment methods)
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return int
     */
    private function _getOuterLimitMax($quote) {
        foreach ($this->products2Methods AS $product => $method) {
            if ($this->getHelper()->getRpConfigData($quote, $method, 'active') == "1") {
                if (!isset($outerLimitMax) || $outerLimitMax < $this->getHelper()->getRpConfigData($quote, $method, 'max_order_total')) {
                    $outerLimitMax = $this->getHelper()->getRpConfigData($quote, $method, 'max_order_total');
                }
            }
        }

        return $outerLimitMax;
    }

}