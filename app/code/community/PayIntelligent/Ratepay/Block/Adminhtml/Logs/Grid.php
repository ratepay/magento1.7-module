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
class PayIntelligent_Ratepay_Block_Adminhtml_Logs_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Is filter allowed
     * 
     * @var boolean
     */
    protected $_isFilterAllowed = true;

    /**
     * Is sortable
     * 
     * @var boolean 
     */
    protected $_isSortable = true;

    /**
     * Construct
     */
    public function __construct()
    {

        $this->setId('logs_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

        parent::__construct();
    }

    /**
     * Is filter allowed
     */
    protected function _isFilterAllowed()
    {
        return $this->_isFilterAllowed;
    }

    /**
     * Is sortable
     */
    protected function _isSortable()
    {
        return $this->_isSortable;
    }

    /**
     * Retrive massaction block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction
     */
    public function getMassactionBlock()
    {
        return $this->getChild('massaction')->setErrorText(Mage::helper('ratepay')->jsQuoteEscape(Mage::helper('ratepay')->__('Pi Please select a logentry.')));
    }

    /**
     * Prepare Collection
     *
     * @return PayIntelligent_Ratepay_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('ratepay/logging')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return PayIntelligent_Ratepay_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('ratepay')->__('Pi Id'),
            'index' => 'id',
        ));

        $this->addColumn('order_number', array(
            'header' => Mage::helper('ratepay')->__('Pi Ordernumber'),
            'index' => 'order_number',
        ));

        $this->addColumn('transaction_id', array(
            'header' => Mage::helper('ratepay')->__('Pi Transaction-Id'),
            'index' => 'transaction_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('ratepay')->__('Pi Customer name'),
            'index' => 'name',
        ));

        $this->addColumn('payment_method', array(
            'header' => Mage::helper('ratepay')->__('Pi Payment method'),
            'index' => 'payment_method',
        ));

        $this->addColumn('payment_type', array(
            'header' => Mage::helper('ratepay')->__('Pi Request type'),
            'index' => 'payment_type',
        ));

        $this->addColumn('payment_subtype', array(
            'header' => Mage::helper('ratepay')->__('Pi Request subtype'),
            'index' => 'payment_subtype',
        ));

        $this->addColumn('result', array(
            'header' => Mage::helper('ratepay')->__('Pi Result message'),
            'index' => 'result',
        ));

        $this->addColumn('result_code', array(
            'header' => Mage::helper('ratepay')->__('Pi Result code'),
            'index' => 'result_code',
        ));

        $this->addColumn('result_code_text', array(
            'header' => Mage::helper('ratepay')->__('Pi Result'),
            'index' => 'result_code',
            'sortable' => false,
            'renderer' => 'ratepay/adminhtml_logs_grid_renderer_result',
        ));

        $this->addColumn('reason', array(
            'header' => Mage::helper('ratepay')->__('Pi Reason'),
            'index' => 'reason',
        ));

        $this->addColumn('date', array(
            'header' => Mage::helper('ratepay')->__('Pi Date'),
            'index' => 'date',
        ));
        return parent::_prepareColumns();
    }

    /**
     * Gets Row Url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }

    /**
     * Prepares Massaction for deletion of Logentries
     *
     * @return PayIntelligent_Ratepay_Block_Adminhtml_Logs_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('log_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('ratepay')->__('Pi Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('ratepay')->__('Pi Are you sure?'),
        ));

        $this->getMassactionBlock()->addItem('delete_old', array(
            'label' => Mage::helper('ratepay')->__('Pi Delete old entries'),
            'url' => $this->getUrl('*/*/massDeleteExtended'),
            'confirm' => Mage::helper('ratepay')->__('Pi Are you sure?'),
            'additional' => array(
                'visibility' => array(
                    'name' => 'days',
                    'type' => 'text',
                    'class' => 'required-entry',
                    'maxlength' => 4,
                    'style' => 'width: 30px;',
                    'label' => Mage::helper('ratepay')->__('Pi All entries, which are older then x days'),
                )
            )
        ));

        return $this;
    }

}