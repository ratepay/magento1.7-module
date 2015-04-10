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
        $valid = "<^((\\+|00)[1-9]\\d{0,3}|0 ?[1-9]|\\(00? ?[1-9][\\d ]*\\))[\\d\\-/ ]*$>";
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
     * @param String
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
     */
    public function getQuote()
    {
        return Mage::getSingleton('checkout/session')->getQuote();
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
     */
    public function setDob($quote, $dob)
    {
        if ($quote->getCustomerId()) {
            $quote->getCustomer()
                ->setDob($dob->toString("yyyy-MM-dd HH:mm:ss"))
                ->save();
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
     * @param string
     * @param bool
     */
    public function isValidTaxvat($taxvat)
    {
        $valid = "<^((DE)?[0-9]{9})$>"; // in case of AT: "^((DE)?[0-9]{9}|(AT)?U[0-9]{8})$"
        if (preg_match($valid, trim($taxvat))) {
            return true;
        }
        return false;
    }

    /**
     * Sets the company into the customer if not guest and always into the Quote/Order
     *
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $quote
     * @param string $company
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
     */
    public function isCompanySet($quote)
    {
        return $quote->getBillingAddress()->getCompany();
    }

    /**
     * We have to diff the addresses, because same_as_billing is sometimes wrong
     *
     * @param unknown_type $address
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
    public function setBankData($data, Mage_Sales_Model_Quote $quote, $code)
    {
        Mage::getSingleton('core/session')->setDirectDebitFlag(true);
        if (!$this->getRpConfigData($quote, $code, 'bankdata_saving') || $quote->getCustomerIsGuest()) {
            $this->_setBankDataSession($data, $code);
            Mage::getSingleton('core/session')->setBankdataAfter(false);
        } else {
            $piEncryption = new Pi_Util_Encryption_MagentoEncryption();
            $bankdata = array (
                'owner' => $data[$code . '_account_holder']
            );
            if(!empty($data[$code . '_iban'])) {
                $bankdata['iban'] = $data[$code . '_iban'];
                if(!empty($data[$code . '_bic'])) {
                    $bankdata['bic'] = $data[$code . '_bic'];
                }
            } else {
                $bankdata['accountnumber'] = $data[$code . '_account_number'];
                $bankdata['bankcode'] = $data[$code . '_bank_code_number'];
            }

            if (Mage::helper('customer')->isLoggedIn()) {
                Mage::getSingleton('core/session')->setBankdataAfter(false);
                $piEncryption->saveBankdata($quote->getCustomer()->getId(), $bankdata);
            } else {
                Mage::getSingleton('core/session')->setBankdataAfter(true);
                $this->_setBankDataSession($data, $code);
            }
        }
    }
    
    private function _setBankDataSession($data, $code)
    {
        if($data[$code . '_iban']) {
            Mage::getSingleton('core/session')->setIban($data[$code . '_iban']);
            if($data[$code . '_bic']) {
                Mage::getSingleton('core/session')->setBic($data[$code . '_bic']);
            }
        } else {
            Mage::getSingleton('core/session')->setAccountNumber($data[$code . '_account_number']);
            Mage::getSingleton('core/session')->setBankCodeNumber($data[$code . '_bank_code_number']);
        }
        Mage::getSingleton('core/session')->setAccountHolder($data[$code . '_account_holder']);
    }
    
    /**
     * Retrieve the encoded bankdata
     * 
     * @return array
     */
    public function getBankData()
    {
        $bankdata = null;
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        (!$quote->getCustomerIsGuest()) ? $customerId = $quote->getCustomer()->getId() : $customerId = '';
        $piEncryption = new Pi_Util_Encryption_MagentoEncryption();
        if (!$quote->getCustomerIsGuest() && $piEncryption->isBankdataSetForUser($customerId)) {
            $bankdata = $piEncryption->loadBankdata($customerId);
        } else {
            $bankdata = array (
                'owner' => Mage::getSingleton('core/session')->getAccountHolder()
            );

            if(Mage::getSingleton('core/session')->getIban()) {
                $bankdata['iban'] = Mage::getSingleton('core/session')->getIban();
                if(Mage::getSingleton('core/session')->getBic()) {
                    $bankdata['bic'] = Mage::getSingleton('core/session')->getBic();
                }
            } else {
                $bankdata['accountnumber'] = Mage::getSingleton('core/session')->getAccountNumber();
                $bankdata['bankcode'] = Mage::getSingleton('core/session')->getBankCodeNumber();
            }
        }
        return $bankdata;
    }

    /**
     * Retrieve due days
     * 
     * @return string
     */
    public function getDueDays($payment)
    {
        $order = $this->getOrderByIncrementId($payment['orderId']);
        $code = $order->getPayment()->getMethodInstance()->getCode();
        if ($code == 'ratepay_rate') {
            $data = '';
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
    
    /**
     * Is order installment
     * 
     * @param integer $orderId
     * @return boolean 
     */
    public function isInstallment($orderId)
    {
        return Mage::getModel('sales/order')->loadByIncrementId($orderId)->getPayment()->getMethodInstance()->getCode() == 'ratepay_rate';
    }

    /**
     * Render the Rate calculator result html
     * 
     * @param array $result
     */
    public function getRateResultHtml($result, $admin = false)
    {
        if (!$admin) {
            echo '<div id="piRpNotfication">' . $this->__('lang_information') . ":<br/>" . $this->__('lang_info[\''. $result['code'] . '\']') . '</div>';
        }

        echo '<h2 class="pirpmid-heading"><b>' . $this->__('lang_individual_rate_calculation') . '</b></h2>';
        echo '<table id="piInstallmentTerms" cellspacing="0">';
        echo '    <tr>';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoPaymentPrice\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoPaymentPrice\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft">' . $this->__('lang_cash_payment_price') . ':</div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoPaymentPrice">' . $this->__('lang_mouseover_cash_payment_price') . '</div>';
        echo '             </div>';
        echo '        </th>';
        echo '        <td>&nbsp;' . $result['amount'] . '</td>';
        echo '        <td class="piRpTextAlignLeft">&euro;</td>';
        echo '    </tr>';
        echo '    <tr class="piTableHr">';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoServiceCharge\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoServiceCharge\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') .'"/></div>';
        echo '             <div class="piRpFloatLeft">' . $this->__('lang_service_charge') . ':</div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoServiceCharge">' . $this->__('lang_mouseover_service_charge') . '</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td>&nbsp;' . $result['serviceCharge'] . '</td>';
        echo '        <td class="piRpTextAlignLeft">&euro;</td>';
        echo '    </tr>';
        echo '    <tr class="piPriceSectionHead">';
        echo '        <th class="piRpPercentWidth">';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoEffectiveRate\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoEffectiveRate\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft">' . $this->__('lang_effective_rate') . ':</div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoEffectiveRate">' . $this->__('lang_mouseover_effective_rate') . ':</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td colspan="2"><div class="piRpFloatLeft">&nbsp;<div class="piRpPercentWith">' . $result['annualPercentageRate'] . '%</div></div></td>';
        echo '    </tr>';
        echo '    <tr class="piTableHr">';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoDebitRate\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoDebitRate\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft">' . $this->__('lang_interestrate_default') . ':</div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDebitRate">' . $this->__('lang_mouseover_debit_rate') . ':</div>';
        echo '            </div>';
        echo '         </th>';
        echo '        <td colspan="2"><div class="piRpFloatLeft">&nbsp;<div class="piRpPercentWith">' . $result['interestRate'] . '%</div></div></td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoInterestAmount\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoInterestAmount\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft">' . $this->__('lang_interest_amount') . ':</div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoInterestAmount">' . $this->__('lang_mouseover_interest_amount') . ':</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td>&nbsp;' . $result['interestAmount'] . '</td>';
        echo '        <td class="piRpTextAlignLeft">&euro;</td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoTotalAmount\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoTotalAmount\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft"><b>' . $this->__('lang_total_amount') . ':</b></div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoTotalAmount">' . $this->__('lang_mouseover_total_amount') . '</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td><b>&nbsp;' . $result['totalAmount'] . '</b></td>';
        echo '        <td class="piRpTextAlignLeft"><b>&euro;</b></td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td colspan="2"><div class="piRpFloatLeft">&nbsp;<div></td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td colspan="2"><div class="piRpFloatLeft">' . $this->__('lang_calulation_result_text') . '<div></td>';
        echo '    </tr>';
        echo '     <tr class="piRpyellow piPriceSectionHead">';
        echo '        <th class="piRpPaddingTop">';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoDurationTime\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoDurationTime\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft"><b>' . $this->__('lang_duration_time') . ':</b></div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDurationTime">' . $this->__('lang_mouseover_duration_time') . '</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td colspan="2" class="piRpPaddingRight piRpPaddingTop"><b>' . $result['numberOfRatesFull'] . '&nbsp;' . $this->__('lang_months') . '</b></td>';
        echo '    </tr>';
        echo '    <tr class="piRpyellow">';
        echo '        <th>';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoDurationMonth\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoDurationMonth\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft piRpPaddingLeft"><b>' . $result['numberOfRates'] . '' . $this->__('lang_duration_month') . ':</b></div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoDurationMonth">' . $this->__('lang_mouseover_duration_month') . '</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td><b>&nbsp;' . $result['rate'] . '</b></td>';
        echo '        <td class="piRpPaddingRight"><b>&euro;</b></td>';
        echo '    </tr>';
        echo '    <tr class="piRpyellow piRpPaddingBottom">';
        echo '        <th class="piRpPaddingBottom">';
        echo '            <div class="piRpInfoImgDiv"><img onMouseOver="piMouseOver(\'piRpMouseoverInfoLastRate\')" onMouseOut="piMouseOut(\'piRpMouseoverInfoLastRate\')" class="piRpInfoImg" src="' . Mage::getDesign()->getSkinUrl('images/ratepay/info-icon.png') . '"/></div>';
        echo '            <div class="piRpFloatLeft piRpPaddingLeft"><b>' . $this->__('lang_last_rate') . ':</b></div>';
        echo '            <div class="piRpRelativePosition">';
        echo '                <div class="piRpMouseoverInfo" id="piRpMouseoverInfoLastRate">' . $this->__('lang_mouseover_last_rate') . '</div>';
        echo '            </div>';
        echo '        </th>';
        echo '        <td class="piRpPaddingBottom"><b>&nbsp;' . $result['lastRate'] . '</b></td>';
        echo '        <td class="piRpPaddingRight piRpPaddingBottom"><b>&euro;</b></td>';
        echo '    </tr>';
        echo '    <tr>';
        echo '        <td colspan="2"><div class="piRpCalculationText ">' . $this->__('lang_calulation_example') . '</div></td>';
        echo '    </tr>';
        echo '</table>';
    }
}

