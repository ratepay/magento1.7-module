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
class RatePAY_Ratepaypayment_Helper_Mapping extends Mage_Core_Helper_Abstract
{

    var $_api = false;

    var $_backend = false;

    /**
     * Article preparations for PAYMENT_REQUEST, PAYMENT_CHANGE, CONFIRMATION_DELIVER
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $object
     * @return array
     */
    public function getArticles($object)
    {
        $basket['Items'] = [];
        $articleDiscountAmount = 0;
        $objectItems = $object->getAllItems();

        // Handle ordered articles
        foreach ($objectItems as $item) {
            if ($item instanceof Mage_Sales_Model_Order_Item ||
                $item instanceof Mage_Sales_Model_Quote_Item) {
                $orderItem = $item;
            } else {
                $orderItem = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            }

            $shopProduct = Mage::getModel('catalog/product')->load($orderItem->getProductId());

            if ((($orderItem->getProductType() !== 'bundle') || ($orderItem->getProductType() === 'bundle' && $shopProduct->getPrice() > 0)) && $orderItem->getRowTotal() > 0) {
                $article = [];
                $article['ArticleNumber'] = $item->getSku();
                $article['Description'] = $item->getName();
                $article['Quantity'] = ($object instanceof Mage_Sales_Model_Order) ? $item->getQtyOrdered() : $item->getQty();
                $article['UnitPriceGross'] = (float) $item->getPriceInclTax();
                $article['TaxRate'] = (float) $orderItem->getTaxPercent();

                if ($article['Quantity'] != (int) $article['Quantity']) {
                    $article['UnitPriceGross'] = (float) $item->getPriceInclTax() * $article['Quantity'];
                    $article['Quantity'] = 1;
                } else {
                    $article['Quantity'] = (int) $article['Quantity'];
                }

                if ($item->getDiscountAmount() > 0) {
                    $article['Discount'] = (float) $item->getDiscountAmount() / $article['Quantity'];
                    $articleDiscountAmount = $articleDiscountAmount + $item->getDiscountAmount();
                }

                $basket['Items'][]['Item'] = $article;
            }
        }

        // Handle order wrapping costs
        if($object->getGwPrice() > 0){
            $article = [];
            $article['ArticleNumber'] = 'orderwrapping';
            $article['Description'] = 'Wrapping Cost Order';
            $article['Quantity'] = 1;
            $article['UnitPriceGross'] = (float) $object->getGwPrice();
            $article['TaxRate'] = round((100 / $object->getGwPrice()) * $object->getGwTaxAmount());

            $basket['Items'][]['Item'] = $article;
        }

        // Handle item wrapping costs
        if($object->getGwItemsPrice() > 0){
            $article = [];
            $article['ArticleNumber'] = 'itemswrapping';
            $article['Description'] = 'Wrapping Cost Items';
            $article['Quantity'] = 1;
            $article['UnitPriceGross'] = (float) $object->getGwItemsPrice();
            $article['TaxRate'] = round((100 / $object->getGwItemsPrice()) * $object->getGwItemsTaxAmount());

            $basket['Items'][]['Item'] = $article;
        }

        // Handle printed card
        if($object->getGwAddCard() > 0){
            $article = [];
            $article['ArticleNumber'] = 'printed_card';
            $article['Description'] = 'Printed Card';
            $article['Quantity'] = 1;
            $article['UnitPriceGross'] = (float) $object->getGwCardPrice();
            $article['TaxRate'] = round((100 / $object->getGwCardPrice()) * $object->getGwCardTaxAmount());

            $basket['Items'][]['Item'] = $article;
        }

        // Handle gift card account
        if(Mage::getEdition() == 'Enterprise') {
            $_cards = Mage::getBlockSingleton('enterprise_giftcardaccount/checkout_cart_total')->getQuoteGiftCards();
            if ($_cards) {
                foreach ($_cards as $card) {
                    $article = [];
                    $article['ArticleNumber'] = 'gift_card';
                    $article['Description'] = $card['c'];
                    $article['Quantity'] = 1;
                    $article['UnitPriceGross'] = -1 * round($card['ba'], 2);
                    $article['TaxRate'] = 0;

                    $basket['Items'][]['Item'] = $article;
                }
            }
        }

        // Handle reward currency amount
        if($object->getRewardCurrencyAmount() > 0){
            $article = [];
            $article['ArticleNumber'] = 'REWARDPOINTS';
            $article['Description'] = 'Reward points';
            $article['Quantity'] = 1;
            $article['UnitPriceGross'] = -1 * $object->getRewardCurrencyAmount();
            $article['TaxRate'] = 0;

            $basket['Items'][]['Item'] = $article;
        }

        // Handle shipping costs
        if ($object instanceof Mage_Sales_Model_Order || $object instanceof Mage_Sales_Model_Order_Invoice || $object instanceof Mage_Sales_Model_Order_Creditmemo) {
            $shippingObject = $object;
        } else {
            $shippingObject = $object->getShippingAddress();
        }

        if ($shippingObject->getShippingAmount() > 0) {
            if ($object instanceof Mage_Sales_Model_Order_Invoice || $object instanceof Mage_Sales_Model_Order_Shipment || $object instanceof Mage_Sales_Model_Order_Creditmemo) {
                $shippingDiscountAmount = $shippingObject->getDiscountAmount() - $articleDiscountAmount;
                $shippingDescription = $object->getOrder()->getShippingDescription();
            } else {
                $shippingDiscountAmount = $shippingObject->getShippingDiscountAmount();
                $shippingDescription = $shippingObject->getShippingDescription();
            }

            $basket['Shipping'] = [];
            $basket['Shipping']['Description'] = $shippingDescription;
            $basket['Shipping']['UnitPriceGross'] = (float) $shippingObject->getShippingInclTax();
            if ($shippingDiscountAmount > 0 && $basket['Shipping']['UnitPriceGross'] >= $shippingDiscountAmount) {
                $basket['Shipping']['UnitPriceGross'] -= (float) $shippingObject->getShippingDiscountAmount();
            }
            $shippingTaxPercent = 0;
            if (($shippingObject->getShippingInclTax() - $shippingObject->getShippingAmount()) > 0) {
                $shippingTaxPercent = round((($shippingObject->getShippingInclTax() - $shippingObject->getShippingAmount()) * 100) / $shippingObject->getShippingAmount());
            }
            $basket['Shipping']['TaxRate'] = $shippingTaxPercent;

            if ((empty($this->_api) || $this->_api == false) && $this->_backend == true) {
                $article = [];
                $article['ArticleNumber'] = 'SHIPPING';
                $article['Description'] = $shippingDescription;
                $article['Quantity'] = 1;
                $article['UnitPriceGross'] = $basket['Shipping']['UnitPriceGross'];
                $article['TaxRate'] = 19;

                $basket['Items'][]['Item'] = $article;
                unset($basket['Shipping']);

            }
        }

        return $basket;
    }

