<?php

class control_precioModel extends object {

    public function consultar_precio($linea, $estilo, $color, $talla) {
        $query = "SELECT precio FROM control_precio WHERE linea=$linea AND control_estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();
        return -1 * $ret['precio'];
    }

    public function existe($linea, $estilo, $color, $talla) {
        $query = "SELECT * FROM control_precio WHERE linea=$linea AND control_estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function cambiar_costo($linea, $estilo, $color, $talla, $costo) {
        $query = "UPDATE control_precio SET costo=$costo WHERE linea=$linea AND control_estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
    }

}

?>