<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_PaymentBan extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ratepaypayment/paymentBan');
    }

    /**
     * @param int $customerId
     * @return RatePAY_Ratepaypayment_Model_PaymentBan[]
     */
    public function loadByCustomerId($customerId)
    {
        $collection = Mage::getModel('ratepaypayment/paymentBan')->getCollection();
        $collection->addFieldToFilter('customer_id', array('eq' => $customerId));
        $result = $collection->load();

        return $result->getItems();
    }

    /**
     * @param int $customerId
     * @param string $paymentMethod
     * @return RatePAY_Ratepaypayment_Model_PaymentBan
     */
    public function loadByCustomerIdPaymentMethod($customerId, $paymentMethod)
    {
        $collection = Mage::getModel('ratepaypayment/paymentBan')->getCollection();
        $collection->addFieldToFilter('customer_id', array('eq' => $customerId));
        $collection->addFieldToFilter('payment_method', array('eq' => $paymentMethod));
        foreach ($collection->load() as $item) {
            return $item;
        }

        return $this;
    }
}
