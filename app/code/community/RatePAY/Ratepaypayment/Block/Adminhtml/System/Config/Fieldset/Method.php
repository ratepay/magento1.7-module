<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_System_Config_Fieldset_Method
        extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $group = (array) $element->getGroup();
        $logo = $group['method'];

        return '<div class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
        . '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
        . $this->getUrl('*/*/state') . '\'); return false;"><img src=' . $this->getSkinUrl('images/ratepay/' . $logo . '.png', array('_secure' => true)) . ' alt="" /></a></div>';

    }

}
