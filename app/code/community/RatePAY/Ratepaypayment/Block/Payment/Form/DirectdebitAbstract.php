<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Payment_Form_DirectdebitAbstract extends RatePAY_Ratepaypayment_Block_Payment_Form_Abstract
{
    /**
     * Retrieve customer name from billing address
     *
     * @return string
     */
    public function getAccountOwner()
    {
        return $this->getQuote()->getBillingAddress()->getFirstname() . " " . $this->getQuote()->getBillingAddress()->getLastname();
    }
}
