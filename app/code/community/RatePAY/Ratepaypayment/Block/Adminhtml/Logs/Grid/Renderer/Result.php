<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid_Renderer_Result extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders SUCCESS or ERROR based on RatePAY Resultcodes
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        switch ($row->getResultCode()) {
            case '350': // PAYMENT_INIT
            case '402': // PAYMENT_REQUEST
            case '400': // PAYMENT_CONFIRM
            case '403': // PAYMENT_CHANGE
            case '404': // CONFIRMATION_DELIVER
            case '500': // CALCULATION_REQUEST
                return 'SUCCESS';
            default:
                return 'ERROR';
        }
    }

}
