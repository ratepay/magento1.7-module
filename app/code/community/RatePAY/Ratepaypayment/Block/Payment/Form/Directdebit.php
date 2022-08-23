<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Form_Directdebit extends RatePAY_Ratepaypayment_Block_Payment_Form_DirectdebitAbstract
{
    protected $_code = 'ratepay_directdebit';

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/payment/form/directdebit.phtml');
    }
}
