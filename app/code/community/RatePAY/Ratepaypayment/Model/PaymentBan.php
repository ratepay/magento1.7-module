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
 * @copyright Copyright (c) 2020 RatePAY GmbH (https://www.ratepay.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