    /**
     * Add adjustment items to the article list
     * 
     * @param Mage_Sales_Model_Creditmemo $creditmemo
     * @param array
     */
    public function addAdjustments($creditmemo)
    {
        $articles = [];

        if ($creditmemo->getAdjustmentPositive() > 0) {
            array_push($articles, ['Item' => $this->addAdjustment((float) $creditmemo->getAdjustmentPositive() * -1, 'Adjustment Refund', 'adj-ref')]);
        }

        if ($creditmemo->getAdjustmentNegative() > 0) {
            array_push($articles, ['Item' => $this->addAdjustment((float) $creditmemo->getAdjustmentNegative(), 'Adjustment Fee', 'adj-fee')]);
        }

        return $articles;
    }

    /**
     * Add merchant credit to artcile list
     * 
     * @param array $articles
     * @param float $amount
     * @return array
     */
    public function addAdjustment($amount, $description, $articleNumber)
    {
        $tempVoucherItem = [];
        $tempVoucherItem['Description'] = $description;
        $tempVoucherItem['ArticleNumber'] = $articleNumber;
        $tempVoucherItem['Quantity'] = 1;
        $tempVoucherItem['UnitPriceGross'] = $amount;
        $tempVoucherItem['TaxRate'] = 0;

        return $tempVoucherItem;
    }

