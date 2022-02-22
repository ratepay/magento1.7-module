<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('pi_ratepay_debitdetails')}` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(256) NOT NULL,
  `owner` blob NOT NULL,
  `accountnumber` blob NOT NULL,
  `bankcode` blob NOT NULL,
  `bankname` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `{$this->getTable('pi_ratepay_log')}` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR( 255 ) NOT NULL,
  `transaction_id` VARCHAR( 255 ) NOT NULL,
  `payment_method` VARCHAR( 40 ) NOT NULL,
  `payment_type` VARCHAR( 40 ) NOT NULL,
  `payment_subtype` VARCHAR( 40 ) NOT NULL,
  `result` VARCHAR( 40 ) NOT NULL,
  `request` MEDIUMTEXT NOT NULL,
  `response` MEDIUMTEXT NOT NULL,
  `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `result_code` VARCHAR( 5 ) NOT NULL,
  `name` VARCHAR( 80 ) NOT NULL DEFAULT '',
  `reason` VARCHAR( 255 ) NOT NULL DEFAULT '',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
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

