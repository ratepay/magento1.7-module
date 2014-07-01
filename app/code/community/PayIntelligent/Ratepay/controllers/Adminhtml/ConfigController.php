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
 * @category PayIntelligent
 * @package PayIntelligent_RatePAY
 * @copyright Copyright (c) 2011 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

class PayIntelligent_Ratepay_Adminhtml_ConfigController extends Mage_Adminhtml_Controller_Action
{
    
    /**
     * Initialize config view
     * 
     * @return PayIntelligent_Ratepay_Adminhtml_ConfigController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('config/ratepay');
        return $this;
    }
    
    /**
     * Render the config layout
     * 
     * @return PayIntelligent_Ratepay_Adminhtml_ConfigController
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
}