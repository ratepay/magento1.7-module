<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Deliverevent
{
    /**
     * Define which Countries are allowed for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $events = array(
            array(
                'label' => Mage::helper('core')->__('Invoice'),
                'value' => 'invoice'
            ),
            array(
                'label' => Mage::helper('core')->__('Delivery'),
                'value' => 'delivery'
            )
        );
        return $events;
    }
}