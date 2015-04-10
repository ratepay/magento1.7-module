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