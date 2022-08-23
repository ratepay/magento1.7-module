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
ALTER TABLE `{$this->getTable('pi_ratepay_debitdetails')}` ADD `iban` BLOB NOT NULL AFTER `accountnumber`;
ALTER TABLE `{$this->getTable('pi_ratepay_debitdetails')}` ADD `bic` BLOB NOT NULL AFTER `bankcode`;
");

$installer->endSetup();