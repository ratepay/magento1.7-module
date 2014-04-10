<?php

class PayIntelligent_Ratepay_Model_System_Config_Source_Dropdown_Values
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