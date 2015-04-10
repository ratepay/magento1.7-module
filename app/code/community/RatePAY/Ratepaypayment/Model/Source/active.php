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