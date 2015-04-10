<?php
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