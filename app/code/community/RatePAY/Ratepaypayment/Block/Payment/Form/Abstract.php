<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        return ($this->isPhoneSet())
            ? htmlspecialchars($this->getQuote()->getBillingAddress()->getTelephone())
            : false;
    }

    /**
     * Check if phone number complies conditions
     *
     * @return bool
     */
    public function isValidPhone() {
        if (!$this->isPhoneSet()) {
            // M1-22 : If missing, a default value will be sent, not asking customer to fill it
            return true;
        }
        $phone = $this->getPhone();
        $valid = "/^[\d\s\/\(\)-+]/";
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
     * @return bool|string
     */
    public function getDob()
    {
        return ($this->isDobSet())
            ? htmlspecialchars($this->getQuote()->getCustomerDob())
            : false;
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
        $vatId = $this->getQuote()->getCustomerTaxvat();

        if (empty($vatId)) {
            $vatId = htmlspecialchars($this->getQuote()->getBillingAddress()->getData('vat_id'));
        }
        return $vatId;
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
        return htmlspecialchars($this->getQuote()->getBillingAddress()->getCompany());
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
     * @throws Mage_Core_Model_Store_Exception
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

    public function getFirstday()
    {
        $firstday = Mage::helper('ratepaypayment')->getRpConfigData($this->getQuote(), $this->_code, 'valid_payment_firstday');
        if(strstr($firstday, ',')){
            $firstday = explode(',', $firstday);
        }
        return $firstday;
    }

    // @ToDo: Move to helper
    public function getDeviceIdentCode()
    {
        if(is_null(Mage::getSingleton('ratepaypayment/session')->getDeviceIdentToken())) {
            $storeId = Mage::app()->getStore()->getStoreId();
            $dfpSnippetId = Mage::getStoreConfig("payment/ratepay_general/snippet_id", $storeId);
            if (empty($dfpSnippetId)) {
                $dfpSnippetId = "ratepay";
            }

            $dfp = Mage::getSingleton('ratepaypayment/libraryConnectorFrontend')->deviceFingerprint(
                $dfpSnippetId,
                Mage::getSingleton('core/session')->getEncryptedSessionId()
            );

            Mage::getSingleton('ratepaypayment/session')->setDeviceIdentToken($dfp['token']);

            return $dfp['dfpSnippetCode'];
        }
    }
}
