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
        Mage::getSingleton('core/session')->setDirectDebitFlag(false);
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
            if ($this->getHelper()->isValidTaxvat($params[$this->_code . '_taxvat'])) {
                $this->getHelper()->setTaxvat($quote, $params[$this->_code . '_taxvat']);
            }
        }

        return $this;

    }

}

