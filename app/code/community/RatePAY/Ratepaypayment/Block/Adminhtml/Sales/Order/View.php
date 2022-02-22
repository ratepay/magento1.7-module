<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {
    public function __construct() {
        $code = $this->getOrder()->getPayment()->getMethodInstance()->getCode();
        if (Mage::helper('ratepaypayment/payment')->isRatepayPayment($code)) {
            parent::__construct();
            $this->removeButton('order_ship');
        } else {
            parent::__construct();
        }
    }
}