<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$queries = array(
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

/* @var Mage_Sales_Model_Resource_Setup $installer */
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$installer->startSetup();

foreach ($queries as $query) {
    $installer->run($query);
}

$installer->endSetup();
