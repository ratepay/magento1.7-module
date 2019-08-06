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
 * @copyright Copyright (c) 2019 RatePAY GmbH (https://www.ratepay.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class RatePAY_Ratepaypayment_Block_Checkout_InstallmentplanDetails extends Mage_Core_Block_Template
{
    /** @var string */
    protected $_template = "ratepay/checkout/installementplandetails.phtml";

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
