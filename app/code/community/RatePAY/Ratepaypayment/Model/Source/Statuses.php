<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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