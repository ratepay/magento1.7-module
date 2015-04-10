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
class RatePAY_Ratepaypayment_Block_Adminhtml_System_Config_Fieldset_Mainsection
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
        return '<div class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
        . '-head" href="#" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
        . $this->getUrl('*/*/state') . '\'); return false;"><img src=' . $this->getSkinUrl('images/ratepay/ratepay.png', array('_secure' => true)) . ' alt="" /></a></div>';

    }

}
