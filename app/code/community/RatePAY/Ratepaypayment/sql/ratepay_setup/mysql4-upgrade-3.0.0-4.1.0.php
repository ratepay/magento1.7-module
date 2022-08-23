<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$attributes = array(
    array(
        'name' => 'ratepay_use_shipping_fallback',
        'entity' => 'quote',
        'config' => array(
            'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'input' => 'boolean',
            'default' => '0',
        ),
    ),
    array(
        'name' => 'ratepay_use_shipping_fallback',
        'entity' => 'order',
        'config' => array(
            'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'input' => 'boolean',
            'default' => '0',
        ),
    ),
);

/* @var Mage_Sales_Model_Resource_Setup $installer */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

foreach ($attributes as $attr) {
    $installer->addAttribute($attr['entity'], $attr['name'], $attr['config']);
}

$installer->endSetup();
