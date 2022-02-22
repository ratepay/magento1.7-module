<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Carrier
{
    /**
     * Define which carriers are selectable
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $carrierArray = array(
            array(
                'label' => Mage::helper('ratepaypayment')->__('No Restriction'),
                'value' => "NO"
            ), array(
                'label' => "-------------------",
                'value' => false
            )
        );

        foreach($methods as $code => $carrier)
        {
            if($title = Mage::getStoreConfig("carriers/$code/active") == "1") {
                if (!$title = Mage::getStoreConfig("carriers/$code/title")) {
                    $title = $code;
                }

                $carrierArray[] = array('value' => $code . '_' . $carrier->getId(), 'label' => $title);
            }
        }

        return $carrierArray;
    }
}