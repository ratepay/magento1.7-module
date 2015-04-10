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

class RatePAY_Ratepaypayment_Block_Adminhtml_Bulk_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('bulk_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }

    /**
     * Prepare the order collection
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid 
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $paymentTable = Mage::getSingleton("core/resource")->getTableName('sales_flat_order_payment');
        $where = '(method like "ratepay_rechnung" or method like "ratepay_rate" or method like "ratepay_directdebit") '
                . 'and (main_table.status not like "canceled" and main_table.status not like "closed" and main_table.status not like "complete")';
        $collection->getSelect()
                     ->join($paymentTable, "parent_id = main_table.entity_id", array("*", "main_table.*"))
                     ->where($where);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * Prepare the columns of the grid
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid 
     */
    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        return parent::_prepareColumns();
    }
    
   /**
     * Prepares Massaction for deletion of Logentries
     *
     * @return RatePAY_Ratepaypayment_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_id');

        $this->getMassactionBlock()->addItem('cancel', array(
             'label'=> Mage::helper('ratepaypayment')->__('Cancel'),
             'url'  => $this->getUrl('*/*/massCancel'),
             'confirm' => Mage::helper('ratepaypayment')->__('Are you sure?'),
        ));
        
        $this->getMassactionBlock()->addItem('invoice', array(
             'label'=> Mage::helper('ratepaypayment')->__('Invoicing'),
             'url'  => $this->getUrl('*/*/massInvoice'),
             'confirm' => Mage::helper('ratepaypayment')->__('Are you sure?'),
        ));
        
        $this->getMassactionBlock()->addItem('creditmemo', array(
             'label'=> Mage::helper('ratepaypayment')->__('Creditmemo'),
             'url'  => $this->getUrl('*/*/massCreditmemo'),
             'confirm' => Mage::helper('ratepaypayment')->__('Are you sure?'),
        ));
        return $this;
    }
} 