    /**
     * Gets all needed Informations for the head Block of Requests for RatePAY
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quoteOrOrder
     * @param string $subtype
     * @param string $methodCode
     * @return array
     */
    public function getRequestHead($quoteOrOrder, $methodCode = null)
    {
        $h = $this->getHelper();

        $head = [];

        // Add credentials
        if (is_null($methodCode)) {
            $configProfileId    = $h->getRpConfigData($quoteOrOrder, $quoteOrOrder->getPayment()->getMethod(), 'profile_id');
            $configSecurityCode = $h->getRpConfigData($quoteOrOrder, $quoteOrOrder->getPayment()->getMethod(), 'security_code');
        } else {
            $configProfileId    = $h->getRpConfigData($quoteOrOrder, $methodCode, 'profile_id');
            $configSecurityCode = $h->getRpConfigData($quoteOrOrder, $methodCode, 'security_code');
        }

        $orderProfileId    = $quoteOrOrder->getPayment()->getAdditionalInformation('profileId');
        $orderSecurityCode = $quoteOrOrder->getPayment()->getAdditionalInformation('securityCode');

        $head['Credential']['ProfileId']     = (!empty($orderProfileId) && $orderProfileId <> $configProfileId) ? $orderProfileId : $configProfileId;
        $head['Credential']['Securitycode']  = (!empty($orderProfileId) && $orderProfileId <> $configProfileId) ? $orderSecurityCode : $configSecurityCode;

        // Add transaction id
        $trxId = $quoteOrOrder->getPayment()->getAdditionalInformation('transactionId');

        if ($quoteOrOrder->getPayment()->getAdditionalInformation('api') !== null) {
            $this->_api = $quoteOrOrder->getPayment()->getAdditionalInformation('api');
        }


        if (!empty($trxId)) {
            $head['TransactionId'] = $trxId;
        }

        // Add order id
        if ($quoteOrOrder instanceof Mage_Sales_Model_Order) {
            $head['External']['OrderId'] = $quoteOrOrder->getRealOrderId();
        } else {
            $head['External']['OrderId'] = $quoteOrOrder->getReservedOrderId();
        }

        // Add merchant consumer id
        if ($customerId = $quoteOrOrder->getCustomerId()) {
            $head['External']['MerchantConsumerId'] = $customerId;
        }

        return $head;
    }

    public function getRequestContent($quoteOrOrder, $operation, $articleList = null, $amount = null)
    {
        $content = [];

        switch ($operation) {
            case "PAYMENT_REQUEST" :
                $content['Customer'] = $this->getRequestCustomer($quoteOrOrder);
                $content['ShoppingBasket'] = $this->getRequestBasket($quoteOrOrder);
                $content['Payment'] = $this->getRequestPayment($quoteOrOrder, $amount);
                break;
            case "PAYMENT_CHANGE" :
                $this->_backend = true;
                $content['ShoppingBasket'] = $this->getRequestBasket($quoteOrOrder, $articleList, $amount);
                break;
            case "CONFIRMATION_DELIVER" :
                $this->_backend = true;
                $content['ShoppingBasket'] = $this->getRequestBasket($quoteOrOrder);
                break;
        }

        return $content;
    }

