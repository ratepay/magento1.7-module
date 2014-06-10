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

class PayIntelligent_Ratepay_Model_Method_Directdebit extends PayIntelligent_Ratepay_Model_Method_Abstract
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
    protected $_formBlockType = 'ratepay/payment_form_directdebit';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepay/payment_info_directdebit';
    
    
    /**
     * Assign data to info model instance
     * 
     * @param mixed $data
     * @return PayIntelligent_Ratepay_Model_Method_Directdebit 
     */
    public function assignData($data)
    {
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();

        // Only german national account/iban
        if (!empty($params[$this->_code . '_account_number'])) {
            $ibanAccno = $params[$this->_code . '_account_number'];
            if (!is_numeric($ibanAccno)) {
                $ibanAccno = $this->_clearIban($ibanAccno);
                if($ibanAccno[0].$ibanAccno[1] != "DE") {
                    Mage::throwException($this->_getHelper()->__('Pi IBAN country Error'));
                } elseif (strlen($ibanAccno)<20 || strlen($ibanAccno)>22) {
                    Mage::throwException($this->_getHelper()->__('Pi IBAN invalid Error'));
                }
                unset($params[$this->_code . '_account_number']);
                unset($params[$this->_code . '_bank_code_number']);
                unset($params[$this->_code . '_bic']);
                $params[$this->_code . '_iban'] = $ibanAccno;

            } elseif (!is_numeric($params[$this->_code . '_bank_code_number']) ||
                      strlen($params[$this->_code . '_bank_code_number']) <> 8) {
                Mage::throwException($this->_getHelper()->__('Pi insert bank code'));
            }
        }

        // Bank details
        Mage::getSingleton('core/session')->setDirectDebitFlag(false);
        if ((isset($params[$this->_code . '_account_number']) && (!empty($params[$this->_code . '_account_number']) && !empty($params[$this->_code . '_bank_code_number'])) || !empty($params[$this->_code . '_iban'])) &&
            !empty($params[$this->_code . '_account_holder']) &&
            !empty($params[$this->_code . '_bank_name'])) {
            $this->getHelper()->setBankData($params, $quote, $this->_code);
        }

        if (!isset($params[$this->_code . '_agreement'])) {
            Mage::throwException($this->_getHelper()->__('Pi AGB Error'));
        }

        // dob
        if (isset($params[$this->_code . '_day'])) {
            $day   = $data->getData($this->_code . '_day');
            $month = $data->getData($this->_code . '_month');
            $year  = $data->getData($this->_code . '_year');

            $datearray = array('year' => $year,
                'month' => $month,
                'day' => $day,
                'hour' => 0,
                'minute' => 0,
                'second' => 0);
            $date = new Zend_Date($datearray);

            $this->getHelper()->setDob($quote, $date);
        }

        // phone
        if (isset($params[$this->_code . '_phone'])) {
            $phone = $data->getData($this->_code . '_phone');
            if ($phone) {
                $this->getHelper()->setPhone($quote, $phone);
            }
        }

        // company
        if (isset($params[$this->_code . '_company'])) {
            $company = $data->getData($this->_code . '_company');
            if ($company) {
                $this->getHelper()->setCompany($quote, $company);
            }
        }

        // taxvat
        if (isset($params[$this->_code . '_taxvat'])) {
            $taxvat = $data->getData($this->_code . '_taxvat');
            if ($taxvat) {
                $this->getHelper()->setTaxvat($quote, $taxvat);
            }
        }
        
        return $this;
    }
    
    /**
     * Validate payment method information object
     *
     * @return  PayIntelligent_Ratepay_Model_Method_Directdebit
     */
    public function validate()
    {
        parent::validate();
        
        $quoteOrOrder = $this->getQuoteOrOrder();

        if (!$this->getHelper()->isPhoneSet($quoteOrOrder)) {
            Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
        }

        if($this->getHelper()->isDobSet($quoteOrOrder)) {
            $validAge = $this->getHelper()->isValidAge($quoteOrOrder->getCustomerDob());
            switch($validAge) {
                case 'old':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
                case 'young':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Age Error'));
                    break;
                case 'wrongdate':
                    $this->getHelper()->unsetDob($quoteOrOrder);
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
            }
        } else {
            Mage::throwException($this->_getHelper()->__('Pi Date Error'));
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