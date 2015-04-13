<?php

class documento_talla_productoModel extends object {

    public function editable($estilo, $color) {
        $query = "SELECT * FROM documento_talla_producto WHERE talla_estilo_producto='{$estilo}' AND color=$color";
        data_model()->executeQuery($query);
        $rows = data_model()->getNumRows();
        if ($rows > 0)
            return false;

        return true;
    }

}

?>