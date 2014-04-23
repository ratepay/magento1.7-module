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
 * @category PayIntelligent
 * @package PayIntelligent_RatePAY
 * @copyright Copyright (c) 2011 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

abstract class PayIntelligent_Ratepay_Model_Adminhtml_Order_Pdf_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract {

    /**
     * Add the descriptor to the head of the invoice or the shipment printout
     *
     * @see Mage_Sales_Model_Order_Pdf_Abstract::insertOrder()
     * @param integer $page
     * @param Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment $obj
     * @param string $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true) {
        parent::insertOrder($page, $obj, $putOrderId = true);
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        if ($order->getPayment()->getMethodInstance()->getCode() == 'ratepay_rechnung' || $order->getPayment()->getMethodInstance()->getCode() == 'ratepay_rate') {
            $descriptor = $order->getPayment()->getAdditionalInformation('descriptor');
            $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
            $page->drawText(Mage::helper('sales')->__('RatePAY - Order: ') . $descriptor, 470, 781, 'UTF-8');
        } else {
            parent::insertOrder($page, $obj, $putOrderId = true);
        }
    }

}
