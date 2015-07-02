<?php

class devolucionModel extends object {

    public function existe($linea, $estilo, $color, $talla, $bodega, $factura, $cambio) {
        $query = "SELECT id FROM devolucion WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega AND factura=$factura AND cambio=$cambio";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function borrar($linea, $estilo, $color, $talla, $bodega, $factura, $cambio) {
        $query = "DELETE FROM devolucion WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega AND factura=$factura AND cambio=$cambio";
        data_model()->executeQuery($query);
    }

    public function eliminar($cambio) {
        $query = "DELETE FROM devolucion WHERE cambio = $cambio";
        data_model()->executeQuery($query);
    }

    public function actualizar($linea, $estilo, $color, $talla, $bodega, $factura, $cambio, $cantidad) {
        $query = "UPDATE devolucion SET cantidad = $cantidad WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega AND factura=$factura AND cambio=$cambio";
        data_model()->executeQuery($query);
    }

    public function aplicar_devolucion($cambio) {

        $oBodega = $this->get_child('bodega');

        $query = "SELECT * FROM devolucion WHERE cambio=$cambio";
        data_model()->executeQuery($query);
        $productos = array();

        while ($res = data_model()->getResult()->fetch_assoc()) {
            $productos[] = $res;
        }

        /*foreach ($productos as $producto) {
            if ($oBodega->existe($producto['linea'], $producto['estilo'], $producto['color'], $producto['talla'], $producto['bodega'])) {
                $oBodega->act_stock($producto['linea'], $producto['estilo'], $producto['color'], $producto['talla'], $producto['bodega'], $producto['cantidad']);
            } else {
                $oBodega->ins_stock($producto['linea'], $producto['estilo'], $producto['color'], $producto['talla'], $producto['bodega'], $producto['cantidad']);
            }
            $this->suplir($producto['id']);
        }*/

        $query = "UPDATE cambio SET editable = false WHERE id=$cambio";
        data_model()->executeQuery($query);
    }

    private function suplir($id) {
        $query = "UPDATE devolucion SET devueltos = cantidad WHERE id=$id";
        data_model()->executeQuery($query);
    }

}

?>