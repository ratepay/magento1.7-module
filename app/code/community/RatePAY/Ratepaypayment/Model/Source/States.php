<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_States
{
    /**
     * Define which state are selectable to payment states
     *
     * @return array
     */
    public function toOptionArray()
    {
        $rClass = new ReflectionClass('Mage_Sales_Model_Order');
        $allConstants = $rClass->getConstants();

        return $this->_getStates($allConstants);
    }

    private function _getStates($allConstants) {
        $states = array();

        foreach($allConstants as $code => $label) {
            if (strstr($code, "STATE_")) {
                $states[$code] = ucwords(str_replace("_", " ", $label));
            }
        }

        return $states;
    }
}