    /**
     * Gets all needed Informations for customer Block of the Request
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quoteOrOrder
     * @return array
     */
    public function getRequestCustomer($quoteOrOrder)
    {
        $customer = [];
        $contacts = [];
        $billing = [];
        $delivery = [];

        $locale = substr(Mage::app()->getLocale()->getLocaleCode(),0,2);
        if (empty($locale)) {
            $locale = substr(Mage::app()->getLocale()->getDefaultLocale(),0,2);
        }

        $dob = new Zend_Date($quoteOrOrder->getCustomerDob());
        $customer['DateOfBirth'] = $dob->toString("yyyy-MM-dd");
        $customer['Gender'] = $this->getHelper()->getGenderCode($quoteOrOrder);
        $customer['FirstName'] = $quoteOrOrder->getBillingAddress()->getFirstname();
        $customer['LastName'] = $quoteOrOrder->getBillingAddress()->getLastname();
        $customer['Language'] = strtolower($locale);
        $customer['IpAddress'] = Mage::helper('core/http')->getRemoteAddr(false);
        $customer['Nationality'] = $quoteOrOrder->getBillingAddress()->getCountryId();

        // B2B
        if (!empty($quoteOrOrder->getBillingAddress()->getCompany())) {
            $customer['Company'] = $quoteOrOrder->getBillingAddress()->getCompany();
            if (!empty($quoteOrOrder->getCustomerTaxvat())) {
                $customer['VatId'] = $quoteOrOrder->getCustomerTaxvat();
            }
        }

        // Contacts
        $contacts['Email'] = $quoteOrOrder->getCustomerEmail();
        $contacts['Phone']['DirectDial'] = $quoteOrOrder->getBillingAddress()->getTelephone();
        if ($quoteOrOrder->getBillingAddress()->getFax() != '') {
            $contacts['Fax']['DirectDial'] = $quoteOrOrder->getBillingAddress()->getFax();
        }
        $customer['Contacts'] = $contacts;

        // Addresses
        $billing['Type'] = "billing";
        // Different handling of street fields in case of NL orders
        $billingStreetFull = $quoteOrOrder->getBillingAddress()->getStreetFull();
        $billingStreet1 = $quoteOrOrder->getBillingAddress()->getStreet1();
        $billingStreet2 = $quoteOrOrder->getBillingAddress()->getStreet2();
        if ($quoteOrOrder->getBillingAddress()->getCountryId() == "NL" && !empty($billingStreet2)) {
            $billing['Street'] = $billingStreet1;
            $billing['StreetAdditional'] = $billingStreet2;
        } else {
            $billing['Street'] = preg_replace('~[\r\n]+~', ' ', $billingStreetFull);
        }
        $billing['ZipCode'] = $quoteOrOrder->getBillingAddress()->getPostcode();
        $billing['City'] = $quoteOrOrder->getBillingAddress()->getCity();
        $billing['CountryCode'] = $quoteOrOrder->getBillingAddress()->getCountryId();

        $delivery['Type'] = "delivery";
        $delivery['FirstName'] = $quoteOrOrder->getShippingAddress()->getFirstname();
        $delivery['LastName'] = $quoteOrOrder->getShippingAddress()->getLastname();

        // Different handling of street fields in case of NL orders
        $shippingStreetFull = $quoteOrOrder->getShippingAddress()->getStreetFull();
        $shippingStreet1 = $quoteOrOrder->getShippingAddress()->getStreet1();
        $shippingStreet2 = $quoteOrOrder->getShippingAddress()->getStreet2();
        if ($quoteOrOrder->getShippingAddress()->getCountryId() == "NL" && !empty($shippingStreet2)) {
            $delivery['Street'] = $shippingStreet1;
            $delivery['StreetAdditional'] = $shippingStreet2;
        } else {
            $delivery['Street'] = preg_replace('~[\r\n]+~', ' ', $shippingStreetFull);
        }
        $delivery['ZipCode'] = $quoteOrOrder->getShippingAddress()->getPostcode();
        $delivery['City'] = $quoteOrOrder->getShippingAddress()->getCity();
        $delivery['CountryCode'] = $quoteOrOrder->getShippingAddress()->getCountryId();
        if ($quoteOrOrder->getShippingAddress()->getCompany()) {
            $delivery['Company'] = $quoteOrOrder->getShippingAddress()->getCompany();
        }

        $customer['Addresses'] = [
            [
                'Address' => $billing
            ], [
                'Address' => $delivery
            ]
        ];

        if ($quoteOrOrder->getPayment()->getMethod() == 'ratepay_directdebit' || Mage::getSingleton('ratepaypayment/session')->getDirectDebitFlag() === true) {
            $customer['BankAccount'] = [];
            $customer['BankAccount']['Owner'] = $customer['FirstName'] . " " . $delivery['LastName'];

            if(Mage::getSingleton('ratepaypayment/session')->getIban()) {
                $customer['BankAccount']['Iban'] = Mage::getSingleton('ratepaypayment/session')->getIban();
            } else {
                $customer['BankAccount']['BankAccountNumber'] = Mage::getSingleton('ratepaypayment/session')->getAccountNumber();
                $customer['BankAccount']['BankCode'] = Mage::getSingleton('ratepaypayment/session')->getBankCodeNumber();
            }
        }

        return $customer;
    }

