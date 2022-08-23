<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Currency
{
    /**
     * Define which Currencies are allowed for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $currency = array(
            array(
                'label' => Mage::helper('core')->__('EUR'),
                'value' => 'EUR'
            ),
            array(
                'label' => Mage::helper('core')->__('CHF'),
                'value' => 'CHF'
            )
        );
        return $currency;
    }
}

