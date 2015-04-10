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

class RatePAY_Ratepaypayment_Model_Request_Communication extends Zend_Http_Client
{
    
    /**
     * RatePAY live url
     * 
     * @var string 
     */
    private $liveServer = 'https://gateway.ratepay.com/api/xml/1_0';

    /**
     * RatePAY test url
     * 
     * @var string
     */
    private $testServer = 'https://gateway-int.ratepay.com/api/xml/1_0';

    /**
     * Test mode
     * 
     * @var boolean 
     */
    private $testMode = false;
    
    /**
     * Curl config array
     * 
     * @var array
     */
    protected $config = array(
        'strictredirects' => false,
        'adapter' => 'Zend_Http_Client_Adapter_Curl',
        'curloptions' => array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSLVERSION => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array (
                "Content-Type: text/xml; charset=UTF-8",
                "Accept: */*",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Connection: keep-alive"
            )
        ),
        'maxredirects'    => 5,
        'useragent'       => 'Zend_Http_Client',
        'timeout'         => 10,
        'httpversion'     => self::HTTP_1,
        'keepalive'       => false,
        'storeresponse'   => true,
        'strict'          => true,
        'output_stream'   => false,
        'encodecookies'   => true,
        'rfc3986_strict'  => false
    );

    /**
     * Constructs Object with Server URL and Configs
     *
     * @param array
     */
    public function __construct($array = false)
    {
        if($array != false) {
            $this->setTestMode($array[0]);
        }
        parent::__construct($this->getServerUrl(), $this->getConfig());
    }

    /**
     * Sets the Testmode to on/off
     *
     * @param boolean
     */
    public function setTestMode($testMode = false)
    {
        $this->testMode = $testMode;
    }

    /**
     * Get the Testmode on or off
     *
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->testMode;
    }

    /**
     * Returns the Server URL depending on Testmode
     *
     * @return boolean
     */
    public function getServerUrl()
    {
        if ($this->getTestMode()) {
            return $this->testServer;
        } else {
            return $this->liveServer;
        }
    }

    /**
     * Returns the Config of the Adapter
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
