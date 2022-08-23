<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Method_Ibs extends RatePAY_Ratepaypayment_Model_Method_Abstract
{
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_ibs';
    
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        Mage::getSingleton('ratepaypayment/session')->setDirectDebitFlag(false);
        parent::assignData($data);
        $quote = $this->getHelper()->getQuote();
        $params = $data->getData();


        // dob
        $dob = (isset($params[$this->_code . '_day'])) ? $this->getDob($data) : false;

        if (!$this->getHelper()->isCompanySet($quote) &&
            (!$this->getHelper()->isDobSet($quote) ||
                $quote->getCustomerDob() != $dob)) {
            if ($dob) {
                $validAge = $this->getHelper()->isValidAge($dob);
                switch($validAge) {
                    case 'success':
                        $this->getHelper()->setDob($quote, $dob);
                        break;
                }
            }
        }

        // phone
        if (!$this->getHelper()->isPhoneSet($quote)) {
            if (isset($params[$this->_code . '_phone'])) {
                $phone = $data->getData($this->_code . '_phone');
                if ($phone && $this->getHelper()->isValidPhone($phone)) {
                    $this->getHelper()->setPhone($quote, $phone);
                }
            }
        } else {
            $phoneCustomer = $this->getHelper()->getPhone($quote);
            $phoneParams = (isset($params[$this->_code . '_phone'])) ? $params[$this->_code . '_phone'] : false;
            if ($phoneCustomer != $phoneParams && !empty($phoneParams)) {
                if ($this->getHelper()->isValidPhone($phoneParams)) {
                    $this->getHelper()->setPhone($quote, $phoneParams);
                }
            }
        }

        // taxvat
        if (isset($params[$this->_code . '_taxvat'])) {
            if ($this->getHelper()->isValidTaxvat($quote, $params[$this->_code . '_taxvat'])) {
                $this->getHelper()->setTaxvat($quote, $params[$this->_code . '_taxvat']);
            }
        }

        //customer balance (store credit)
        if($params['use_customer_balance'] == 1){
            Mage::throwException($this->getHelper()->__('StoreCredit Error'));
        }

        return $this;

    }

}

