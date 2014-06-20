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

class PayIntelligent_Ratepay_Model_Method_Rechnung extends PayIntelligent_Ratepay_Model_Method_Abstract
{
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_rechnung';
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'ratepay/payment_form_rechnung';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepay/payment_info_rechnung';

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::getSingleton('core/session')->setDirectDebitFlag(false);
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();

        // dob
        $dob = (isset($params[$this->_code . '_day'])) ? $this->_getDob($data) : false;

        if(!$this->getHelper()->isDobSet($quote) ||
           $quote->getCustomerDob() != $dob) {
            if ($dob) {
                $validAge = $this->getHelper()->isValidAge($dob);
                switch($validAge) {
                    case 'old':
                        Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                        break;
                    case 'young':
                        Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                        break;
                    case 'wrongdate':
                        Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                        break;
                    case 'success':
                        $this->getHelper()->setDob($quote, $dob);
                        break;
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Pi Date Error'));
            }
        }

        // phone
        if (!$this->getHelper()->isPhoneSet($quote)) {
            if (isset($params[$this->_code . '_phone'])) {
                $phone = $data->getData($this->_code . '_phone');
                if ($phone && $this->getHelper()->isValidPhone($phone)) {
                    $this->getHelper()->setPhone($quote, $phone);
                } else {
                    Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
                }
            } else {
                Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
            }
        } else {
            $phoneCustomer = $this->getHelper()->getPhone($quote);
            $phoneParams = (isset($params[$this->_code . '_phone'])) ? $params[$this->_code . '_phone'] : false;
            if ($phoneCustomer != $phoneParams && !empty($phoneParams)) {
                if ($this->getHelper()->isValidPhone($phoneParams)) {
                    $this->getHelper()->setPhone($quote, $phoneParams);
                } else {
                    Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
                }
            } elseif (!$this->getHelper()->isValidPhone($phoneCustomer)) {
                Mage::throwException($this->_getHelper()->__('Pi Phone Error'));
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
     * Returns date object from dob params
     *
     * @param   mixed $data
     * @return  Zend_Date
     */

    function _getDob($data) {
        $day   = $data->getData($this->_code . '_day');
        $month = $data->getData($this->_code . '_month');
        $year  = $data->getData($this->_code . '_year');

        $datearray = array('year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => 0,
            'minute' => 0,
            'second' => 0);
        return new Zend_Date($datearray);
    }
}

