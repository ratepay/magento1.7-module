<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Logs_View extends Mage_Adminhtml_Block_Widget_View_Container
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->_objectId    = 'id';
        $this->_controller  = 'adminhtml_logs';
        $this->_mode        = 'view';
        $this->_headerText  = Mage::helper('ratepaypayment')->__('Log');

        parent::__construct();

        $this->_removeButton('edit');
    }

    /**
     * @see Mage_Adminhtml_Block_Widget_View_Container::_prepareLayout()
     */
    protected function _prepareLayout()
    {
        $this->setChild('plane', $this->getLayout()->createBlock('ratepaypayment/' . $this->_controller . '_view_plane'));
    }

}