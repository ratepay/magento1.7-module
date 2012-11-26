<?php

class Pi_Util_Encryption_PrivateKey
{
    private $_keyPath;

    public function __construct($pathModifier = null)
    {
        $pathModifier = isset($pathModifier)? $pathModifier : '/../../../';
        $this->_keyPath = dirname(__FILE__) . $pathModifier . 'piPrivateKey.php';
    }

    /*
     * Gets private key from piPrivateKey.php
     *
     * @return  String PI_PRIVATE_KEY     private key
     */
    public function getPrivateKey()
    {
        if (!file_exists($this->_keyPath)) {
            $this->createPrivateKey();
        }
        require_once $this->_keyPath;
        return PI_PRIVATE_KEY;
    }

    /*
     * Creates file with random private key
     */
    private function createPrivateKey()
    {
        $datei = fopen($this->_keyPath, 'w');
        fwrite($datei, '<?php DEFINE ("PI_PRIVATE_KEY", "'
            . $this->createRandomString(15, true)
            . $this->createRandomString(35, false)
            . $this->createRandomString(14, true) . '");'
            .' ?>');
    }

  /**
   * Generate a random string with variable length and optional
   * number and/or special characters
   *
   * @param int $length
   * @param bool $useNumbers
   *
   * @return string
   */
    public function createRandomString($length, $useNumbers = false)
    {
        $secret = '';
        $key = 0;
        $lastKey = -1;
        $chars = range ( 0 , 9 );
        $numbers = range ( 'a' , 'z' );
        if ($useNumbers) {
            $chars = array_merge($chars, $numbers);
        }
        shuffle($chars);
        for ($index = 1; $index <= (int) $length; $index++) {
            $key = array_rand($chars, 1);
            if ($key == $lastKey) {
                continue;
            }
            if (0 == ($key % 2)) {
                $secret .= $chars[$key];
            } else {
                $secret .= strtoupper($chars[$key]);
            }
            $lastKey = $key;
        }
        return (string) $secret;
    }
}
