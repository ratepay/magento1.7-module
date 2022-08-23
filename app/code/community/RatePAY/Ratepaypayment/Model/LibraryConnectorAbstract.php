<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
