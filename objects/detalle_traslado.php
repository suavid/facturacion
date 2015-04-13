<?php

class detalle_trasladoModel extends object {

    public function existe($estilo, $linea, $color, $talla, $id_ref) {
        $query = "SELECT * FROM detalle_traslado WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_ref=$id_ref";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function actualizar($estilo, $linea, $color, $talla, $cantidad, $total, $id_ref) {
        $query = "UPDATE detalle_traslado SET cantidad = (cantidad+$cantidad), total = (total+$total)  WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_ref=$id_ref";
        data_model()->executeQuery($query);
    }

}

?>