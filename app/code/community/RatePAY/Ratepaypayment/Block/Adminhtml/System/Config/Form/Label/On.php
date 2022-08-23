<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_System_Config_Form_Label_On
        extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_element = null;

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $elementData = $element->getData();
        $status = ((int) $elementData['value'] == 1);
        $text = ($status) ? 'Yes' : 'No';
        return Mage::helper('ratepaypayment')->__($text);
    }
}
