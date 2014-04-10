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

class PayIntelligent_Ratepay_Model_Method_Paymentquery extends PayIntelligent_Ratepay_Model_Method_Abstract
{
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_paymentquery';
    
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

            $validAge = $this->getHelper()->isValidAge($date);
            switch($validAge) {
                case 'old':
                    $this->getHelper()->setDob($quote, $date);
                case 'young':
                    $this->getHelper()->setDob($quote, $date);
                case 'success':
                    $this->getHelper()->setDob($quote, $date);
            }
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

        if(!isset($params[$this->_code . '_agreement'])) {
            Mage::throwException($this->_getHelper()->__('Pi AGB Error'));
        }
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return  PayIntelligent_Ratepay_Model_Method_Rechnung
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
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
                case 'young':
                    Mage::throwException($this->_getHelper()->__('Pi Age Error'));
                    break;
                case 'wrongdate':
                    Mage::throwException($this->_getHelper()->__('Pi Date Error'));
                    break;
            }
        } else {
            Mage::throwException($this->_getHelper()->__('Pi Date Error'));
        }
        return $this;
    }
}