    /**
     * Gets all needed Informations for shopping-basket Block of the Request
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order|Mage_Sales_Model_Order_Invoice|Mage_Sales_Model_Order_Creditmemo $object
     * @param float $amount
     * @param array $articleList
     * @return array
     */
    public function getRequestBasket($object, $articleList = null, $amount = null)
    {
        $basket = [];

        if (is_null($articleList)) {
            $basket = $this->getArticles($object);

            // If no positiv item is remained in basket clear basket
            if (!$this->_anyPositiveItems($basket['Items'])) {
                $basket = [];
            }

            if (!is_null($amount)) {
                $basket['Amount'] = $amount;
            } elseif ($object->getTotalDue() > 0) {
                $basket['Amount'] = (float) $object->getTotalDue();
            } elseif ($object->getGrandTotal() > 0) {
                $basket['Amount'] = (float) $object->getGrandTotal();
            } // If Amount is not set it will be summed up by library

            // Ensure that the basket amount is never less than zero
            // In certain cases (i.e. in case of maloperation) the amount can become < 0
            if ($basket['Amount'] < 0) {
                $basket['Amount'] = 0;
            }
        } elseif (count($articleList) > 0) {
            $basket['Items'] = $articleList;
        } // If $articleList is set, the basket amount will be totalized by library

        $basket['Currency'] = ($object instanceof Mage_Sales_Model_Quote) ? $object->getQuoteCurrencyCode() : $object->getOrderCurrencyCode();

        return $basket;
    }

    /**
     * Check for any positive items
     *
     * @param array $items
     * @return boolean
     */
    public function _anyPositiveItems($items) {
        $anyPositiveItems = false;
        foreach ($items as $item) {
            if ($item['Item']['UnitPriceGross'] >= 0) {
                $anyPositiveItems = true;
            }
        }
        return $anyPositiveItems;
    }

    /**
     * Gets all needed Informations for payment Block of the Request
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @param float amount
     * @param string $request
     * @return array
     */
    public function getRequestPayment($object, $amount = null)
    {
        $paymentMethod = $object->getPayment()->getMethod();
        $payment = [];
        switch ($paymentMethod) {
            case 'ratepay_rechnung':
                $payment['Method'] = 'INVOICE';
                break;
            case 'ratepay_directdebit':
                $payment['Method'] = 'ELV';
                break;
            case 'ratepay_rate':
            case 'ratepay_rate0':
                $payment['Method'] = 'INSTALLMENT';

                $amount = Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'TotalAmount'}();

                // Add installment data
                $payment['InstallmentDetails']['InstallmentNumber'] = Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'NumberOfRatesFull'}();
                $payment['InstallmentDetails']['InstallmentAmount'] = Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'Rate'}();
                $payment['InstallmentDetails']['LastInstallmentAmount'] = Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'LastRate'}();
                $payment['InstallmentDetails']['InterestRate'] = Mage::getSingleton('ratepaypayment/session')->{'get' . Mage::helper('ratepaypayment')->convertUnderlineToCamelCase($paymentMethod) . 'InterestRate'}();

                // Differentiate between installment.invoice & installment.directdebit
                if (Mage::getSingleton('ratepaypayment/session')->getDirectDebitFlag()) {
                    //$payment['InstallmentDetails']['PaymentFirstDay'] = 2;
                    $payment['DebitPayType'] = 'DIRECT-DEBIT';
                } else {
                    //$payment['InstallmentDetails']['PaymentFirstDay'] = 28;
                    $payment['DebitPayType'] = 'BANK-TRANSFER';
                }
            break;
        }

        if (!is_null($amount)) {
            $payment['Amount'] = $amount;
        } else {
            $payment['Amount'] = $object->getGrandTotal();
        }

        // Ensure that the basket amout is never less than zero
            // In certain cases (i.e. in case of maloperation) the amount can become < 0
        if ($payment['Amount'] < 0) {
            $payment['Amount'] = 0;
        }

        return $payment;
    }

    /**
     * Returns the payment method helper
     *
     * @return RatePAY_Ratepaypayment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('ratepaypayment');
    }

}
