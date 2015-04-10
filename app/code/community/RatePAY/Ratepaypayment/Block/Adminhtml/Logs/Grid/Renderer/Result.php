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
