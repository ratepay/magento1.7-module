<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Adminhtml_Ratepaypayment_ProfilerequestController extends Mage_Adminhtml_Controller_Action
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

        $request = Mage::getModel('ratepaypayment/libraryConnector', $credentials['sandbox'] == "1" ? true : false);

        // @ToDo: Move this to mapping helper
        $headInfo = array(
            'Credential' => array(
                'ProfileId' => $credentials['profile_id'],
                'Securitycode' => $credentials['security_code']
            )
        );

        $response = $request->callProfileRequest($headInfo);

        $coreConfig = Mage::getModel('core/config');

        if (!$response->isSuccessful()) {
            $coreConfig->saveConfig('payment/' . $method . '/status', 0);
            return Mage::helper('ratepaypayment')->__('Request Failed') . " (Reason Message: " . $response->getReasonMessage() . ")";
        }

        $result = $response->getResult();

        $merchantConfig = $result['merchantConfig'];
        $installmentConfig = $result['installmentConfig'];

        if (strstr(strtolower($merchantConfig['country-code-billing']), $country) == false) {
            return Mage::helper('ratepaypayment')->__('Country not supported by credentials');
        }

        if (strstr($method, "ratepay_rate0") && intval($installmentConfig['interestrate-max']) > 0) {
            return Mage::helper('ratepaypayment')->__('Interest Rate not supported by payment method');
        }

        $coreConfig->saveConfig('payment/' . $method . '/specificcountry_billing', $merchantConfig['country-code-billing']);
        $coreConfig->saveConfig('payment/' . $method . '/specificcountry_delivery', $merchantConfig['country-code-delivery']);
        $coreConfig->saveConfig('payment/' . $method . '/specificcurrency', $merchantConfig['currency']);

        if ($this->_getRpMethodWithoutCountry($method) != "ratepay_ibs") {
            $coreConfig->saveConfig('payment/' . $method . '/status', (($merchantConfig['merchant-status'] == 2) &&
                ($merchantConfig['activation-status-' . $product] != 1) &&
                ($merchantConfig['eligibility-ratepay-' . $product] == 'yes')) ? $merchantConfig['activation-status-' . $product] : 1);

            $coreConfig->saveConfig('payment/' . $method . '/min_order_total', $merchantConfig['tx-limit-' . $product . '-min']);
            $coreConfig->saveConfig('payment/' . $method . '/max_order_total', $merchantConfig['tx-limit-' . $product . '-max']);
            $coreConfig->saveConfig('payment/' . $method . '/b2b', ($merchantConfig['b2b-' . $product] == 'yes') ? 1 : 0);
            $coreConfig->saveConfig('payment/' . $method . '/limit_max_b2b', ($merchantConfig['tx-limit-' . $product . '-max-b2b'] > 0) ? $merchantConfig['tx-limit-' . $product . '-max-b2b'] : $merchantConfig['tx-limit-' . $product . '-max']);
            $coreConfig->saveConfig('payment/' . $method . '/delivery_address', ($merchantConfig['delivery-address-' . $product] == 'yes') ? 1 : 0);
        } else {
            $ibsSubtypes = array("full");
            foreach($ibsSubtypes as $subtype) {
                $coreConfig->saveConfig('payment/' . $method . '/status', ($merchantConfig['merchant-status'] == 2 && $merchantConfig['eligibility-ratepay-pq-' . $subtype] == 'yes') ? 1 : 0);
                $coreConfig->saveConfig('payment/' . $method . '/b2b', ($merchantConfig['b2b-PQ-' . $subtype] == 'yes'));
                $coreConfig->saveConfig('payment/' . $method . '/delivery_address', ($merchantConfig['delivery-address-PQ-' . $subtype] == 'yes'));
            }
        }

        if (strstr($method, "ratepay_rate")) {
            $coreConfig->saveConfig('payment/' . $method . '/month_allowed', $installmentConfig['month-allowed']);
            $coreConfig->saveConfig('payment/' . $method . '/rate_min', $installmentConfig['rate-min-normal']);
            $coreConfig->saveConfig('payment/' . $method . '/service_charge', $installmentConfig['service-charge']);
            $coreConfig->saveConfig('payment/' . $method . '/interestrate_default', $installmentConfig['interestrate-default']);
            $coreConfig->saveConfig('payment/' . $method . '/valid_payment_firstday', $installmentConfig['valid-payment-firstdays']);
        }

        return 1;
    }

    private function _getRpMethod($id) {
        return str_replace('payment_', '', $id);
    }

    private function _getRpMethodWithoutCountry($id) {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);
        $id = str_replace('_ch', '', $id);
        $id = str_replace('_nl', '', $id);
        $id = str_replace('_be', '', $id);
        $id = str_replace('0', '', $id);

        return $id;
    }

    private function _getRpCountry($id) {
        if(strstr($id, '_at')) {
            return 'at';
        }
        if(strstr($id, '_ch')) {
            return 'ch';
        }
        if(strstr($id, '_nl')) {
            return 'nl';
        }
        if(strstr($id, '_be')) {
            return 'be';
        }
        return 'de';
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
