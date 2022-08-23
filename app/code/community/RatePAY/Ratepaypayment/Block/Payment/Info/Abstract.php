<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class RatePAY_Ratepaypayment_Block_Payment_Info_Abstract extends Mage_Payment_Block_Info
{
    /**
     * Add custom information to payment method information
     *
     * @param Varien_Object|array $transport
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        $data = array();
        $data[$this->__('payment method')] = $this->__($this->getInfo()->getData('method'));
        $data[$this->__('transactionId')] = $this->getInfo()->getAdditionalInformation('transactionId');
        if($this->getInfo()->getData('method') != 'ratepay_rate') {
            $data[$this->__('descriptor')] = $this->getInfo()->getAdditionalInformation('descriptor');
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * Returns title of payment method set in config table
     *
     * @return string
     */
    public function getMethodTitle()
    {
        $order = Mage::registry('current_order');
        $method_code = $this->getMethod()->getCode();
        if(is_null($order) || empty($order)){
            return "Ratepay";
        } else {
            return Mage::helper('ratepaypayment')->getRpConfigData($order, $method_code, 'title');
        }
    }
}
