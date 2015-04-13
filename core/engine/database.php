<?php

/**
 * This class provides method to generate html from templates
 *
 */
class database {

    /** database connection @type ArrayObject $connections. */
    private $connections = array(); // all connections
    /** active connection @type int $activeConnection. */
    private $activeConnection = 0; // current connection
    /** cached queries @type ArrayObject $queryCache. */
    private $queryCache = array(); // cache to store queries
    /** cached data @type ArrayObject $dataCache. */
    private $dataCache = array(); // cache to store data
    /** restul from last escuted query @type MysqliResult $last. */
    private $last; // last exectuted query
    private $tran = false;

    /**
     * class constructor
     *
     * @return none
     * 
     */
    public function __construct() {
        
    }

    /**
     * set new conection
     * 
     * @param string $host host name
     * @param string $user user with access permision
     * @param string $password user password
     * @param string $database database name
     * @return int
     * 
     */
    public function newConnection($host, $user, $password, $database) {
        $this->connections[] = new mysqli($host, $user, $password, $database);
        $connection_id = count($this->connections) - 1;
        if (mysqli_connect_errno()):
            trigger_error('Can not connect to server. ' . $this->connections[$connection_id]->error, E_USER_ERROR);
        endif;
        return $connection_id;
    }

    /**
     * close current connection
     * 
     * @return none
     * 
     */
    public function start_transaction() {
        $this->executeQuery("START TRANSACTION;");
        $this->tran = true;
    }

    public function commit() {
        $this->executeQuery("COMMIT");
        $this->tran = false;
    }

    public function closeConnection() {
        $this->connections[$this->activeConnection]->close();
    }

    /**
     * change active connection
     * 
     * @param int $new connection id 
     * @return none
     * 
     */
    public function setActiveConnection($new) {
        $this->activeConnection = $new;
    }

    /**
     * execute and store query result
     * 
     * @param string $queryStr Sql query 
     * @return affected rows (-1 -> error)
     * 
     */
    public function cacheQuery($queryStr) {
        if (!$result = $this->connections[$this->activeConnection]->query($queryStr)):
            trigger_error('Error, query can not be executed and cached: ' . $this->connections[$this->activeConnection]->error, E_USER_ERROR);
            return -1;
        else:
            $this->queryCache[] = $result;
            return count($this->queryCache) - 1;
        endif;
    }

    /**
     * gets number of rows afected in cached query 
     * 
     * @param int $cache_id query position 
     * @return int 
     * 
     */
    public function numRowsFromCache($cache_id) {
        return $this->queryCache[$cache_id]->num_rows;
    }

    /**
     * get result from query in cache
     * 
     * @param  int $cache_id query position 
     * @return Array or NULL
     * 
     */
    public function resultsFromCache($cache_id) {
        return $this->queryCache[$cache_id]->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * save data 
     * 
     * @param Array $data data 
     * @return length of data
     * 
     */
    public function cacheData($data) {
        $this->dataCache[] = $data;
        return count($this->dataCache) - 1;
    }

    /**
     * gets data from cached query
     * 
     * @param int $cache_id data position 
     * @return Array
     * 
     */
    public function dataFromCache($cache_id) {
        return $this->dataCache[$cache_id];
    }

    /**
     * delete data from database
     * 
     * @param string $table table name
     * @param string $condition condition to make a good selection
     * @param string $limit nums of records to be deleted  
     * @return none
     * 
     */
    public function deleteRecords($table, $condition, $limit) {
        $limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
        $delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
        $this->executeQuery($delete);
    }

    /**
     * update data from database
     * 
     * @param string $table table name
     * @param string $changes new data
     * @param string $condition condition to make a good selection
     * @return boolean true for success
     * 
     */
    public function updateRecords($table, $changes, $condition) {
        $update = "UPDATE " . $table . " SET ";
        foreach ($changes as $field => $value):
            $update .= "`" . $field . "`='{$value}',";
        endforeach;
        $update = substr($update, 0, -1);
        if ($condition != ''):
            $update .= "WHERE " . $condition;
        endif;
        $this->executeQuery($update);
        return true;
    }

    /**
     * insert new data
     * 
     * @param string $table table name
     * @param string $data new data
     * @return none
     * 
     */
    public function insertRecords($table, $data) {
        $fields = "";
        $values = "";
        foreach ($data as $f => $v):
            $fields .= "`$f`,";
            $values .= set_type($v);
        endforeach;
        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);
        $insert = "INSERT INTO $table ({$fields}) VALUES({$values})";
        $this->executeQuery($insert);
        return true;
    }

    /**
     * executed query which is not cached will be overwritten
     * 
     * @param string $queryStr Sql query 
     * @return none
     * 
     */
    public function executeQuery($queryStr) {
        if (!$result = $this->connections[$this->activeConnection]->query($queryStr)):
            if (!$this->tran):
                trigger_error('Error, query can not be executed' . $this->connections[$this->activeConnection]->error, E_USER_ERROR);
            else:
                $this->executeQuery("ROLLBACK");
                $this->tran = false;
                return false;
            endif;
        else:
            $this->last = $result; // save result
            return true;
        endif;
    }

    /**
     * get rows from last query
     * 
     * @return mysql assoc
     * 
     */
    public function getRows() {
        return $this->last->fetch_array(MYSQLI_ASSOC);
    }

    /**
     * get result from last query
     * 
     * @return mysql result
     * 
     */
    public function getResult() {
        return $this->last;
    }

    /**
     * get num rows from last query
     *  
     * @return int 
     * 
     */
    public function getNumRows() {
        return $this->last->num_rows;
    }

    /**
     * get affected rows from active connection
     * 
     * @return int
     * 
     */
    public function affectedRows() {
        return $this->$this->connections[$this->activeConnection]->affected_rows;
    }

    /**
     * avoid SQL inyection
     * 
     * @param string $data data value 
     * @return string
     * 
     */
    public function sanitizeData($data) {
        return $this->connections[$this->activeConnection]->real_escape_string($data);
    }

    /**
     * close all connections
     * 
     * @return none
     * 
     */
    public function __deconstruct() {
        foreach ($this->connections as $connection):
            $connection->close();
        endforeach;
    }

}

?>