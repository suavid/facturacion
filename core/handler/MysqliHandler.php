<?php

/**
 *  This class provides methods to manage queries 
 *
 */
class MysqliHandler {

    /**
     * get 'insert' query
     *
     * @param string $table_name affected table
     * @param ArrayObject $data 
     *
     * @return string  
     *
     */
    public static function get_insert_query($table_name = '', array $data = NULL) {
        foreach ($data as $key => $value):
            $data[$key] = set_type($value);
        endforeach;
        $fields = implode(',', array_keys($data));
        $values = implode(',', array_values($data));
        return "INSERT INTO $table_name($fields) VALUES($values)";
    }

    /**
     * get 'update' query
     *
     * @param string $table_name affected table
     * @param ArrayObject $data
     * @param string $condition target row 
     *
     * @return string  
     *
     */
    public static function get_update_query($table_name = '', array $data = NULL, $condition = '') {
        foreach ($data as $key => $value) {
            $data[$key] = set_type($value);
        }
        $fields = implode('=?,', array_keys($data));
        $fields.= '=?';
        $temp[] = explode('?', $fields);
        $ct = 0;
        $upd = "";
        foreach ($data as $key => $value)
            $upd.=$temp[0][$ct++] . $value;
        return "UPDATE $table_name SET $upd WHERE $condition";
    }

    /**
     * get 'delete' query
     *
     * @param string $table_name affected table
     * @param string $condition target row 
     *
     * @return string  
     *
     */
    public static function get_delete_query($table_name = '', $condition = '') {
        return "DELETE FROM $table_name WHERE $condition";
    }

}

?>