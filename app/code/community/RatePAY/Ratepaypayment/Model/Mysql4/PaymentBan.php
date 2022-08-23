<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Mysql4_PaymentBan extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ratepaypayment/paymentBan', 'id');
    }
}