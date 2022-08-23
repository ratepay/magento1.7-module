<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Source_Categories
{
    /**
     * Define which customer groups are selectable
     *
     * @return array
     */
    public function toOptionArray()
    {
        $categoriesList = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->getData();

        $categoriesArray = array(
            array(
                'label' => Mage::helper('ratepaypayment')->__('No Restriction'),
                'value' => "NO"
            ), array(
                'label' => "-------------------",
                'value' => false
            )
        );

        foreach ($categoriesList as $category) {
            $categoriesArray[] = array(
                'label' => Mage::getModel('catalog/category')->load($category['entity_id'])->getName(),
                'value' => $category['entity_id']
            );
        }

        return $categoriesArray;
    }
}