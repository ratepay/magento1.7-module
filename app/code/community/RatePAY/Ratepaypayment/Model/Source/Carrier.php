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