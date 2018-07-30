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

class RatePAY_Ratepaypayment_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getRpConfigData($quoteOrOrder, $method, $field, $advanced = false, $noCountry = false)
    {
        $storeId = $quoteOrOrder->getStoreId();
        $country = strtolower($quoteOrOrder->getBillingAddress()->getCountryId());

        $dataset = $method;
        if ($advanced !== false) {
            $dataset .= '_advanced';
        }
        if ($noCountry !== true) {
            $dataset .= '_' . $country;
        }

        $path = 'payment/'. $dataset . '/' . $field;
        $result = Mage::getStoreConfig($path, $storeId);
        return $result;
    }

    /**
     * Check if phone number complies conditions
     *
     * @param string $phone
     * @return bool
     */
    public function isValidPhone($phone) {
        $valid = "/^[\d\s\/\(\)-+]/";
        if (strlen(trim($phone)) >= 6 && preg_match($valid, trim($phone))) {
            return true;
        }
        return false;
    }

    /**
     * Checks if a phonenumber is set to the customer
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return boolean
     */
    public function isPhoneSet($quote)
    {
        return $quote->getBillingAddress()->getTelephone() != '';
    }

    /**
     * Retrieve phonenumber from the quote or order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return String
     */
    public function getPhone($quote)
    {
        return $quote->getBillingAddress()->getTelephone();
    }

    /**
     * Sets the Phone Number into Quote or Order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param String $phone
     */
    public function setPhone($quote, $phone)
    {
        $quote->getBillingAddress()->setTelephone($phone)->save();
        $quote->getShippingAddress()->setTelephone($phone)->save();
        $customerAddressId = $quote->getBillingAddress()->getCustomerAddressId();
        if ($customerAddressId) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            $customerAddress->setTelephone($phone)->save();
        }
    }

    /**
     * Return Storename
     *
     * @return string
     */
    public function getStoreName()
    {
        return Mage::getStoreConfig('general/store_information/name', $this->getQuote()->getStoreId());
    }

    /**
     * Returns the Quote Object
     *
     * @return Mage_Sales_Model_Quote
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getQuote()
    {
        if(Mage::app()->getStore()->isAdmin()){
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else {
            return Mage::getSingleton('checkout/session')->getQuote();
        }
    }

    /**
     * Check if customer is 18 years old or older and less then 125 years
     *
     * @param string $dob
     * @return string
     */
    public function isValidAge($dob)
    {
        $customerDob = new Zend_Date($dob); // Zend_Date::ISO_8601
        if (!Zend_Date::isDate($customerDob)) {
            return 'wrongdate';
        }
        $currentDate = new Zend_Date(Mage::getModel('core/date')->timestamp(time()), Zend_Date::TIMESTAMP);
        $minDob = clone $currentDate;
        $minDob->subYear(18);
        $maxDob = clone $currentDate;
        $maxDob->subYear(125);

        if(!$customerDob->isEarlier($minDob)) {
            return 'young';
        } else if(!$customerDob->isLater($maxDob)) {
            return 'old';
        } else {
            return 'success';
        }
    }

    /**
     * Checks if Day of Birth is set to the customer
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return boolean
     */
    public function isDobSet($quote)
    {
        $dob = $quote->getCustomerDob();
        return $dob != '';
    }

    /**
     * Gets the Day of Birth from the Quote or Order if guest, else from the customer
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return String
     */
    public function getDob($quote)
    {
        return $quote->getCustomerDob();
    }

    /**
     * Sets the Day of Birth into the customer if not guest and always into the Quote/Order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param Zend_Date $dob
     * @throws Mage_Core_Model_Store_Exception
     */
    public function setDob($quote, $dob)
    {
        if ($quote->getCustomerId()) {
            $quote->getCustomer()
                ->setDob($dob->toString("yyyy-MM-dd HH:mm:ss"))
                ->save();
        }
        if (Mage::app()->getStore()->isAdmin()){
            Mage::getSingleton('ratepaypayment/session')->setCustomerDob($dob->toString("yyyy-MM-dd HH:mm:ss"));
        }
        $quote->setCustomerDob($dob->toString("yyyy-MM-dd HH:mm:ss"))->save();
    }

    /**
     * This method returns the customer gender code
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return string
     */
    public function getGenderCode($quote)
    {
        $gender = $quote->getCustomerGender();
        if ($gender) {
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('customer', 'gender');
            $option = $attribute->getFrontend()->getOption($gender);

            switch (strtolower($option)) {
                case 'male':
                    return 'M';
                case 'female':
                    return 'F';
            }
        }

        $gender = $quote->getCustomerPrefix();
        if ($gender) {
            switch (strtolower($gender)) {
                case 'herr':
                case 'mr':
                    return 'M';
                case 'frau':
                case 'mrs':
                    return 'F';
            }
        }
        return 'U';
    }

    /**
     * Gets the country id from the quote
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @return String
     */
    public function getCountryCode($quote)
    {
        return strtoupper($quote->getBillingAddress()->getCountryId());
    }

    /**
     * Sets the vat id into the customer if not guest and always into the Quote/Order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param string $taxvat
     * @throws Exception
     */
    public function setTaxvat($quote, $taxvat)
    {
        if ($quote->getCustomerId()) {
            $quote->getCustomer()
                ->setTaxvat($taxvat)
                ->save();
        }
        $quote->setCustomerTaxvat($taxvat)->save();
    }

    /**
     * Check if the vat id is valid
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param string
     * @return bool
     */
    public function isValidTaxvat($quote, $taxvat)
    {
        switch (strtoupper($quote->getBillingAddress()->getCountryId())) {
            case "DE": $valid = "<^((DE)?[0-9]{9})$>"; break;
            case "AT": $valid = "<^((AT)?U[0-9]{8})$>"; break;
            case "NL": $valid = "<^((NL)?[0-9]{9}?(B)[0-9]{2})$>"; break;
            case "CH": $valid = "<^((CHE)?[0-9]{9}(MWST))$>"; break;
            case "BE": $valid = "<^((BE)?[0-9]{9}?(B)[0-9]{2})$>"; break;
        }

        if (preg_match($valid, trim(strtoupper($taxvat)))) {
            return true;
        }
        return false;
    }

    /**
     * Sets the company into the customer if not guest and always into the Quote/Order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param string $company
     * @throws Exception
     */
    public function setCompany($quote, $company)
    {
        $quote->getBillingAddress()->setCompany($company)->save();
        $quote->getShippingAddress()->setCompany($company)->save();
        $customerAddressId = $quote->getBillingAddress()->getCustomerAddressId();
        if ($customerAddressId) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            $customerAddress->setCompany($company)->save();
        }
    }

    /**
     * Check if company is set
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param string
     * @return string
     */
    public function isCompanySet($quote)
    {
        return $quote->getBillingAddress()->getCompany();
    }

    /**
     * We have to diff the addresses, because same_as_billing is sometimes wrong
     *
     * @param unknown_type $address
     * @return array
     */
    public function getImportantAddressData($address)
    {
        $result = array();
        $result['city'] = trim($address->getCity());
        $result['company'] = trim($address->getCompany());
        $result['prefix'] = $address->getPrefix();
        $result['gender'] = $address->getGender();
        $result['firstname'] = $address->getFirstname();
        $result['lastname'] = $address->getLastname();
        $result['street'] = $address->getStreetFull();
        $result['postcode'] = $address->getPostcode();
        $result['region'] = $address->getRegion();
        $result['region_id'] = $address->getRegionId();
        $result['country_id'] = $address->getCountryId();
        return $result;
    }

    /**
     * Formats Prices without Currency Symbol
     * 
     * @param int|float $value
     * @return string
     */
    public function formatPriceWithoutCurrency($value) {
        return Mage::getModel('directory/currency')->format($value, array('display' => Zend_Currency::NO_SYMBOL), false);
    }
    
    /**
     * Set the bank data into the session/db
     * 
     * @param array $data
     * @param string $code
     */
    public function setBankData($data, $code)
    {
        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(true);
        $this->_setBankDataSession($data, $code);
        Mage::getSingleton('ratepaypayment/session')->setBankdataAfter(false);

    }
    
    private function _setBankDataSession($data, $code)
    {
        if (isset($data[$code . '_iban']) && $data[$code . '_iban']) {
            Mage::getSingleton('ratepaypayment/session')->setIban($data[$code . '_iban']);
        } else {
            Mage::getSingleton('ratepaypayment/session')->setAccountNumber($data[$code . '_account_number']);
            Mage::getSingleton('ratepaypayment/session')->setBankCodeNumber($data[$code . '_bank_code_number']);
        }
    }

    /**
     * Retrieve due days
     *
     * @param $payment
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getDueDays($payment)
    {
        $order = $this->getOrderByIncrementId($payment['orderId']);
        $code = $order->getPayment()->getMethodInstance()->getCode();
        if (strstr($code, "ratepay_rate")) {
            $data = "";
        } else {
            $data = $this->getRpConfigData($order, $code, 'due_days');
        }
        return $data;
    }
    
    /**
     * Retrieve order object by increment id
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getOrderByIncrementId($id)
    {
        return Mage::getModel('sales/order')->loadByIncrementId($id);
    }
    
    /**
     * Is object a instance of Mage_Sales_Model_Quote
     * 
     * @param mixed $object
     * @return boolean 
     */
    public function isQuote($object)
    {
        return $object instanceof Mage_Sales_Model_Quote;
    }
    
    /**
     * Is object a instance of Mage_Sales_Model_Order
     * 
     * @param mixed $object
     * @return boolean 
     */
    public function isOrder($object)
    {
        return $object instanceof Mage_Sales_Model_Order;
    }
    
    /**
     * Is object a instance of Mage_Sales_Model_Order_Invoice
     *
     * @param mixed $object
     * @return boolean
     */
    public function isInvoice($object)
    {
        return $object instanceof Mage_Sales_Model_Order_Invoice;
    }

    /**
     * Is object a instance of Mage_Sales_Model_Order_Creditmemo
     *
     * @param mixed $object
     * @return boolean
     */
    public function isCreditmemo($object)
    {
        return $object instanceof Mage_Sales_Model_Order_Creditmemo;
    }

    /**
     * Retrieve Magento edition
     * 
     * @return string 
     */
    public function getEdition()
    {
        $edition = 'CE';
        if (file_exists(Mage::getBaseDir() . '/LICENSE_EE.txt')) {
            $edition = 'EE';
        } else if (file_exists(Mage::getBaseDir() . '/LICENSE_PRO.html')) {
            $edition = 'PE';
        }
        return $edition;
    }

    public function isEnterpriseEdition()
    {
        return 'EE' === $this->getEdition();
    }

    public function isCommunityEdition()
    {
        return 'CE' === $this->getEdition();
    }

    public function isProfessionalEdition()
    {
        return 'PE' === $this->getEdition();
    }

    public function getCurrencyAmountForRewardPoints()
    {
        $usesRewardPoints = (Mage::getBlockSingleton('enterprise_reward/checkout_payment_additional')
            ->getCanUseRewardPoints());
        if (!$usesRewardPoints) {
            return 0;
        }

        return Mage::getBlockSingleton('enterprise_reward/checkout_payment_additional')
            ->getCurrencyAmount();
    }
    
    /**
     * Is order installment
     * 
     * @param integer $orderId
     * @return boolean 
     */
    public function isInstallment($orderId)
    {
        return (bool) strstr(Mage::getModel('sales/order')->loadByIncrementId($orderId)->getPayment()->getMethodInstance()->getCode(), "ratepay_rate");
    }


    /**
     * Converts names separated by underlines to camel case
     *
     * @param $paymentMethod
     * @return mixed|string
     */
    public function convertUnderlineToCamelCase($paymentMethod) {
        $paymentMethod = str_replace("_", " ", $paymentMethod);
        $paymentMethod = ucwords($paymentMethod);
        $paymentMethod = str_replace(" ", "", ucwords($paymentMethod));
        return $paymentMethod;
    }

    /**
     * Render the Rate calculator result html
     *
     * @param $result
     * @param $notification
     * @param $method
     */
    public function getRateResultHtml($result, $notification, $method)
    {
        echo '
        <style>
            .rp-installment-plan-details:hover #totalAmount { display: block; }
            .rp-installment-plan-no-details:hover #rate2 { display: block; }
            .rp-installment-plan-details:hover #lastRate { display: block; }
            .rp-installment-plan-details:hover #rate { display: block; }
            .rp-installment-plan-details:hover #interestAmount { display: block; }
            .rp-installment-plan-details:hover #interestRate { display: block; }
            .rp-installment-plan-details:hover #annualPercentageRate { display: block; }
            .rp-installment-plan-details:hover #serviceCharge { display: block; }
            .rp-installment-plan-details:hover #amount { display: block; }
            #rp-hide-installment-plan-details_' . $method . ' { display: none; }
            #rp-show-installment-plan-details_' . $method . ' { display: block; }
            #rp-installment-plan-details_' . $method . ' { display: none; }
            #rp-installment-plan-no-details_' . $method . ' { display: block; }
        </style>';

        echo '
            <div class="rp-table-striped">
                <div>
                    <div class="text-center text-uppercase" colspan="2">
                        ' .  $this->__('rp_personal_calculation') . '
                </div>
            </div>';
        if (!is_null($notification)) echo '
            <div>
                <div class="warning small text-center" colspan="2">
                    ' . $this->__('rp_reason_code_translation_' . $notification) . '
                    <br/>
                </div>
            </div>';


        echo '
            <div class="rp-menue">
                <div colspan="2" class="small text-right">
                    <a class="rp-link" id="rp-show-installment-plan-details_' . $method . '" onclick="changeDetails(\'' . $method . '\')">
                        Zeige Details
                        <img src="' . Mage::getDesign()->getSkinUrl('images/ratepay/icon-enlarge.png') . '" class="rp-details-icon" />
                    </a>
                    <a class="rp-link" id="rp-hide-installment-plan-details_' . $method . '" onclick="changeDetails(\'' . $method . '\')">
                        Schließe Details
                        <img src="' . Mage::getDesign()->getSkinUrl('images/ratepay/icon-shrink.png') . '" class="rp-details-icon" />
                    </a>
                </div>
            </div>
            <div id="rp-installment-plan-details_' . $method . '">
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_cash_payment_price') . '
                        <p id="amount" class="rp-installment-plan-description small">
                            ' . $this->__('rp_mouseover_cash_payment_price') . '
                        </p>
                    </div>
                    <div class="text-right">
                        ' . $result['amount'] . ' &euro;
                    </div>
                </div>
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_service_charge') . '
                        <p id="serviceCharge" class="rp-installment-plan-description small">
                            ' . $this->__('rp_mouseover_service_charge') . '
                        </p>
                    </div>
                    <div class="text-right">
                        ' . $result['serviceCharge'] . ' &euro;
                    </div>
                </div>
    
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_effective_rate') .'
                        <p id="annualPercentageRate" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_effective_rate') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['annualPercentageRate'] . ' %
                    </div>
                </div>
    
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_debit_rate') . '
                        <p id="interestRate" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_debit_rate') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['interestRate'] . ' %
                    </div>
                </div>
    
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_interest_amount') . '
                        <p id="interestAmount" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_interest_amount') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['interestAmount'] . ' &euro;
                    </div>
                </div>
    
                <div class="rp-installment-plan-details">
                    <div colspan="2"></div>
                </div>
    
    
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $result['numberOfRates'] . ' ' . $this->__('rp_duration_month') . '
                        <p id="rate" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_duration_month') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['rate'] . ' &euro;
                    </div>
                </div>
    
                <div class="rp-installment-plan-details">
                    <div class="rp-installment-plan-title">
                        ' . $this->__('rp_last_rate') . '
                        <p id="lastRate" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_last_rate') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['lastRate'] . ' &euro;
                    </div>
                </div>
            </div>
            <div id="rp-installment-plan-no-details_' . $method . '">
                <div class="rp-installment-plan-no-details">
                    <div class="rp-installment-plan-title">
                        ' . $result['numberOfRatesFull'] . ' ' . $this->__('rp_duration_month') . '
                        <p id="rate2" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_duration_month') . '</p>
                    </div>
                    <div class="text-right">
                        ' . $result['rate'] . ' &euro;
                    </div>
                </div>
            </div>
            <div class="rp-installment-plan-details">
                <div class="rp-installment-plan-title">
                    ' . $this->__('rp_total_amount') . '
                    <p id="totalAmount" class="rp-installment-plan-description small">' . $this->__('rp_mouseover_total_amount') . '</p>
                </div>
                <div class="text-right">
                    ' . $result['totalAmount'] . ' &euro;
                </div>
            </div>';
    }

    /**
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Quote $quoteOrOrder
     * @param bool $isNewOrder
     * @return bool
     */
    public function shouldUseFallbackShippingItem($quoteOrOrder, $isNewOrder = false)
    {
        $fallbackShippingFlag = (bool)$this->getRpConfigData(
            $quoteOrOrder,
            'ratepay_general',
            'use_shipping_fallback',
            true,
            true
        );

        if ($isNewOrder) {
            return $fallbackShippingFlag;
        }

        $orderUsesShippingFallback = (bool)$quoteOrOrder->getData('ratepay_use_shipping_fallback');

        return $orderUsesShippingFallback;
    }
}
