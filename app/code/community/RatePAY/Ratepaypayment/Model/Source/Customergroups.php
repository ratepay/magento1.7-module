<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Customergroups
{
    /**
     * Define which customer groups are selectable
     *
     * @return array
     */
    public function toOptionArray()
    {
        $customerGroups = new Mage_Customer_Model_Group();
        $allGroups  = $customerGroups->getCollection()->toOptionHash();

        $listGroups = array(
            array(
                'value' => "ALL",
                'label' => Mage::helper('ratepaypayment')->__('All Customers')
            ), array(
                'label' => "-------------------",
                'value' => false
            )
        );

        foreach ($allGroups as $value => $label) {
            $listGroups[] = array(
                'label' => $label,
                'value' => $value
            );
        }


        return $listGroups;
    }
}