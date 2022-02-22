<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Method_Rate0 extends RatePAY_Ratepaypayment_Model_Method_RateAbstract
{
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_rate0';
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'ratepaypayment/payment_form_rate0';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepaypayment/payment_info_rate0';

}

