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
}
