<?php

class empleadoModel extends object {

    public function es_empleado($id_datos) {
        $query = "SELECT id_datos FROM empleado WHERE id_datos=$id_datos";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

}

?>