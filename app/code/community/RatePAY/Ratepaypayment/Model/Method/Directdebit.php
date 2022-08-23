<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_Method_Directdebit extends RatePAY_Ratepaypayment_Model_Method_Abstract
{
    
    /**
     * Payment code
     * 
     * @var string 
     */
    protected $_code = 'ratepay_directdebit';
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'ratepaypayment/payment_form_directdebit';
    
    /**
     * Info block identifier
     * 
     * @var string
     */
    protected $_infoBlockType = 'ratepaypayment/payment_info_directdebit';

    /**
     * Assign data to info model instance
     * 
     * @param mixed $data
     * @return RatePAY_Ratepaypayment_Model_Method_Directdebit
     */
    public function assignData($data)
    {
        parent::assignData($data);
        parent::assignBankData($data);

        return $this;
    }

}
