<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$installer = $this;

$queries = array(
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('ratepay_debitdetails')}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `userid` varchar(256) NOT NULL,
          `owner` blob NOT NULL,
          `accountnumber` blob NOT NULL,
          `iban` blob NOT NULL,
          `bankcode` blob NOT NULL,
          `bic` blob NOT NULL,
          `bankname` blob NOT NULL,
          PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('ratepay_log')}` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_number` varchar(255) NOT NULL,
          `transaction_id` varchar(255) NOT NULL,
          `payment_method` varchar(40) NOT NULL,
          `payment_type` varchar(40) NOT NULL,
          `payment_subtype` varchar(40) NOT NULL,
          `result` varchar(40) NOT NULL,
          `request` mediumtext NOT NULL,
          `response` mediumtext NOT NULL,
          `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `result_code` varchar(5) NOT NULL,
          `name` varchar(80) NOT NULL DEFAULT '',
          `reason` varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",
    "CREATE TABLE IF NOT EXISTS `{$this->getTable('ratepay_payment_ban')}` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `customer_id` VARCHAR(100) NULL,
        `payment_method` VARCHAR(50) NULL,
        `from_date` DATETIME NULL,
        `to_date` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE INDEX `UNQ_RATEPAY_CUSTOMER_ID_PAYMENT_METHOD` (`customer_id` ASC, `payment_method` ASC)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"
);

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


$installer->startSetup();

foreach ($queries as $query) {
    $installer->run($query);
}

$installer->endSetup();

/* @var Mage_Sales_Model_Resource_Setup $installer */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

foreach ($attributes as $attr) {
    $installer->addAttribute($attr['entity'], $attr['name'], $attr['config']);
}

$installer->endSetup();

$orderStatusTable = $installer->getTable('sales/order_status');

$statuses = array(
    'payment_success' => 'Payment Success',
    'payment_processing' => 'Payment Processing',
    'payment_complete' => 'Payment Complete',
    'payment_failed' => 'Payment Failed',
);

foreach ($statuses as $status => $label) {
    $values = compact('status', 'label');
    try {
        $installer->getConnection()->insertArray($orderStatusTable, array('status', 'label'), array($values));
    } catch (Exception $e) {
        Mage::logException($e);
    }
}
