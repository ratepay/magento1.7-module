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

class PayIntelligent_Ratepay_Model_Adminhtml_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Invoice
{

    /**
     * Create invoice as pdf
     *
     * @param array $invoices
     * @see Mage_Sales_Model_Order_Pdf_Invoice::getPdf()
     */
    public function getPdf($invoices = array())
    {
        $pdf = parent::getPdf($invoices);

        $isRatepayPayment = false;
        $storeId = null;

        foreach ($invoices as $invoice) {
            $order = $invoice->getOrder();
            if (Mage::helper('ratepay/payment')->isRatepayPayment($order->getPayment()->getMethod())) {
                $isRatepayPayment = true;

                if ($storeId = $invoice->getStoreId()) {
                    Mage::app()->getLocale()->emulate($storeId);
                    Mage::app()->setCurrentStore($storeId);
                }

                $descriptor = '';
                $addInfo = $order->getPayment()->getAdditionalInformation();
                if (isset($addInfo['descriptor'])) {
                    $descriptor = $addInfo['descriptor'];
                }
                break;
            }
        }

        if ($isRatepayPayment) {
//            var_dump($this->y);
//            exit();
            if($this->y < 200) $this->newPage();
            switch($order->getPayment()->getMethod()) {
                case 'ratepay_rechnung':
                    $this->setRatepayRechnungDetails($pdf, $storeId, $order);
                    break;
                case 'ratepay_rate':
                    $this->setRatepayRateDetails($pdf, $storeId, $order);
                    break;
                case 'ratepay_directdebit':
                    $this->setRatepayDirectdebitDetails($pdf, $storeId, $order);
                    break;
            }
        }

        return $pdf;
    }

    /**
     * Footer for invoice as pdf
     *
     * @param Zend_Pdf $pdf
     * @param Mage_Sales_Model_Order $order
     */
    protected function _addFooterToPdf($pdf, $order)
    {
        $x = 35;
        $y = 10;
        $diff = 8;
        // chr(8226)	&bull;   chr(917)
        // chr(8901)	&sdot;   chr(149)
        $dot = '•';
        $helper = Mage::helper('ratepay');

        // prepare text
        $web = str_replace('http://', '', Mage::getStoreConfig('web/unsecure/base_url'));
        if ($pos = strrpos($web, '/', -1)) {
           $web = substr($web, 0, $pos);
        }
        $line1 = Mage::getStoreConfig('general/store_information/name') . " $dot " . $web;
        $line2 = $helper->__('Pi Phone pdf') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/phone', $order->getStoreId())
               . " $dot " . $helper->__('Pi Fax') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/fax', $order->getStoreId())
               . " $dot " . $helper->__('Pi Email') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/email', $order->getStoreId());
        $line3 = $helper->__('Pi CEO') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/ceo', $order->getStoreId())
               . " $dot " . $helper->__('Pi court') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/court', $order->getStoreId())
               . " $dot " . $helper->__('Pi HR') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/trade_register', $order->getStoreId())
               . " $dot " . $helper->__('Pi Vat Id pdf') . ': ' . Mage::getStoreConfig('sales_pdf/' . $order->getPayment()->getMethod() . '/vat_id', $order->getStoreId());

        // prepare logo
        $params = array(
            '_area' => 'adminhtml',
            '_package' => 'base',
            '_theme' => 'default',
        );
        $image = Mage::getDesign()->getSkinBaseDir($params) . '/images/ratepay/ratepay.png';
        if (is_file($image)) {
            $logo = Zend_Pdf_Image::imageWithPath($image);
        }

        foreach ($pdf->pages as $page) {
            // draw text
            $page->drawText($line1, $x, $y + 2 * $diff, 'UTF-8');
            $page->drawText($line2, $x, $y + $diff, 'UTF-8');
            $page->drawText($line3, $x, $y, 'UTF-8');

            // draw logo
            if (is_file($image)) {
                $page->drawImage($logo, 500, 10, 565, 31); // 130 x 43 -> 65 x 21
            }
        }
    }

