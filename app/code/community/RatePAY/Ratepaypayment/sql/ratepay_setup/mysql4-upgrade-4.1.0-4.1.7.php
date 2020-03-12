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
 * @copyright Copyright (c) 2020 RatePAY GmbH (https://www.ratepay.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
