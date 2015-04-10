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

class RatePAY_Ratepaypayment_Model_Source_Statuses
{
    /**
     * Define which Status are selectable to payment statuses
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_getStatuses(Mage::getModel('sales/order_status')->getResourceCollection()->getData());
    }

    private function _getStatuses($arrStatuses)
    {
        $statuses = array();

        foreach ($arrStatuses as $status) {
            $statuses[$status['status']] = $status['label'];
        }

        if ($statuses['payment_success']) {
            $statuses['payment_success'] = 'Payment Success';
        }

        if (!$statuses['payment_complete']) {
            $statuses['payment_complete'] = 'Payment Complete';
        }

        return $statuses;
    }
}