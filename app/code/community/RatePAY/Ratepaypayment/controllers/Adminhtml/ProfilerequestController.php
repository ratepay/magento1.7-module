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

class RatePAY_Ratepaypayment_Adminhtml_ProfilerequestController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return some checking result
     *
     * @return void
     */
    public function callProfileRequestAction()
    {
        $credentials = array();
        $credentials['profile_id'] = $this->getRequest()->getParam('profile_id');
        $credentials['security_code'] = $this->getRequest()->getParam('security_code');
        $credentials['sandbox'] = $this->getRequest()->getParam('sandbox');
        $credentials['method'] = $this->_getRpMethod($this->getRequest()->getParam('method'));

        Mage::app()->getResponse()->setBody($this->_callProfileRequest($credentials));
    }

    private function _callProfileRequest($credentials) {
        $method = $credentials['method'];
        $product = Mage::helper('ratepaypayment/payment')->convertMethodToProduct($this->_getRpMethodWithoutCountry($method));
        $country = $this->_getRpCountry($method);

        $client = Mage::getModel('ratepaypayment/request');

        $headInfo = array(
            'securityCode' => $credentials['security_code'],
            'profileId' => $credentials['profile_id']
        );

        $loggingInfo = array(
            'logging'       => false,
            'requestType'   => 'PROFILE_REQUEST',
            'sandbox'       => ($credentials['sandbox'] == "1") ? 1 : 0
        );

        $result = $client->callProfileRequest($headInfo, $loggingInfo);

        $coreConfig = Mage::getModel('core/config');

        if (!is_array($result)) {
            $coreConfig->saveConfig('payment/' . $method . '/status', 0);
            return Mage::helper('ratepaypayment')->__('Request Failed');
        }

        $merchantConfig = $result['merchant_config'];
        $installmentConfig = $result['installment_config'];

        if (strstr(strtolower($merchantConfig['country-code-billing']), $country) == false) {
            return Mage::helper('ratepaypayment')->__('Country is not supported by credentials');
        }

        $coreConfig->saveConfig('payment/' . $method . '/specificcountry_billing', $merchantConfig['country-code-billing']);
        $coreConfig->saveConfig('payment/' . $method . '/specificcountry_delivery', $merchantConfig['country-code-delivery']);

        if ($this->_getRpMethodWithoutCountry($method) != "ratepay_ibs") {
            $coreConfig->saveConfig('payment/' . $method . '/status', (($merchantConfig['merchant-status'] == 2) &&
                ($merchantConfig['activation-status-' . $product] == 2) &&
                ($merchantConfig['eligibility-ratepay-' . $product] == 'yes')) ? 1 : 0);

            $coreConfig->saveConfig('payment/' . $method . '/min_order_total', $merchantConfig['tx-limit-' . $product . '-min']);
            $coreConfig->saveConfig('payment/' . $method . '/max_order_total', $merchantConfig['tx-limit-' . $product . '-max']);
            $coreConfig->saveConfig('payment/' . $method . '/b2b', ($merchantConfig['b2b-' . $product] == 'yes') ? 1 : 0);
            $coreConfig->saveConfig('payment/' . $method . '/delivery_address', ($merchantConfig['delivery-address-' . $product] == 'yes') ? 1 : 0);
        } else {
            $ibsSubtypes = array("full");
            foreach($ibsSubtypes as $subtype) {
                $coreConfig->saveConfig('payment/' . $method . '/status', ($merchantConfig['merchant-status'] == 2 && $merchantConfig['eligibility-ratepay-pq-' . $subtype] == 'yes') ? 1 : 0);
                $coreConfig->saveConfig('payment/' . $method . '/b2b', ($merchantConfig['b2b-PQ-' . $subtype] == 'yes'));
                $coreConfig->saveConfig('payment/' . $method . '/delivery_address', ($merchantConfig['delivery-address-PQ-' . $subtype] == 'yes'));
            }
        }

        if ($this->_getRpMethodWithoutCountry($method) == "ratepay_rate") {
            $coreConfig->saveConfig('payment/' . $method . '/month_allowed', $installmentConfig['month-allowed']);
            $coreConfig->saveConfig('payment/' . $method . '/rate_min', $installmentConfig['rate-min-normal']);
        }

        $coreConfig->saveConfig('payment/ratepay_general/device_ident', ($merchantConfig['eligibility-device-fingerprint'] == "yes") ? 1 : 0);

        return 1;
    }

    private function _getRpMethod($id) {
        return str_replace('payment_', '', $id);
    }

    private function _getRpMethodWithoutCountry($id) {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);

        return $id;
    }

    private function _getRpCountry($id) {
        if(strstr($id, '_at')) {
            return 'at';
        }
        return 'de';
    }
}