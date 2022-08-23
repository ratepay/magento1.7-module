<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Logs_View_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('logs_view_tabs');
        $this->setDestElementId('logs_view');
        $this->setTitle(Mage::helper('ratepaypayment')->__('Logs Information View'));
    }

}