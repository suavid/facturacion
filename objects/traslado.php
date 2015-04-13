<?php

class trasladoModel extends object {

    public function es_valido($cantidad, $total, $id) {
        $query = "SELECT total_costo, total_costo_p, total_pares, total_pares_p FROM traslado WHERE id = $id";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();

        if (($ret['total_costo_p'] + $total) > $ret['total_costo'] || ($ret['total_pares_p'] + $cantidad) > $ret['total_pares']) {
            return false;
        } else {
            return true;
        }
    }

    public function actualizar($cantidad, $total, $id) {
        $query = "UPDATE traslado SET total_pares_p = ( total_pares_p + $cantidad), total_costo_p=( total_costo_p + $total) WHERE id=$id";
        data_model()->executeQuery($query);
    }

    public function actualizar2($cantidad, $total, $id) {
        $query = "UPDATE traslado SET total_pares = ( total_pares + $cantidad), total_costo=( total_costo + $total) WHERE id=$id";
        data_model()->executeQuery($query);
    }
    
    public function obtenerId($codigo, $transaccion){
        $query = "SELECT id FROM traslado WHERE cod=$codigo AND transaccion='$transaccion'";
        data_model()->executeQuery($query);
        
        if(data_model()->getNumRows()>0){
            $res = data_model()->getResult()->fetch_assoc();
            return $res['id'];
        }else{
            return 0;   
        }
    }

}

?>