<?php

class serieModel extends object {

    public function get_id($tipo, $serie) {
        $query = "SELECT id FROM serie WHERE tipo='{$tipo}' AND serie='{$serie}' ";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $data = data_model()->getResult()->fetch_assoc();
            return $data['id'];
        } else {
            return 0;
        }
    }

    public function get_by_type($tipo) {
        $query = "SELECT * FROM serie WHERE tipo='{$tipo}'";
        return data_model()->cacheQuery($query);
    }

    public function existe_serie($tipo, $serie) {
        $query = "SELECT * FROM serie WHERE tipo='{$tipo}' AND serie='{$serie}' ";
        data_model()->executeQuery($query);
        $ret = array();
        if (data_model()->getNumRows() > 0) {
            $ret = data_model()->getResult()->fetch_assoc();
            $ret['existe'] = true;
        } else {
            $ret['existe'] = false;
        }

        echo json_encode($ret);
    }

}

?>