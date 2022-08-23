<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_System_Config_Form_Button_Profilerequest
        extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_element = null;

    /*
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ratepay/system/config/profilerequest.phtml');
    }

    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/ratepaypayment_profilerequest/callProfileRequest');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'ratepaypayment_profilerequest',
                'label'     => Mage::helper('ratepaypayment')->__('Get Config'),
                'onclick'   => 'callRpProfileRequest(\'' . $this->_element->getContainer()->getId() . '\'); return false;'
            ));

        return $button->toHtml();
    }
}
