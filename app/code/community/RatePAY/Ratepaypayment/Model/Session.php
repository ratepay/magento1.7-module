<?php
class RatePAY_Ratepaypayment_Model_Session extends Mage_Core_Model_Session_Abstract
{

    public function __construct() {
        $namespace = 'ratepay';

        $this->init($namespace);
        Mage::dispatchEvent('ratepay_session_init', array('ratepay_session' => $this));
    }

} 