<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Mysql4_Logging extends Mage_Core_Model_Mysql4_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        $this->_init('ratepaypayment/logging', 'id');
    }
}