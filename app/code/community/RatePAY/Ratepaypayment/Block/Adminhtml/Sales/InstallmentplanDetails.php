<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Block_Adminhtml_Sales_InstallmentplanDetails extends Mage_Core_Block_Template
{
    /** @var string */
    protected $_template = "ratepay/sales/installementplandetails.phtml";

    /** @var array */
    protected $_errors = array();

    /**
     * @return string
     */
    public function renderView()
    {
        return parent::renderView();
    }

    /**
     * @param string $title
     * @param string $message
     */
    public function _addError($title, $message)
    {
        $this->_errors[] = (object) array(
            'title' => $title,
            'message' => $message
        );
    }
}
