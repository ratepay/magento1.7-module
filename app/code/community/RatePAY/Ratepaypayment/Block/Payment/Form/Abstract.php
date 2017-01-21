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

class RatePAY_Ratepaypayment_Block_Payment_Form_Abstract extends Mage_Payment_Block_Form
{
    /**
     * @return RatePAY_Ratepaypayment_Helper_Data
     */
    public function getPaymentHelper()
    {
       return $this->getMethod()->getHelper();
    }

    /**
     * Return due days
     *
     * @return mixed
     */
    public function getDueDays()
    {
        return Mage::helper('ratepaypayment')->getRpConfigData($this->getQuote(), $this->_code, 'due_days');
    }

    /**
     * Checks if all needed informations are available or if some needs to be set
     *
     * @return boolean
     */
    public function isAdditionalFieldsNeeded()
    {
        return !($this->isPhoneSet() && $this->isValidPhone() && $this->isDobSet() && !$this->isTaxvatNeeded());
    }

    /**
     * Checks if customer phone number is set
     *
     * @return boolean
     */
    public function isPhoneSet()
    {
        return (bool) $this->getQuote()->getBillingAddress()->getTelephone();
    }

    /**
     * Return phone number
     *
     * @return string
     */
    public function getPhone()
    {
        return ($this->isPhoneSet()) ? $this->getQuote()->getBillingAddress()->getTelephone() : false;
    }

    /**
     * Check if phone number complies conditions
     *
     * @param string $phone
     * @return bool
     */
    public function isValidPhone() {
        if (!$this->isPhoneSet()) {
            return false;
        }
        $phone = $this->getPhone();
        $valid = "<^((\\+|00)[1-9]\\d{0,3}|0 ?[1-9]|\\(00? ?[1-9][\\d ]*\\))[\\d\\-/ ]*$>";
        if (strlen(trim($phone)) >= 6 && preg_match($valid, trim($phone))) {
            return true;
        }
        return false;
    }

    /**
     * Checks if customers day of birth is set
     *
     * @return boolean
     */
    public function isDobSet()
    {
        return (bool) $this->getQuote()->getCustomerDob();
    }

    /**
     * Return day of birth
     *
     * @return array
     */
    public function getDob()
    {
        return ($this->isDobSet()) ? $this->getQuote()->getCustomerDob() : false;
    }

    /**
     * Check if customer is a company
     *
     * @return boolean
     */
    public function isB2b()
    {
        return (bool) ($this->getQuote()->getBillingAddress()->getCompany());
    }

    /**
     * Check if customer is a company, and if customer is a company if vat id is set
     *
     * @return boolean
     */
    public function isTaxvatNeeded()
    {
        return (bool) ($this->getQuote()->getBillingAddress()->getCompany() && !$this->getQuote()->getCustomerTaxvat());
    }

    /**
     * Returns the entered vat id
     *
     * @return boolean
     */
    public function getVatId()
    {
        return $this->getQuote()->getCustomerTaxvat();
    }

    /**
     * Check if customer has set a vat id, if yes, checks if customer has set company name
     *
     * @return boolean
     */
    public function isCompanyNeeded()
    {
        return (bool) ($this->getQuote()->getCustomerTaxvat() && !$this->getQuote()->getBillingAddress()->getCompany());
    }

    /**
     * Returns the entered company name
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->getQuote()->getBillingAddress()->getCompany();
    }

    /**
     * Returns the country code
     *
     * @return string
     */
    public function getCountryCode()
    {
        return strtolower($this->getQuote()->getBillingAddress()->getCountryId());
    }

    /**
     * Returns the privacy policy url
     *
     * @return string
     */
    public function getPrivacyPolicyUrl()
    {
        return Mage::helper('ratepaypayment')->getRpConfigData($this->getQuote(), 'ratepay_directdebit', 'privacy_policy');
    }

    /**
     * Checks if method is set on sandbox mode
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return Mage::helper('ratepaypayment')->getRpConfigData($this->getQuote(), $this->_code, 'sandbox');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (Mage::app()->getStore()->isAdmin()){
            return Mage::getSingleton('adminhtml/session_quote')->getQuote();
        }
        else {
            return $this->getMethod()->getInfoInstance()->getQuote();
        }
    }
}
