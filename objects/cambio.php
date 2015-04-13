<?php

class cambioModel extends object {

    public function cliente($cambio) {
        $query = "SELECT cliente FROM cambio WHERE id=$cambio";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        return $res['cliente'];
    }

}

?>