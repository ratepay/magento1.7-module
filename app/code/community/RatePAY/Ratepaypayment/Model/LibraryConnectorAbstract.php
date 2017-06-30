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

require_once("LibraryAutoloader.php");

abstract class RatePAY_Ratepaypayment_Model_LibraryConnectorAbstract extends Mage_Core_Model_Abstract
{
    private $autoloader;

    protected function setLibAutoloader()
    {
        $this->autoloader = spl_autoload_functions();
        foreach ($this->autoloader as $function) {
            spl_autoload_unregister($function);
        }
        spl_autoload_register('LibraryAutoloader::loader', 'autoload');
    }

    protected function removeLibAutoloader()
    {
        $currentAutoloader = spl_autoload_functions();
        foreach ($currentAutoloader as $function) {
            spl_autoload_unregister($function);
        }

        foreach ($this->autoloader as $function) {
            spl_autoload_register($function);
        }
    }
}
