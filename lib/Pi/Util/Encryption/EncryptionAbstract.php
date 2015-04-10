<?php

/*
 * creates private key
 */

abstract class Pi_Util_Encryption_EncryptionAbstract
{
    /**
     * Service responsible for private key handling.
     * @var Pi_Util_Encryption_PrivateKey
     */
    private $_privateKeyService;

    private $_privateKey;

    protected $_tableName = 'ratepay_debitdetails';

    public function __construct(Pi_Util_Encryption_PrivateKey $privateKeyService = null)
    {
        $this->_privateKeyService = isset($privateKeyService)? $privateKeyService : new Pi_Util_Encryption_PrivateKey();
        $this->_privateKey = $this->_privateKeyService->getPrivateKey();
    }

    public function loadBankdata($userId)
    {
        $selectSql = $this->_createBankdataSelectSql($userId);
        $bankdata = $this->_selectBankdataFromDatabase($selectSql);

        return $bankdata;
    }

    public function saveBankdata($userId, array $bankdata)
    {
        if ($this->isBankdataSetForUser($userId)) {
            $saveSql = $this->_createBankdataUpdateSql($userId, $bankdata);
        } else {
            $saveSql = $this->_createBankdataInsertSql($userId, $bankdata);
        }
        $this->_insertBankdataToDatabase($saveSql);
    }

    private function _createBankdataInsertSql($userId, array $bankdata)
    {
        $insertSql = 'INSERT INTO ' . $this->_tableName . ' (userid, ';
        $key = $this->_privateKey;
        $arr = array_keys($bankdata);
        $lastArrayKey = array_pop($arr);

        foreach($bankdata as $columnName => $columnValue) {
            $insertSql .= $columnName;
            $insertSql .= $lastArrayKey != $columnName? ', ' : ')';
        }

        $insertSql .= ' Values (' . "'" . $userId . "', ";

        foreach($bankdata as $columnName => $columnValue) {
            $insertSql .= "AES_ENCRYPT('" . $this->_convertBinaryToHex($columnValue) . "', '" . $key . "')";
            $insertSql .= $lastArrayKey != $columnName? ', ' : ')';
        }

        return $insertSql;
    }

    private function _createBankdataUpdateSql($userId, array $bankdata)
    {
        $updateSql = 'UPDATE ' . $this->_tableName . ' SET ';
        $key = $this->_privateKey;
        $arr = array_keys($bankdata);
        $lastArrayKey = array_pop($arr);
        
        foreach($bankdata as $columnName => $columnValue) {
            $updateSql .= $columnName . " = AES_ENCRYPT('" . $this->_convertBinaryToHex($columnValue) . "', '" . $key . "')";
            $updateSql .= $lastArrayKey != $columnName? ', ' : ' ';
        }

        $updateSql .= ' where userid = ' . "'" . $userId . "'";

        return $updateSql;
    }

    public function isBankdataSetForUser($userId)
    {
        $sanitizedString = $userId;
        $userSql = "Select userid from " . $this->_tableName . " where userid = '$sanitizedString'";
        $userIdStoredInDb = $this->_selectUserIdFromDatabase($userSql);

        return $userId === $userIdStoredInDb;
    }

    private function _createBankdataSelectSql($userId)
    {
        $key = $this->_privateKey;
        $selectSql = "SELECT
                        userid,
                        AES_DECRYPT(owner, '$key') AS decrypt_owner,
                        AES_DECRYPT(accountnumber, '$key') AS decrypt_accountnumber,
                        AES_DECRYPT(iban, '$key') AS decrypt_iban,
                        AES_DECRYPT(bankcode, '$key') AS decrypt_bankcode,
                        AES_DECRYPT(bic, '$key') AS decrypt_bic,
                        AES_DECRYPT(bankname, '$key') AS decrypt_bankname
                      FROM " . $this->_tableName . " WHERE userid = '$userId'";
        return $selectSql;
    }
    
    protected function _convertBinaryToHex($value)
    {
        $toHex = bin2hex($value);
        
        return $toHex;
    }
    
    protected function _convertHexToBinary($value)
    {
        $toBinary = pack("H*", $value);
        
        return $toBinary;
    }

    abstract protected function _insertBankdataToDatabase($insertSql);

    abstract protected function _selectBankdataFromDatabase($selectSql);

    abstract protected function _selectUserIdFromDatabase($userSql);
}
