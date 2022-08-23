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
RENAME TABLE `{$this->getTable('pi_ratepay_log')}` TO `{$this->getTable('ratepay_log')}`;
RENAME TABLE `{$this->getTable('pi_ratepay_debitdetails')}` TO `{$this->getTable('ratepay_debitdetails')}`;
");

$installer->endSetup();
