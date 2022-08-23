<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Method_Rechnung extends RatePAY_Ratepaypayment_Model_Method_Abstract
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ratepay_rechnung';

    /**
     * Form block identifier
     *
     * @var string
     */
    protected $_formBlockType = 'ratepaypayment/payment_form_rechnung';

    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'ratepaypayment/payment_info_rechnung';

}

