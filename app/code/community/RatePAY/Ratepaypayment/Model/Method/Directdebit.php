<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category RatePAY
 * @package RatePAY_Ratepaypayment
 * @copyright Copyright (c) 2015 RatePAY GmbH (https://www.ratepay.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
