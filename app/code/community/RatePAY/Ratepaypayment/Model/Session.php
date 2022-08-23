<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Session extends Mage_Core_Model_Session_Abstract
{

    public function __construct() {
        $namespace = 'ratepay';

        $this->init($namespace);
        Mage::dispatchEvent('ratepay_session_init', array('ratepay_session' => $this));
    }

} 