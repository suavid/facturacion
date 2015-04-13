<?php

/**
 * This abstract class provides basic operations for new models
 *
 */
abstract class object {

    protected $not_null;
    protected $modelTablePk;
    protected $tblname;
    protected $fields;
    protected $is_auto;

    public function __construct() {
        $this->not_null = array();
        list($this->tblname, $this->fields, $this->modelTablePk, $this->is_auto) = ORMHelper::analize($this);
        $this->init_globals();
    }

    public function init_globals()
    {
        //echo "Please, overwrite the 'init_globals' method for ".get_class($this)."<br/>";
    }

    # establece campos no nulos

    public function not_null(array $attrs = NULL) {
        $this->not_null = $attrs;
    }

    public function get_fields() {
        return $this->fields;
    }

    public function getId() {
        return $this->modelTablePk;
    }

    public function setVirtualId($field) {
        $this->modelTablePk = $field;
    }

    # actualiza el estado del objeto

    public function get($search_id) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        if (!$this->modelTablePk):
            return false;
        else:
            if ($search_id !== 0):
                $search_id = set_type($search_id);
                $id = $this->modelTablePk;
                $select = "SELECT * FROM $tblname WHERE $id = $search_id";
                if(data_model()->getNumRows()>0):
                    data_model()->executeQuery($select);
                    $data = data_model()->getResult()->fetch_assoc();
                    if(!is_null($data)):
                        foreach ($data as $key => $value):
                            $this->set_attr($key, $value);
                            if (empty($value)):
                                $this->set_attr($key, '');
                            endif;
                        endforeach;
                    else:
                        foreach ($fields as $field):
                            $this->set_attr($field, '');
                        endforeach;
                    endif;    
                else:
                    foreach ($fields as $field):
                        $this->set_attr($field, '');
                    endforeach;
                endif;
            else:
                foreach ($fields as $field):
                    $this->set_attr($field, '');
                endforeach;
            endif;
        endif;
    }

    # cambia el estado del objeto 
    # no se guarda automaticamente para que sea posible serializarlo

    public function change_status($data) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        foreach ($fields as $field):
            if (isset($data[$field])):
                $this->set_attr($field, $data[$field]);
            endif;
        endforeach;
    }

    # guarda el estado actual del objeto

    public function save() {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $data = array();
        foreach ($fields as $field):
            try{
                $data[$field] = $this->get_attr($field);
            }catch(Exception $e){
                $data[$field] = '';
            }
        endforeach;
        if ($this->validateData($data)):
            if ($this->modelTablePk):
                $id = $this->modelTablePk;
                if ($this->exists($data[$id])):
                    $value     = set_type($data[$id]);
                    $condition = "$id = $value";
                    $query = MysqliHandler::get_update_query($tblname, $data, $condition);
                else:
                    if ($is_auto)
                        unset($data[$id]);
                    $query = MysqliHandler::get_insert_query($tblname, $data);
                endif;
            else:
                $query = MysqliHandler::get_insert_query($tblname, $data);
            endif;
            //throw_error($query);
            data_model()->executeQuery($query);
            return true;
        else:
            return false;
        endif;
    }

    public function force_save() {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $data = array();
        foreach ($fields as $field):
            $data[$field] = $this->get_attr($field);
        endforeach;
        $query = MysqliHandler::get_insert_query($tblname, $data);
        data_model()->executeQuery($query);
    }

    # establece el valor de un atributo

    public function set_attr($attr_name, $val = '') {
        $this->$attr_name = $val;
    }

    # devuelve el valor de un atributo

    public function get_attr($attr_name) {
        if (isset($this->$attr_name))
            return $this->$attr_name;
        else
            throw new Exception(' Can not find attribute ' . $attr_name);
    }

    # valida aquellos campos establecidos como no nulos

    public function validateData($data) {
        foreach ($this->not_null as $key):
            if (!isset($data[$key]) || empty($data)):
                return false;
            else:
                $data[$key] = trim($data[$key]);
                if ((empty($data[$key]) && $data[$key] != 0) || $data[$key] == ""):
                    return false;
                endif;
            endif;
        endforeach;
        return true;
    }

    public function last_insert_id() {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $id = $this->modelTablePk;
        $query = "SELECT MAX($id) AS id FROM $tblname";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        if (data_model()->getNumRows() > 0)
            return $data['id'];
        else
            return 0;
    }

    # verifica si el objeto ya existe en la base de datos

    public function exists($data) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $id = $this->modelTablePk;
        if ($this->modelTablePk):
            $data = set_type($data);
            $query = "SELECT $id from $tblname WHERE $id = $data";
            data_model()->executeQuery($query);
            $return = (data_model()->getNumRows() > 0) ? true : false;
        else:
            $return = false;
        endif;
        return $return;
    }

    # obtiene modelos bajo el directorio mdl/model

    public function get_sibling($sibling_name) {
        import("mdl.model.{$sibling_name}");
        $cls = "{$sibling_name}Model";
        $sibling_obj = new $cls();
        return $sibling_obj;
    }

    # obtiene modelos bajo el directorio objects/

    public function get_child($child_name) {
        import("objects.{$child_name}");
        $cls = "{$child_name}Model";
        $child_obj = new $cls();
        return $child_obj;
    }

    # obtiene numero de registros coincidentes

    public function quantify($field = '', $term = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        if ($field == '' || $term == ''):
            $query = "SELECT * FROM $tblname";
        else:
            $term_s = set_type($term);
            $query = "SELECT * FROM $tblname WHERE ($field = $term_s) 
					  			OR ($field LIKE '%{$term}') OR ($field LIKE '{$term}%') 
					  			OR ($field LIKE '%{$term}%') ";
        endif;
        data_model()->executeQuery($query);
        return data_model()->getNumRows();
    }

    # elimina los registros coincidentes

    public function delete($term, $field = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $id = $this->modelTablePk;
        $term = set_type($term);
        data_model()->start_transaction();
        if ($field == ''):
            $query = "DELETE FROM $tblname WHERE $id = $term";
        else:
            $query = "DELETE FROM $tblname WHERE $field = $term";
        endif;
        
        data_model()->executeQuery($query);
        data_model()->commit();
    }

    # filtrado de registros

    public function filter($field, $term, $limitInf = '', $limitSup = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str = "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";
        $term_s = set_type($term);
        $query = "SELECT * FROM $tblname WHERE ($field = $term_s) 
					  OR ($field LIKE '%{$term}') OR ($field LIKE '{$term}%') 
					  OR ($field LIKE '%{$term}%') " . $limit_str;
        return data_model()->cacheQuery($query);
    }

    public function Multyfilter($filter, $limitInf = '', $limitSup = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str = "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";
        $keys = array_keys($filter);
        $values = array_values($filter);
        $str_ct = implode(' LIKE \'%$\' ? ', $keys);
        $str_ct.= ' LIKE \'%$\' ';
        $art = explode('?', $str_ct);
        for ($i = 0; $i < count($art); $i++):
            $art[$i] = str_replace('$', $values[$i], $art[$i]);
        endfor;
        $fin = implode(' OR ', $art);
        $query = "SELECT * FROM $tblname WHERE $fin " . $limit_str;
        return data_model()->cacheQuery($query);
    }

    public function MultyQuantify($filter) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $keys = array_keys($filter);
        $values = array_values($filter);
        $str_ct = implode(' LIKE \'%$\' ? ', $keys);
        $str_ct.= ' LIKE \'%$\' ';
        $art = explode('?', $str_ct);
        for ($i = 0; $i < count($art); $i++):
            $art[$i] = str_replace('$', $values[$i], $art[$i]);
        endfor;
        $fin = implode(' OR ', $art);
        $query = "SELECT * FROM $tblname WHERE $fin ";
        data_model()->executeQuery($query);
        return data_model()->getNumRows();
    }

    # obtiene una lista de registros

    public function get_list($limitInf = '', $limitSup = '', $trim = null) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str .= "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";

        $str_trim = "";

        if ($trim != null) {
            $str_trim = " WHERE ";
            $arr_trim = array();
            foreach ($trim as $clave) {
                $arr_trim[] = " ( $clave is not null AND $clave != '' ) ";
            }
            $str_trim.= implode(' AND ', $arr_trim);
        }

        $query = "SELECT * FROM $tblname $str_trim " . $limit_str;
        return data_model()->cacheQuery($query);
    }

    public function get_list_array($limitInf = '', $limitSup = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str .= "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";
        $query = "SELECT * FROM $tblname " . $limit_str;
        $res = array();
        data_model()->executeQuery($query);
        while ($dat = data_model()->getResult()->fetch_assoc()) {
            $res[] = $dat;
        }

        return $res;
    }

    # envia un listado de registros hacia $output

    public function send_data_to_file($output, $limitInf = '', $limitSup = '') {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str .= "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";
        $query = "SELECT * FROM $tblname " . $limit_str;
        $result = "";
        $archivo = fopen(APP_PATH . 'scripts/' . $output, 'w');
        data_model()->executeQuery($query);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $result = implode(";", $data);
            if (!(fwrite($archivo, $result . PHP_EOL))):
                echo "can't write " . $output;
                exit;
            endif;
        endwhile;
        fclose($archivo);
    }

    # revuelve el resultado de una busqueda puntual

    public function search($field, $term) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        $term = set_type($term);
        $query = "SELECT * FROM $tblname WHERE $field = $term";
        return data_model()->cacheQuery($query);
    }

}

?>