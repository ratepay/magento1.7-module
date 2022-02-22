<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Logs extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->_blockGroup = 'ratepaypayment';
        $this->_controller = 'adminhtml_logs';
        $this->_headerText = Mage::helper('ratepaypayment')->__('Ratepay Logs');
        parent::__construct();
    }

    /**
     * @see Mage_Adminhtml_Block_Widget_Grid_Container::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }
}