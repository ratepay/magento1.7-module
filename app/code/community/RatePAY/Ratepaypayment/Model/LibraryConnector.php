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

class RatePAY_Ratepaypayment_Model_LibraryConnector extends RatePAY_Ratepaypayment_Model_LibraryConnectorAbstract
{
    private $headModel;

    private $contentModel;

    private $request;

    public function __construct($sandbox = true)
    {
        // Set library as autoloader (and remove mage autoloader) while instancing library classes
        $this->setLibAutoloader();

        $this->headModel = new RatePAY\ModelBuilder('Head');
        $this->contentModel = new RatePAY\ModelBuilder('Content');
        $this->request = new RatePAY\RequestBuilder((bool)$sandbox);

        // Switch back to mage autoloader
        $this->removeLibAutoloader();
    }

    public function callRequest($head, $content = null, $subtype = null)
    {
        $head = $this->extendHeadData($head);

        // Set library as autoloader (and remove mage autoloader) while instancing library classes
        $this->setLibAutoloader();
        try {
            $this->headModel->setArray($head);

            if (!is_null($content)) {
                $this->contentModel->setArray($content);
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        try {
            // Get calling method
            $backtrace = debug_backtrace();
            $callingMethod = $backtrace[1]['function'];

            if (is_null($content)) {
                $response = $this->request->$callingMethod($this->headModel);
            } elseif (is_null($subtype)) {
                $response = $this->request->$callingMethod($this->headModel, $this->contentModel);
            } else {
                $response = $this->request->$callingMethod($this->headModel, $this->contentModel)->subtype($subtype);
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }

        // Switch back to mage autoloader
        $this->removeLibAutoloader();

        return $response;
    }

    public function callProfileRequest($head)
    {
        return $this->callRequest($head);
    }

    /*public function callConfigurationRequest($head)
    {
        return $this->callRequest($head);
    }*/

    public function callCalculationRequest($head, $content, $subtype, $paymentMethod = null)
    {
        return $this->callRequest($head, $content, $subtype, $paymentMethod);
    }

    public function callPaymentInit($head, $paymentMethod = null)
    {
        return $this->callRequest($head, null, null, $paymentMethod);
    }

    public function callPaymentRequest($head, $content, $paymentMethod = null)
    {
        return $this->callRequest($head, $content, null, $paymentMethod);
    }

    public function callPaymentChange($head, $content, $subtype, $paymentMethod = null)
    {
        return $this->callRequest($head, $content, $subtype, $paymentMethod);
    }

    public function callConfirmationDeliver($head, $content, $paymentMethod = null)
    {
        return $this->callRequest($head, $content, null, $paymentMethod);
    }

    private function extendHeadData($head)
    {
        $head['SystemId'] = Mage::helper('core/http')->getServerAddr(false); // @ToDo: Move this to helper
        $head['Meta'] = [
            'Systems' => [
                'System' => [
                    'Name' => "Magento_" . Mage::helper('ratepaypayment')->getEdition(), // @ToDo: Move this to helper
                    'Version' => Mage::getVersion() . '_' . (string) Mage::getConfig()->getNode()->modules->RatePAY_Ratepaypayment->version // @ToDo: Move this to helper
                ]
            ]
        ];

        return $head;
    }
}
