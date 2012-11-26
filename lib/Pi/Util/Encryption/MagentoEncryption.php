<?php

class Pi_Util_Encryption_MagentoEncryption extends Pi_Util_Encryption_EncryptionAbstract
{
    
    /**
     * Tablename where bankdata saved
     * 
     * @var string 
     */
    protected $_tableName = 'pi_ratepay_debitdetails';
    
    /**
     * Insert query
     * 
     * @param string $insertSql 
     */
    protected function _insertBankdataToDatabase($insertSql)
    {
        $write = Mage::getModel('core/resource')->getConnection('core_write');
        $write->query($insertSql);
    }

    /**
     * Retrieve selected bankdata
     * 
     * @param string $selectSql
     * @return array 
     */
    protected function _selectBankdataFromDatabase($selectSql)
    {
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        $sqlResult = $read->fetchRow($selectSql);

        $bankdata = array (
            'userid' => $this->_convertHexToBinary($sqlResult['userid']),
            'owner' => $this->_convertHexToBinary($sqlResult['decrypt_owner']),
            'accountnumber' => $this->_convertHexToBinary($sqlResult['decrypt_accountnumber']),
            'bankcode' => $this->_convertHexToBinary($sqlResult['decrypt_bankcode']),
            'bankname' => $this->_convertHexToBinary($sqlResult['decrypt_bankname'])
        );

        return $bankdata;
    }

    /**
     * Retrieve userId
     * 
     * @param string $userSql
     * @return integer
     */
    protected function _selectUserIdFromDatabase($userSql)
    {
        $read = Mage::getModel('core/resource')->getConnection('core_read');
        $userId = $read->fetchOne($userSql);

        return $userId;
    }
}