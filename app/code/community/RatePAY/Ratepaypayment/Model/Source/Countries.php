<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Countries
{
    /**
     * Define which Countries are allowed for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $countries = array(
            array(
                'label' => Mage::helper('core')->__('Germany'),
                'value' => 'DE'
            ),
            array(
                'label' => Mage::helper('core')->__('Austria'),
                'value' => 'AT'
            ),
            array(
                'label' => Mage::helper('core')->__('Switzerland'),
                'value' => 'CH'
            ),
            array(
                'label' => Mage::helper('core')->__('Netherlands'),
                'value' => 'NL'
            ),
            array(
                'label' => Mage::helper('core')->__('Belgium'),
                'value' => 'BE'
            )
        );
        return $countries;
    }
}