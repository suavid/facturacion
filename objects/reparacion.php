<?php

class reparacionModel extends object {

    public function cliente($reparacion) {
        $query = "SELECT cliente FROM reparacion WHERE id=$reparacion";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        return $res['cliente'];
    }

}

?>