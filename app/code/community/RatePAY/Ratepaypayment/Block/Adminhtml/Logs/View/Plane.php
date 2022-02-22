<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Logs_View_Plane extends Mage_Adminhtml_Block_Widget_Form
{
        /**
         * Prepare form before rendering HTML
         *
         * @return RatePAY_Ratepaypayment_Block_Adminhtml_Logs_View_Plane
         */
	protected function _prepareForm()
	{
            $this->setTemplate('ratepay/logs/view.phtml');
            return parent::_prepareForm();
	}

        /**
         * Returns Logging Model
         *
         * @return RatePAY_Ratepaypayment_Model_Logging
         */
	public function getEntry()
	{
	    return Mage::registry('ratepay_logging_data');
	}

        /**
         * Gets the formatted Request Xml
         *
         * @return string
         */
	public function getRequest()
	{
	    return $this->_formatXml($this->getEntry()->getRequest());
	}

        /**
         * Gets the formatted Response Xml
         *
         * @return string
         */
	public function getResponse()
	{
	    return $this->_formatXml($this->getEntry()->getResponse());
	}

        /**
         * Formats Xml
         *
         * @return string
         */
	protected function _formatXml($xmlString)
	{
            $str = str_replace("\n", "", $xmlString);
            $xml = new DOMDocument('1.0');
            $xml->preserveWhiteSpace = false;
            $xml->formatOutput = true;
            $xml->loadXML($str);
            $var = $xml->saveXML();
	    return htmlentities($var);
	}

}