    /**
     * RatePAY Rechnung PDF Details
     *
     * @param Zend_Pdf $pdf
     * @param string $storeId
     * @param Mage_Sales_Model_Order $order
     */
    private function setRatepayRechnungDetails($pdf, $storeId,$order) {
        $page = end($pdf->pages);

        $this->_setFontRegular($page);

        $x = 35;
        $helper = Mage::helper('ratepay');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
        $descriptor = $order->getPayment()->getAdditionalInformation('descriptor');
        $page->drawText(Mage::helper('sales')->__('Pi RatePAY - Order:') . " " . $descriptor, 440, 781, 'UTF-8');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->drawText(sprintf($helper->__('Pi Paymentinformation'),  Mage::getStoreConfig('payment/ratepay_rechnung/due_days', $order->getStoreId())), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Following Account') . ': ', $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Account holder') . ': '
            . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/account_holder', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Account number') . ': '
                . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/account_number', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Bank code number') . ': '
            . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/bank_code_number', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Bank name') . ': '
            . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/bank', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi descriptor') . $descriptor, $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Ratepay Rechnung PDF Block International Transfer') . ': ', $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi SWIFT-BIC') . ': ' . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/swift_bic', $order->getStoreId()) . ' '
            . $helper->__('Pi IBAN') . ': ' . Mage::getStoreConfig('sales_pdf/ratepay_rechnung/iban', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;

        $page->drawText($helper->__('Pi Ratepay Rechnung PDF Block 1'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rechnung PDF Block 2'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rechnung PDF Block 3'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rechnung PDF Block 4'), $x, $this->y, 'UTF-8');

        $text = Mage::getStoreConfig('sales_pdf/ratepay_rechnung/invoice_field', $order->getStoreId());

        $text = preg_replace('/[\t\r\n]+/', '{{pdf_row_separator}}', $text);

        $textArray = explode('{{pdf_row_separator}}', $text);

        $lines = array();
        foreach ($textArray as $line) {
             $wordwrap = wordwrap($line, 110, "{{pdf_row_separator}}", false);
             $lines = array_merge($lines, explode('{{pdf_row_separator}}', $wordwrap));
        }

        foreach($lines as $string) {
            $page->drawText($string, $x, $this->y, 'UTF-8');
            $this->y -= 8;
        }
        $this->_addFooterToPdf($pdf, $order);

        if ($storeId) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();
    }

    /**
     * RatePAY Rate PDF Details
     *
     * @param Zend_Pdf $pdf
     * @param string $storeId
     * @param Mage_Sales_Model_Order $order
     */
    private function setRatepayRateDetails($pdf, $storeId,$order) {
        $page = end($pdf->pages);

        $this->_setFontRegular($page);

        $x = 35;
        $helper = Mage::helper('ratepay');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
        $descriptor = $order->getPayment()->getAdditionalInformation('descriptor');
        $page->drawText(Mage::helper('sales')->__('Pi RatePAY - Order:') . " " . $descriptor, 440, 781, 'UTF-8');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 1'), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 2')
                . Mage::getStoreConfig('sales_pdf/ratepay_rate/debt_holder', $order->getStoreId())
                . $helper->__('Pi Ratepay Rate PDF Block 3'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 4')
                . Mage::getStoreConfig('sales_pdf/ratepay_rate/debt_holder', $order->getStoreId())
                . $helper->__('Pi Ratepay Rate PDF Block 5'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 6')
                . $descriptor
                . $helper->__('Pi Ratepay Rate PDF Block 7'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 9'), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Ratepay Rate PDF Block 10'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText(Mage::getStoreConfig('sales_pdf/ratepay_rate/account_holder', $order->getStoreId())
                . ", " . Mage::getStoreConfig('sales_pdf/ratepay_rate/bank', $order->getStoreId())
                . ", " . $helper->__('Pi Ratepay Rate PDF Block 11') . Mage::getStoreConfig('sales_pdf/ratepay_rate/bank_code_number', $order->getStoreId())
                . ", " . $helper->__('Pi Ratepay Rate PDF Block 12') . Mage::getStoreConfig('sales_pdf/ratepay_rate/account_number', $order->getStoreId()), $x, $this->y, 'UTF-8');
        $this->y -= 15;

        $text = Mage::getStoreConfig('sales_pdf/ratepay_rate/invoice_field', $order->getStoreId());

        $text = preg_replace('/[\t\r\n]+/', '{{pdf_row_separator}}', $text);

        $textArray = explode('{{pdf_row_separator}}', $text);

        $lines = array();
        foreach ($textArray as $line) {
             $wordwrap = wordwrap($line, 110, "{{pdf_row_separator}}", false);
             $lines = array_merge($lines, explode('{{pdf_row_separator}}', $wordwrap));
        }

        foreach($lines as $string) {
            $page->drawText($string, $x, $this->y, 'UTF-8');
            $this->y -= 8;
        }
        $this->_addFooterToPdf($pdf, $order);

        if ($storeId) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();
    }
    
    /**
     * RatePAY Lastschrift PDF Details
     *
     * @param Zend_Pdf $pdf
     * @param string $storeId
     * @param Mage_Sales_Model_Order $order
     */
    protected function setRatepayDirectdebitDetails($pdf, $storeId, $order)
    {
        $page = end($pdf->pages);

        $this->_setFontRegular($page);

        $x = 35;
        $helper = Mage::helper('ratepay');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
        $descriptor = $order->getPayment()->getAdditionalInformation('descriptor');
        $page->drawText(Mage::helper('sales')->__('Pi RatePAY - Order:') . " " . $descriptor, 440, 781, 'UTF-8');

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $page->setFont($font, 13);
        
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 1'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $this->_setFontRegular($page);
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 2'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText(sprintf($helper->__('Pi Paymentinformation'),  
                         Mage::getStoreConfig('payment/ratepay_directdebit/due_days', $order->getStoreId())), $x, $this->y, 'UTF-8');
        $this->y -= 15;
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 3'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 4'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 5'), $x, $this->y, 'UTF-8');
        $this->y -= 8;
        $page->drawText($helper->__('Pi Ratepay Directdebit PDF Block 6'), $x, $this->y, 'UTF-8');
        $this->y -= 15;

        $text = Mage::getStoreConfig('sales_pdf/ratepay_rechnung/invoice_field', $order->getStoreId());

        $text = preg_replace('/[\t\r\n]+/', '{{pdf_row_separator}}', $text);

        $textArray = explode('{{pdf_row_separator}}', $text);

        $lines = array();
        foreach ($textArray as $line) {
             $wordwrap = wordwrap($line, 110, "{{pdf_row_separator}}", false);
             $lines = array_merge($lines, explode('{{pdf_row_separator}}', $wordwrap));
        }

        foreach($lines as $string) {
            $page->drawText($string, $x, $this->y, 'UTF-8');
            $this->y -= 8;
        }
        $this->_addFooterToPdf($pdf, $order);

        if ($storeId) {
            Mage::app()->getLocale()->revert();
        }

        $this->_afterGetPdf();
    }

}
