<?php

class estado_bodegaModel extends object {

    public function existe($linea, $estilo, $color, $talla) {
        $query = "SELECT * FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function referencia($linea, $estilo, $color, $talla) {
        $query = "SELECT id FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND (bodega=1 OR bodega=2 OR bodega=3)";
		data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        if (data_model()->getNumRows() > 0)
            return $res['id'];
        else
            return 0;
    }

}

?>