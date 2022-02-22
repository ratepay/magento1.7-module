<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Form_Rechnung extends RatePAY_Ratepaypayment_Block_Payment_Form_Abstract
{
    protected $_code = 'ratepay_rechnung';

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/form/rechnung.phtml');
    }
}