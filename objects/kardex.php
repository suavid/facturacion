<?php

class kardexModel extends object {
    /* CONSULTA ESTADO ACTUAL DE KARDEX */

    public function estado_actual($linea, $estilo, $color, $talla) {
        $data = array();
        $query = "SELECT cantidad_ex, valor_unitario_ex,valor_total_ex FROM kardex 
			      WHERE id=(SELECT MAX(id) as id from kardex WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla)";

        data_model()->executeQuery($query);

        if (data_model()->getNumRows() > 0):
            while ($ex = data_model()->getResult()->fetch_assoc()):
                $data['cantidad_ex']       = $ex['cantidad_ex'];
                $data['valor_unitario_ex'] = $ex['valor_unitario_ex'];
                $data['valor_total_ex']    = $ex['valor_total_ex'];
            endwhile;
        else:
            $data['cantidad_ex']       = 0;
            $data['valor_unitario_ex'] = 0;
            $data['valor_total_ex']    = 0;
        endif;

        return array($data['cantidad_ex'], $data['valor_unitario_ex'], $data['valor_total_ex']);
    }

    /* genera una entrada al karex */

    public function generar_entrada($linea, $estilo, $color, $talla, $cantidad, $vu, $detalle = "Entrada producto") {

        list( $c_actual, $vu_actual, $vt_actual) = $this->estado_actual($linea, $estilo, $color, $talla);

        $importe = $cantidad * $vu;        // total monetario que entra
        $stock   = $c_actual + $cantidad;  // total (existente + entrante)
        $valor   = $vt_actual + $importe;  // total monetario (importe + monto actual)
        $nvu     = $valor / $stock;        // nuevo valor unitario por costo promedio (total monto / total unidades)
        $data    = array();

        $system = $this->get_child('system');
        $system->get(1);
        $this->get(0);
        $data['linea']             = $linea;
        $data['estilo']            = $estilo;
        $data['color']             = $color;
        $data['talla']             = $talla;
        $data['fecha']             = date("y-m-d");
        $data['fecha_transaccion'] = date("y-m-d");
        $data['detalle']           = $detalle;
        $data['cantidad_en']       = $cantidad;
        $data['valor_unitario_en'] = $vu;
        $data['valor_total_en']    = $importe;
        $data['cantidad_sa']       = 0;
        $data['valor_unitario_sa'] = 0;
        $data['valor_total_sa']    = 0;
        $data['cantidad_ex']       = $stock;
        $data['valor_unitario_ex'] = $nvu;
        $data['valor_total_ex']    = $valor;
        $data['editable']          = true;
        $data['periodo']           = $system->get_attr('periodo_actual');


        $this->change_status($data);
        $this->save();

        $nuevo_costo = $nvu;

        /* debemos actualizar el costo del producto */
        $query = "UPDATE control_precio SET costo=$nuevo_costo WHERE control_estilo='{$estilo}' AND linea=$linea AND color=$color AND talla=$talla";
        
        data_model()->executeQuery($query);
    }

    public function generar_salida($linea, $estilo, $color, $talla, $cantidad, $detalle = "Salida producto") {

        list( $c_actual, $vu_actual, $vt_actual ) = $this->estado_actual($linea, $estilo, $color, $talla);

        $salida = $cantidad * $vu_actual;  // total monto saliente
        $stock  = $c_actual - $cantidad;   // stock después de la salida
        $valor  = $vt_actual - $salida;    // total monto despues de la salida
        $nvu    = $vu_actual;              // costo no cambia

        $system = $this->get_child('system');
        $system->get(1);
        $this->get(0);

        $data['linea']             = $linea;
        $data['estilo']            = $estilo;
        $data['color']             = $color;
        $data['talla']             = $talla;
        $data['valor']             = $talla;
        $data['fecha']             = date("y-m-d");
        $data['fecha_transaccion'] = date("y-m-d");
        $data['detalle']           = $detalle;
        $data['cantidad_en']       = 0;
        $data['valor_unitario_en'] = 0;
        $data['valor_total_en']    = 0;
        $data['cantidad_sa']       = $cantidad;
        $data['valor_unitario_sa'] = $vu_actual;
        $data['valor_total_sa']    = $salida;
        $data['cantidad_ex']       = $stock;
        $data['valor_unitario_ex'] = $nvu;
        $data['valor_total_ex']    = $valor;
        $data['editable']          = true;
        $data['periodo']           = $system->get_attr('periodo_actual');

        $this->change_status($data);
        $this->save();

        $system = $this->get_child('system');
        
        if($system->stock_bajo==1){
            $query = "SELECT SUM(stock) AS stock, minimo_stock FROM estado_bodega INNER JOIN producto ON estado_bodega.estilo = producto.estilo AND estado_bodega.linea = producto.linea WHERE (producto.linea=$linea AND producto.estilo='{$estilo}')  GROUP BY producto.linea, grupo";
            data_model()->executeQuery($query);
            
            if(data_model()->getNumRows() > 0){

                $res = data_model()->getResult()->fetch_assoc();
                if($res['stock'] <= $res['minimo_stock']){
                    $query = "SELECT usuario FROM empleado";
                    $empleados = array();
                    data_model()->executeQuery($query);
                    while($inf = data_model()->getResult()->fetch_assoc()){
                        $empleados[] = $inf;
                    }


                    $inbox = $this->get_child('inbox');

                    foreach ($empleados as $usuario) {
                        $inbox->get(0);
                        $inbox->mensaje = "PRODUCTO BAJO DE STOCK! <br/> Se sugiere realizar una compra para el siguiente producto: <br/><br/>Estilo: ".$estilo."<br/>Linea:  ".$linea."<br/>Color: ".$color."<br/> Talla: ".$talla."<br/><br/> Unidades restantes: ".$res['stock'];
                        $inbox->destinatario = $usuario['usuario'];
                        $inbox->remitente = "Sistema";
                        $inbox->fecha = date("Y-m-d");
                        $inbox->titulo = "Aleta de stock bajo";
                        $inbox->save();   
                    }

                }
            }
        }

    }

}

?>