<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_System_Config_Source_Dropdown_Values
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => null,
                'label' => 'N/A',
            ),
            array(
                'value' => 'slim',
                'label' => 'Slim',
            ),
            array(
                'value' => 'light',
                'label' => 'Light',
            ),
            array(
                'value' => 'full',
                'label' => 'Full',
            ),
        );
    }
}