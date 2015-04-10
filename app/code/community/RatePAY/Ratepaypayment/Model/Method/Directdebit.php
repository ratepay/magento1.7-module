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

class RatePAY_Ratepaypayment_Model_Method_Directdebit extends RatePAY_Ratepaypayment_Model_Method_Abstract
{
    
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_directdebit';
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'ratepaypayment/payment_form_directdebit';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepaypayment/payment_info_directdebit';
    
    
    /**
     * Assign data to info model instance
     * 
     * @param mixed $data
     * @return RatePAY_Ratepaypayment_Model_Method_Directdebit
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();

        // Bank data
        $ibanAccNo = $this->_clearIban($params[$this->_code . '_account_number']);
        $ibanAccNoCountryCode = substr($ibanAccNo, 0, 2);
        $bic = $params[$this->_code . '_bank_code_number'];

        if (!empty($ibanAccNo)) {
            if (!is_numeric($ibanAccNo)) {
                if ($ibanAccNoCountryCode == $this->getHelper()->getCountryCode($quote)) {
                    if ($ibanAccNoCountryCode == "DE") {
                        if (strlen($ibanAccNo) <> 22) {
                            Mage::throwException($this->_getHelper()->__('IBAN invalid Error'));
                        }
                        unset($params[$this->_code . '_bic']);
                    }
                    if ($ibanAccNoCountryCode == "AT") {
                        if (strlen($ibanAccNo) <> 20) {
                            Mage::throwException($this->_getHelper()->__('IBAN invalid Error'));
                        }
                        if ($bic == '') {
                            Mage::throwException($this->_getHelper()->__('insert bank code'));
                        } elseif (strlen($bic) <> 8 && strlen($bic) <> 11) {
                            Mage::throwException($this->_getHelper()->__('insert bank code'));
                        }
                        $params[$this->_code . '_bic'] = $bic;
                    }
                } else {
                    Mage::throwException($this->_getHelper()->__('IBAN invalid Error'));
                }
                unset($params[$this->_code . '_account_number']);
                unset($params[$this->_code . '_bank_code_number']);

                $params[$this->_code . '_iban'] = $ibanAccNo;
            } else {
                if ($this->getHelper()->getCountryCode($quote) != "DE") {
                    Mage::throwException($this->_getHelper()->__('IBAN invalid Error'));
                } elseif (!is_numeric($bic) || strlen($bic) <> 8) {
                    Mage::throwException($this->_getHelper()->__('insert bank code'));
                }
            }
        }

        Mage::getSingleton('core/session')->setDirectDebitFlag(false);
        if ((isset($params[$this->_code . '_account_number']) && (!empty($params[$this->_code . '_account_number']) && !empty($params[$this->_code . '_bank_code_number'])) || !empty($params[$this->_code . '_iban'])) &&
            !empty($params[$this->_code . '_account_holder'])) {
            $this->getHelper()->setBankData($params, $quote, $this->_code);
        }

        if (!isset($params[$this->_code . '_agreement'])) {
            Mage::throwException($this->_getHelper()->__('AGB Error'));
        }

        // dob
        $dob = (isset($params[$this->_code . '_day'])) ? $this->getDob($data) : false;

        if(!$this->getHelper()->isDobSet($quote) ||
            $quote->getCustomerDob() != $dob) {
            if ($dob) {
                $validAge = $this->getHelper()->isValidAge($dob);
                switch($validAge) {
                    case 'old':
                        Mage::throwException($this->_getHelper()->__('Date Error'));
                        break;
                    case 'young':
                        Mage::throwException($this->_getHelper()->__('Date Error'));
                        break;
                    case 'wrongdate':
                        Mage::throwException($this->_getHelper()->__('Date Error'));
                        break;
                    case 'success':
                        $this->getHelper()->setDob($quote, $dob);
                        break;
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Date Error'));
            }
        }

        // phone
        if (!$this->getHelper()->isPhoneSet($quote)) {
            if (isset($params[$this->_code . '_phone'])) {
                $phone = $data->getData($this->_code . '_phone');
                if ($phone && $this->getHelper()->isValidPhone($phone)) {
                    $this->getHelper()->setPhone($quote, $phone);
                } else {
                    Mage::throwException($this->_getHelper()->__('Phone Error'));
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Phone Error'));
            }
        } else {
            $phoneCustomer = $this->getHelper()->getPhone($quote);
            $phoneParams = (isset($params[$this->_code . '_phone'])) ? $params[$this->_code . '_phone'] : false;
            if ($phoneCustomer != $phoneParams && !empty($phoneParams)) {
                if ($this->getHelper()->isValidPhone($phoneParams)) {
                    $this->getHelper()->setPhone($quote, $phoneParams);
                } else {
                    Mage::throwException($this->_getHelper()->__('Phone Error'));
                }
            } elseif (!$this->getHelper()->isValidPhone($phoneCustomer)) {
                Mage::throwException($this->_getHelper()->__('Phone Error'));
            }
        }

        // taxvat
        if (isset($params[$this->_code . '_taxvat'])) {
            if ($this->getHelper()->isValidTaxvat($params[$this->_code . '_taxvat'])) {
                $this->getHelper()->setTaxvat($quote, $params[$this->_code . '_taxvat']);
            } else {
                Mage::throwException($this->_getHelper()->__('VatId Error'));
            }
        }
        
        return $this;
    }
    
    public function _clearIban($iban)
    {
        $iban = ltrim(strtoupper($iban));
        $iban = preg_replace('/^IBAN/','',$iban);
        $iban = preg_replace('/[^a-zA-Z0-9]/','',$iban);
        $iban = strtoupper($iban);
        return $iban;
    }
    
}