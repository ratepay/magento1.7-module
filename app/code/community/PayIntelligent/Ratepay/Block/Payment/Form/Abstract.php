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

class PayIntelligent_Ratepay_Block_Payment_Form_Abstract extends Mage_Payment_Block_Form
{
    /**
     * @return PayIntelligent_Ratepay_Helper_Data
     */
    public function getPaymentHelper()
    {
       return $this->getMethod()->getHelper();
    }

    /**
     * Return minimum order total
     *
     * @return string
     */
    public function getMinAmount()
    {
        return $this->getMethod()->getConfigData("min_order_total", $this->getQuote()->getStoreId());
    }

    /**
     * Return maximum order total
     *
     * @return string
     */
    public function getMaxAmount()
    {
        return $this->getMethod()->getConfigData("max_order_total", $this->getQuote()->getStoreId());
    }
    
    public function getDueDays()
    {
        return $this->getMethod()->getConfigData("due_days", $this->getQuote()->getStoreId());
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
     * Checks if method is set on Whitelabel mode
     *
     * @return boolean
     */
    public function isWhitelabel()
    {
        return $this->getMethod()->getConfigData("whitelabel", $this->getQuote()->getStoreId());
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getMethod()->getInfoInstance()->getQuote();
    }
}