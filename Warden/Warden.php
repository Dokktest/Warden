<?php
/**
* Warden.php
* 
* Warden user authentication
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
*/

/**
* Class responsible for authentication
* 
* @author Barry O'Mahony <the.ewok@gmail.com>
* @version 1.0
* @package Warden
* @subpackage classes
*/

namespace Warden;

class Warden
{
    /**
     * Path to ini
     * @access private
     * @var string
     */
    private $iniPath;
   
    /**
     * Parsed ini
     * @access private
     * @var array
     */
    private $ini;
    
    /**
     * Hash object with HashInterface
     * @access private
     * @var object
     */
    private $hash;

    /**
     * Salt object with SaltInterface
     * @access private
     * @var object
     */
    private $salt;

    /**
     * Constructor
     * @param string $iniPath
     * @param PDO $databaseHandle
     * @param HashInteface $hash
     * @param SaltInterface $salt
     */ 
    public function __construct($iniPath,\PDO $databaseHandle,HashInterface $hash=null, SaltInterface $salt=null) {
        $this->dbh = $databaseHandle;
        $this->iniPath = $iniPath;
        
        //Check if APC is installed!
        $apcLoaded = extension_loaded('apc') && ini_get('apc.enabled');

        $this->loadIni($apcLoaded); 

        if(!$this->tableExists($apcLoaded)) {
            $this->createTable($apcLoaded);
        }

        if($hash==null) {
            $this->hash = new Hash();
        } else {
            $this->hash = $hash;
        }

        if($salt==null) {
            $this->salt = new Salt();
        } else {
            $this->salt = $salt;
        }
   
    }

    /**
     * Save the user
     * @param stdClass $user
     */
    public function save(\stdClass $user)
    {
        $primaryKey = $this->ini['db_config']['primary_key'];
        if(isset($user->$primaryKey)) {
            $this->updateUser($user,true);
        } else {
            $this->updateUser($user,false);
        }
    }

    /**
     * Find a user by the primary key
     * @param mixed $id
     * @return stdClass 
     */
    public function findByPrimaryKey($id) {
        $sql = "SELECT ".implode(',',$this->ini['db_config']['fields']).
            " FROM ".$this->ini['db_config']['table'].
            " WHERE ".$this->ini['db_config']['primary_key'].
            " = ? LIMIT 1";
        $statement = $this->dbh->prepare($sql);
        $statement->execute(array($id));
        
        $result = $statement->fetch();

        $return = new \stdClass();

        foreach($this->ini['db_config']['fields'] as $field) {
            $return->$field = $result[$field];
        }

        return $return;
    } 

    /**
     * Find a user by the alternative key
     * @param string $key
     * @param mixed $value
     * @return stdClass 
     */
    public function findByAltKey($key,$value)
    {
        $sql = "SELECT ".implode(',',$this->ini['db_config']['fields']).
            " FROM ".$this->ini['db_config']['table'].
            " WHERE ".$key.
            " = ? LIMIT 1";
        $statement = $this->dbh->prepare($sql);
        $statement->execute(array($value));
        
        $result = $statement->fetch();

        $return = new \stdClass();

        foreach($this->ini['db_config']['fields'] as $field) {
            $return->$field = $result[$field];
        }

        return $return;

    }

    /**
     * Set the password for the user
     * @param string $password
     * @param stdClass $user
     */
    public function setPassword($password,$user) {
        $credential = $this->ini['credentials']['credential'];
        $user->salt = $this->salt->getSalt();
        $user->$credential = $this->hash->hashString($password,$user->salt);        

    }

    /**
     * Delete the user from the database
     * @param stdClass $user
     */
    public function delete($user) {
        
        $primaryKey = $this->ini['db_config']['primary_key'];
        $sql = "DELETE FROM ".$this->ini['db_config']['table'].
            " WHERE ". $primaryKey .
            " = ?";

        $statement = $this->dbh->prepare($sql);
        return $statement->execute(array($user->$primaryKey));
    }

    /**
     * Authenticate the user using the supplied Hash algorithm
     * @param string $password
     * @param stdClass $user
     * @return boolean
     */
    public function authenticate($password,$user) {
        $credential = $this->ini['credentials']['credential'];
        $checkPass = $this->hash->hashString($password,$user->salt);
        return $checkPass==$user->$credential;
    }

    /**
     * Create or update the user
     * @param stdClass $user
     * @param bool $update
     */
    private function updateUser($user,$update)
    {
        $setValues = array();

        foreach($this->ini['db_config']['fields'] as $field) {
            if($field!==$this->ini['db_config']['primary_key']) {
                $setValueString[] = $field." = :".$field;
            }
            $setValues[':'.$field] = $user->$field;
        }
        if(!$update) {
            unset($setValues[':'.$this->ini['db_config']['primary_key']]);
        }

        $setString = implode(',',$setValueString);

        $whereString = " WHERE ".$this->ini['db_config']['primary_key']."= :".$this->ini['db_config']['primary_key'];

        if($update) {
            $sql = "UPDATE ";
        } else {
            $sql = "INSERT INTO ";
        }
        
        $sql .= $this->ini['db_config']['table']." SET ";
        $sql .= $setString;
        
        if($update) {
            $sql .= $whereString;
        }
        var_dump($sql);
        var_dump($setValues);

        $statement = $this->dbh->prepare($sql);
        $statement->execute($setValues); 

    }

    /**
     * Load the ini file or get from APC
     * @param bool $apc
     */ 
    private function loadIni($apc) 
    {
        if($apc) {
            if(apc_exists('warden_ini')) {
                $this->ini = apc_fetch('warden_ini');
                return true;
            } 
        } else {
            error_log("Warden - You really should install APC.");
        }

        if(file_exists($this->iniPath)) {
            $this->ini = parse_ini_file($this->iniPath,true);
        } else {
            throw new Exception('\\Warden - File not found - '.$iniPath);
        }

        if($apc) {
            apc_add('warden_ini',$this->ini);
        }

    }

    /**
     * Check if database table exists
     * @param bool $apc
     */
    private function tableExists($apc) 
    {
        if($apc) {
            if(apc_exists('warden_table_exist')) {
                return true;
            }
        }
        //Check if table exists
        $query = "SELECT ".$this->ini['db_config']['primary_key'].
                 " FROM ".$this->ini['db_config']['table'].
                 " LIMIT 1";
        $table_response = $this->dbh->query($query);

        return !$table_response===false;
    }
    
    /**
     * Create table
     * @param bool $apc
     */
    private function createTable($apc)
    {
        $query = "CREATE TABLE `".$this->ini['db_config']['table']."` (";
        
        $fields = array();

        foreach($this->ini['db_config']['fields'] as $key => $field) {
            $fields[] = '`'.$field."` ".$this->ini['db_config']['types'][$key];
        }

        $fields[] = "PRIMARY KEY(`".$this->ini['db_config']['primary_key']."`)";

        foreach($this->ini['db_config']['indexes'] as $index) {
            $fields[] = "KEY `".$index."` (`".$index."`)";
        }

        $query .= implode(',',$fields).")";

        $this->dbh->exec($query);
        apc_add('warden_table_exist',true);

    }
        
}


