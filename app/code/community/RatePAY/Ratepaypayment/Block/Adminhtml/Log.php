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

class RatePAY_Ratepaypayment_Block_Adminhtml_Log
    extends RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Is filter allowed
     * 
     * @var boolean 
     */
    protected $_isFilterAllowed = false;
    
    /**
     * Is sortable
     * 
     * @var boolean
     */
    protected $_isSortable = false;
    
    /**
     * set template for the RatePAY logging on the order backend
     */
    public function __construct()
    {
        parent::__construct();
        if ($this->isLogEntry()) {
            $this->setTemplate('ratepay/sales/order/view/tab/log.phtml');
        }
    }
    
   /**
     * This method returns the value for the tab label in the order backend
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('core')->__('RatePAY Log');
    }

    /**
     * This method returns the value for the tab title in the order backend
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('core')->__('RatePAY Log');
    }

    /**
     * This method navigate if the tab RatePAY is shown in the order backend
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * This method navigate if the tab RatePAY is shown in the order backend
     *
     * @return boolean
     */
    public function isHidden()
    {
        return !Mage::helper('ratepaypayment/payment')->isRatepayPayment($this->getOrder()->getPayment()->getMethod());
    }
    
    /**
     * Retrieve order instace
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        return Mage::getModel('sales/order')->load($id);
    }
    
    /**
     * Prepare Collection
     *
     * @return RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareCollection()
    {
        
        if (!$this->isLogEntry()) {
            $collection = Mage::getModel('ratepaypayment/logging')->getCollection();
            $this->setCollection($collection);
            $this->getCollection()->addFilter('order_number', $this->getOrder()->getIncrementId());
        }
        return $this;
    }
    
    /**
     * Is log entry
     * 
     * @return boolean 
     */
    protected function isLogEntry()
    {
        $id = $this->getRequest()->getParam('log_id');
        if (empty($id)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Gets Row Url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array ('log_id' => $row->getId(), 'order_id' => $this->getOrder()->getId(), 'active_tab' => 'order_log_ratepay'));
    }
    
    /**
     * Retrieve log url
     * 
     * @return string
     */
    public function getLogUrl()
    {
        return $this->getUrl('*/*/view', array ('order_id' => $this->getOrder()->getId(), 'active_tab' => 'order_log_ratepay'));
    }
    
    /**
     * Retrive Request XML
     * 
     * @return string 
     */
    public function getRequestXml()
    {
        $log = Mage::getModel('ratepaypayment/logging')->load($this->getRequest()->getParam('log_id'));
        return $this->_formatXml($log->getRequest());
    }
    
    /**
     * Retrive Request XML
     * 
     * @return string 
     */
    public function getResponseXml()
    {
        $log = Mage::getModel('ratepaypayment/logging')->load($this->getRequest()->getParam('log_id'));
        return $this->_formatXml($log->getResponse());
    }
    
    /**
     * Formats Xml
     *
     * @return string
     */
    protected function _formatXml($xmlString)
    {
        $str = str_replace("\n", "", $xmlString);
        $xml = new DOMDocument('1.0');
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $xml->loadXML($str);
        $var = $xml->saveXML();
        return htmlentities($var);
    }
    
    /**
     * Prepares Massaction for deletion of Logentries
     *
     * @return RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }
    
    /**
     * Retrieve reset filter button html
     *  
     * @return string 
     */
    public function getResetFilterButtonHtml()
    {
        return '';
    }

    /**
     * Retrieve search button html
     * 
     * @return string 
     */
    public function getSearchButtonHtml()
    {
        return '';
    }
    
    /**
     * Prepare Columns
     *
     * @return RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ratepaypayment')->__('Id'),
            'index'     => 'id',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('transaction_id', array(
            'header'    => Mage::helper('ratepaypayment')->__('Transaction-Id'),
            'index'     => 'transaction_id',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('ratepaypayment')->__('Customer name'),
            'index'     => 'name',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('payment_method', array(
            'header'    => Mage::helper('ratepaypayment')->__('Payment method'),
            'index'     => 'payment_method',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('payment_type', array(
            'header'    => Mage::helper('ratepaypayment')->__('Request type'),
            'index'     => 'payment_type',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('payment_subtype', array(
            'header'    => Mage::helper('ratepaypayment')->__('Request subtype'),
            'index'     => 'payment_subtype',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('result', array(
            'header'    => Mage::helper('ratepaypayment')->__('Result message'),
            'index'     => 'result',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('result_code', array(
            'header'    => Mage::helper('ratepaypayment')->__('Result code'),
            'index'     => 'result_code',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('result_code_text', array(
            'header'    => Mage::helper('ratepaypayment')->__('Result'),
            'index'     => 'result_code',
            'sortable'  => false,
            'renderer'  => 'ratepaypayment/adminhtml_logs_grid_renderer_result',
            'filter'    => $this->_isFilterAllowed(),
        ));

        $this->addColumn('reason', array(
            'header'    => Mage::helper('ratepaypayment')->__('Reason'),
            'index'     => 'reason',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));

        $this->addColumn('date', array(
            'header'    => Mage::helper('ratepaypayment')->__('Date'),
            'index'     => 'date',
            'filter'    => $this->_isFilterAllowed(),
            'sortable'  => $this->_isSortable()
        ));
    }
}
