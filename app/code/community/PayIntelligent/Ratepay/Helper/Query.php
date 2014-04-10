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
class PayIntelligent_Ratepay_Helper_Query extends Mage_Core_Helper_Abstract
{

    /**
     * Extracts allowed products from the response xml (associative) and returns an simple array
     *
     * @param array $result_products
     * @return array/boolean
     */
    public function isPaymentQueryActive($quote)
    {
        return Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethod() . '/active', $quote->getStoreId());
    }

    /**
     * Extracts allowed products from the response xml (associative) and returns an simple array
     *
     * @param array $result_products
     * @return array/boolean
     */
    public function getQuerySubType($quote)
    {
        // Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethod() . '/b2c', $quote->getStoreId());
        // Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethod() . '/b2b', $quote->getStoreId());
        // Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethod() . '/delivery_address_b2c', $quote->getStoreId());
        // Mage::getStoreConfig('payment/' . $quote->getPayment()->getMethod() . '/delivery_address_b2b', $quote->getStoreId());

        // $quote->getCustomerXYZ()

        return 'full';
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
            $products[] = (string) $element->attributes()->{'method'};
        }

        return $products;
    }

}