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
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('ratepay_debitdetails')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(256) NOT NULL,
  `owner` blob NOT NULL,
  `accountnumber` blob NOT NULL,
  `iban` blob NOT NULL,
  `bankcode` blob NOT NULL,
  `bic` blob NOT NULL,
  `bankname` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('ratepay_log')}` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
");

$installer->endSetup();

$statusTable = $installer->getTable('sales/order_status');

$statuses = array(
    'payment_success' => 'Payment Success',
    'payment_processing' => 'Payment Processing',
    'payment_complete' => 'Payment Complete',
    'payment_failed' => 'Payment Failed'
);

foreach ($statuses as $code => $info) {
    $data = array();
    $data[] = array(
        'status' => $code,
        'label' => $info
    );
    try {
        $installer->getConnection()->insertArray($statusTable, array('status', 'label'), $data);
    } catch (Exception $e) {
        Mage::logException($e);
    }
}

