<?php
/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RatePAY_Ratepaypayment_Model_LibraryConnectorFrontend extends RatePAY_Ratepaypayment_Model_LibraryConnectorAbstract
{
    public function deviceFingerprint($snippetId, $uniqueIdentifier)
    {
        // Set library as autoloader (and remove mage autoloader) while instancing library classes
        $this->setLibAutoloader();

        $deviceFingerprint = new RatePAY\Frontend\DeviceFingerprintBuilder($snippetId, $uniqueIdentifier);

        // Switch back to mage autoloader
        $this->removeLibAutoloader();

        return array(
            'token' => $deviceFingerprint->getToken(),
            'dfpSnippetCode' => $deviceFingerprint->getDfpSnippetCode()
        );
    }
}
