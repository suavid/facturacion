<?php

class bodegaModel extends object {
    /*
     * 
     * Implementaciones de abstraccion
     * 
     * */

    public function existe_producto($linea, $estilo, $color, $talla) {
        $query = "SELECT * FROM estado_bodega WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;

        return false;
    }

    public function obtener_bodega($linea, $estilo, $color, $talla) {
        $query = "SELECT bodega FROM estado_bodega WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color";
        data_model()->executeQuery($query);
        $bodegas = array();
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $bodegas[] = $res['bodega'];
        }

        return $bodegas;
    }

    public function crear_entrada($linea, $estilo, $color, $talla, $bodega) {
        $query = "INSER INTO estado_bodega(estilo,linea,color,talla,bodega,stock) VALUES('$estilo',$linea,$color,$talla,$bodega,0)";
        data_model()->executeQuery($query);
    }

    public function aumentar_stock($linea, $estilo, $color, $talla, $bodega, $cantidad) {
        if ($cantidad < 0)
            $cantidad *= -1;
        if ($this->existe_producto($linea, $estilo, $color, $talla)) {
            $query = "UPDATE estado_bodega SET stock=(stock+$cantidad) WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color AND bodega=$bodega";
            data_model()->executeQuery($query);
        } else {
            $this->crear_entrada($linea, $estilo, $color, $talla, $bodega);
            $this->aumentar_stock($linea, $estilo, $color, $talla, $bodega, $cantidad);
        }
    }

    public function reducir_stock($linea, $estilo, $color, $talla, $bodega, $cantidad) {
        if ($cantidad < 0)
            $cantidad *= -1;
        $query = "UPDATE estado_bodega SET stock=(stock-$cantidad) WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color AND bodega=$bodega";
        data_model()->executeQuery($query);
    }

    public function consultar_stock($linea, $estilo, $color, $talla, $bodega) {
        $query = "SELECT stock FROM estado_bodega WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color AND bodega=$bodega";
        data_model()->executeQuery($query);
        $stock = 0;
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $stock = $res['stock'];
        }

        return $stock;
    }

    // fin

    public function ultimo_en_blanco() {
        $query = "select id from bodega WHERE nombre is null";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $ret = data_model()->getResult()->fetch_assoc();
            return $ret['id'];
        } else {
            return -1;
        }
    }

    public function existe($linea, $estilo, $color, $talla, $bodega) {
        $query = "SELECT * FROM estado_bodega WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color AND bodega=$bodega";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function act_stock($linea, $estilo, $color, $talla, $bodega, $cantidad) {
        $query = "UPDATE estado_bodega SET stock = (stock + $cantidad) WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color AND bodega=$bodega";
        data_model()->executeQuery($query);
    }

    public function ins_stock($linea, $estilo, $color, $talla, $bodega, $cantidad) {
        $data = array();
        $data['linea'] = $linea;
        $data['estilo'] = $estilo;
        $data['color'] = $color;
        $data['talla'] = $talla;
        $data['bodega'] = $bodega;
        $data['stock'] = $cantidad;
        $this->get(0);
        $this->change_status($data);
        $this->save();
    }

}

?>