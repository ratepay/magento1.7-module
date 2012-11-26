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

class PayIntelligent_Ratepay_Helper_Email extends Mage_Core_Helper_Abstract
{
    /**
     * Method needed for sendNewOrderEmail and sendNewInvoiceEmail
     * @param string $configPath
     * @param int $storeId
     */
    public function getEmails($configPath, $storeId)
    {
        $data = Mage::getStoreConfig($configPath, $storeId);
        if (!empty($data)) {
            return explode(',', $data);
        }
        return false;
    }
    
    /**
     * Send a RatePAY specific creditmemo mail
     * 
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    public function sendNewCreditmemoEmail($creditmemo)
    {
        $order = $creditmemo->getOrder();
        $storeId = $order->getStore()->getId();
        $copyTo = $this->getEmails(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_COPY_TO, $storeId);
        $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($order->getCustomerIsGuest()) {
            $templateId = $order->getPayment()->getMethod() . '_creditmemo_template';
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $order->getPayment()->getMethod() . '_creditmemo_template';
            $customerName = $order->getCustomerName();
        }
        $notifyCustomer = true;
        
        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                // Add bcc to customer email
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }
        
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $web = str_replace('http://', '', Mage::getStoreConfig('web/unsecure/base_url'));
        if ($pos = strrpos($web, '/', -1)) {
           $web = substr($web, 0, $pos);
        }
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'invoice'      => $creditmemo,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'debt_holder'  => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/debt_holder', $order->getStoreId()),
                'account_holder' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/account_holder', $order->getStoreId()),
                'account_number' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/account_number', $order->getStoreId()),
                'bank_code' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/bank_code_number', $order->getStoreId()),
                'bank_name' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/bank', $order->getStoreId()),
                'decriptor' => $order->getPayment()->getAdditionalInformation('descriptor'),
                'swift' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/swift_bic', $order->getStoreId()),
                'iban' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/iban', $order->getStoreId()),
                'email' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/email', $order->getStoreId()),
                'fax' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/fax', $order->getStoreId()),
                'phone' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/phone', $order->getStoreId()),
                'owner' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/ceo', $order->getStoreId()),
                'court' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/court', $order->getStoreId()),
                'trade_register' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/trade_register', $order->getStoreId()),
                'vat_id' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/vat_id', $order->getStoreId()),
                'url' => $web,
                'due_days' => Mage::getStoreConfig('payment/' . $order->getPayment()->getMethod() . '/due_days', $order->getStoreId()),
                'logo' => '<img src="' . Mage::getDesign()->getSkinUrl() . 'images/ratepay/ratepay.png" alt="RatePAY Logo" width="90px" height="25px" style="margin-left:5px;"/>',
                'due_days' => Mage::getStoreConfig('payment/' . $order->getPayment()->getMethod() . '/due_days', $order->getStoreId())
            )
        );
        $mailer->send();
    }
    
    /**
     * Send additional invoice email (transactional email)
     */
    public function sendNewInvoiceEmail($order)
    {       
        $storeId = $order->getStore()->getId();
        $invoice = $this->getLastInvoice($order);
        $copyTo = $this->getEmails(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_COPY_TO, $storeId);
        $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_COPY_METHOD, $storeId);
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($order->getCustomerIsGuest()) {
            $templateId = $order->getPayment()->getMethod() . '_invoice_template';
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = $order->getPayment()->getMethod() . '_invoice_template';
            $customerName = $order->getCustomerName();
        }
        $notifyCustomer = true;
        
        $mailer = Mage::getModel('core/email_template_mailer');
        if ($notifyCustomer) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            if ($copyTo && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
            $mailer->addEmailInfo($emailInfo);
        }
        
        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }
        $mailer->setSender(Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $web = str_replace('http://', '', Mage::getStoreConfig('web/unsecure/base_url'));
        if ($pos = strrpos($web, '/', -1)) {
           $web = substr($web, 0, $pos);
        }
        $mailer->setTemplateParams(array(
                'order'        => $order,
                'invoice'      => $invoice,
                'billing'      => $order->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'debt_holder'  => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/debt_holder', $order->getStoreId()),
                'account_holder' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/account_holder', $order->getStoreId()),
                'account_number' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/account_number', $order->getStoreId()),
                'bank_code' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/bank_code_number', $order->getStoreId()),
                'bank_name' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/bank', $order->getStoreId()),
                'decriptor' => $order->getPayment()->getAdditionalInformation('descriptor'),
                'swift' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/swift_bic', $order->getStoreId()),
                'iban' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/iban', $order->getStoreId()),
                'email' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/email', $order->getStoreId()),
                'fax' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/fax', $order->getStoreId()),
                'phone' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/phone', $order->getStoreId()),
                'owner' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/ceo', $order->getStoreId()),
                'court' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/court', $order->getStoreId()),
                'trade_register' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/trade_register', $order->getStoreId()),
                'vat_id' => Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/vat_id', $order->getStoreId()),
                'url' => $web,
                'due_days' => Mage::getStoreConfig('payment/' . $order->getPayment()->getMethod() . '/due_days', $order->getStoreId()),
                'logo' => '<img src="' . Mage::getDesign()->getSkinUrl() . 'images/ratepay/ratepay.png" alt="RatePAY Logo" width="90px" height="25px" style="margin-left:5px;"/>',
                'due_days' => Mage::getStoreConfig('payment/' . $order->getPayment()->getMethod() . '/due_days', $order->getStoreId())
            )
        );
        $mailer->send();
    }
    
    /**
     * Retrieve the last invoice object from the given order
     * 
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function getLastInvoice($order)
    {
        foreach ($order->getInvoiceCollection() as $invoice){
            
        }
        
        return $invoice;
    }
}