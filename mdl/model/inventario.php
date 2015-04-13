<?php

class inventarioModel extends object {

    public function __construct() {
        
    }

    public function DATOS_PRODUCTO($campos = null, $condiciones = null, $group_by = null, $order_by = null, $order_type = "", $limitInf = null, $tamPag = null, $op_type = "cache"){
        
        $campos_str    = "*";
        $condicion_str =  "";
        $group_by_str =  "";
        $order_by_str =  "";
        $limit_str =  "";

        if(count($campos)>0){
            $campos_str = "";
            $campos_str = implode(",", $campos);
        }        

        if(count($condiciones)>0){

            $condicion_str = "WHERE ";
            $condicion_str .= $this->buildCondition($condiciones); 
        }

        if(count($group_by)>0){
            $group_by_str = "GROUP BY ";
            $group_by_str .= implode(",", $group_by);
        }

        if($order_by != null){
            $order_by_str = "ORDER BY ".$order_by;
        }

        if($limitInf!=null){
            $limit_str = "LIMIT $limitInf";
        }

        if($tamPag!=null){
            $limit_str .= ", $tamPag";
        }

        $query = "SELECT $campos_str FROM producto 
                  LEFT JOIN linea ON linea.id = producto.linea LEFT JOIN grupo ON grupo.id = producto.grupo 
                  LEFT JOIN tacon ON tacon.id = producto.tacon LEFT JOIN material ON material.id = producto.material
                  LEFT JOIN suela ON suela.id = producto.suela 
                  $condicion_str $group_by_str $order_by_str $order_type $limit_str";
    
        if($op_type=="cache"){

            return array(data_model()->getNumRows(), data_model()->cacheQuery($query));
        
        }else{

            data_model()->executeQuery($query);
            $res = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $res[] = $row;
            }

            return array(data_model()->getNumRows(), $res);
        }
    }

    private function buildCondition($condiciones){
        if(!is_array($condiciones)){
            return $condiciones;
        }
        else if((count($condiciones) == 3)){
            $condiciones[0] = $this->buildCondition($condiciones[0]);
            $condiciones[1] = $this->buildCondition($condiciones[1]);
            $condiciones[2] = $this->buildCondition($condiciones[2]);

            return implode(" ", $condiciones);
        }
    }

    public function producto_registrado($estilo) {
        $sql = "SELECT * FROM producto WHERE estilo=$estilo";
        data_model()->executeQuery($sql);
        if (data_model()->getNumRows() > 0):
            return true;
        else:
            return false;
        endif;
    }
    
    public function guardar_elemento_kit(){
        if(isset($_POST)){
            $estilo   = $_POST['estilo'];
            $linea    = $_POST['linea'];
            $kit      = $_POST['kit'];
            $cantidad = $_POST['cantidad'];
            $elemento_kit = $this->get_child('elemento_kit');
            $query = "SELECT id FROM elemento_kit WHERE estilo='{$estilo}' AND linea=$linea AND kit='{$kit}'";
            data_model()->executeQuery($query);
            if(data_model()->getNumRows()>0){
                $res = data_model()->getResult()->fetch_assoc();
                $elemento_kit->get($res['id']);
                $elemento_kit->change_status($_POST);
                $elemento_kit->save();
            }else{
                $elemento_kit->get(0);
                $elemento_kit->change_status($_POST);
                $elemento_kit->save();
            }
        }
    }

    public function requestRun($linea, $estilo, $color){
        $query = "select * from talla_producto WHERE  talla_estilo_producto='{$estilo}' AND linea=$linea AND color=$color";
        data_model()->executeQuery($query);
        $response = array();
        while($res = data_model()->getResult()->fetch_assoc()){
            $response[] = $res;
        }

        echo json_encode($response);
    }

    public function obtenerLineasYGrupos(){
        $query = "SELECT linea.id as id_linea, grupo.id as id_grupo, linea.nombre as linea, grupo.nombre as grupo FROM producto INNER JOIN linea on linea.id = linea INNER JOIN grupo ON grupo.id = grupo WHERE linea is not null AND grupo is not null GROUP BY linea, grupo";
        return data_model()->cacheQuery($query);
    }

    public function actualizarMinimo($linea, $grupo, $minimo_stock){
        $query = "UPDATE producto SET minimo_stock = $minimo_stock WHERE linea=$linea AND grupo=$grupo";
        data_model()->executeQuery($query);
    }

    public function requestColors($linea, $estilo){
        $query = "select * from color_producto join color on color=color.id WHERE  color_estilo_producto='{$estilo}' AND linea=$linea";
        data_model()->executeQuery($query);
        $response = array();
        while($res = data_model()->getResult()->fetch_assoc()){
            $response[] = $res;
        }

        echo json_encode($response);
    }

    public function general_producto($estilo){
        $query = "SELECT producto.descripcion, linea.id as id_linea, catalogo.nombre as catalogo, linea.nombre as linea, n_pagina,  grupo.nombre as grupo FROM producto INNER JOIN linea on linea.id = producto.linea INNER JOIN catalogo on catalogo.id = producto.catalogo INNER JOIN grupo on grupo.id = producto.grupo  WHERE estilo='{$estilo}'";
        return data_model()->cacheQuery($query);
    }

    public function l_general_producto($estilo){
        $query = "SELECT linea FROM producto  WHERE estilo='{$estilo}'";
        data_model()->executeQuery($query);
        $lineas = array();
        while($res = data_model()->getResult()->fetch_assoc()){
            $lineas[] = $res['linea'];
        }
        return $lineas;
    }

    public function detalle_producto_stock($linea, $estilo){
        $query = "SELECT estado_bodega.talla as eb_talla, estado_bodega.color as eb_color, stock as eb_stock, precio as eb_precio FROM estado_bodega INNER JOIN control_precio ON ((control_precio.linea = estado_bodega.linea) AND (control_precio.control_estilo = estado_bodega.estilo)) WHERE estado_bodega.linea = $linea AND estilo='{$estilo}' AND bodega = 1 GROUP BY estado_bodega.estilo, estado_bodega.linea, estado_bodega.color, estado_bodega.talla";

        return data_model()->cacheQuery($query);
    }

    public function detalle_producto_transito($linea, $estilo){
        $query = "SELECT color as ptr_color, talla as ptr_talla, detalle_orden_compra.cantidad as ptr_cantidad, fecha_espera as ptr_fecha_espera FROM orden_compra INNER JOIN detalle_orden_compra ON id_orden = orden_compra.id WHERE estilo='{$estilo}' AND detalle_orden_compra.linea=$linea AND estado='PENDIENTE'";
        return data_model()->cacheQuery($query);
    }

    public function c_general_producto($estilo){
        $query = "SELECT linea.id as id_linea, catalogo.nombre as catalogo, linea.nombre as linea, n_pagina,  grupo.nombre as grupo FROM producto INNER JOIN linea on linea.id = producto.linea INNER JOIN catalogo on catalogo.id = producto.catalogo INNER JOIN grupo on grupo.id = producto.grupo  WHERE estilo='{$estilo}'";
        data_model()->executeQuery($query);

        return data_model()->getNumRows();
    }

    public function obtenerEstilos($linea){
        $query = "SELECT estilo FROM producto WHERE linea=$linea";
        data_model()->executeQuery($query);
        $response = array();
        while($res = data_model()->getResult()->fetch_assoc()){
            $response[] = $res;
        }

        echo json_encode($response);
    }

    public function s_cambio_linea($data, $json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "cambio de linea"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            if (!isset($_POST) || empty($_POST)) {
                $res['succes'] = false;
                $res['message'] = "envio incorrecto de datos detectado";
            } else {
                if (Session::singleton()->getLevel() == 1) {
                    $linea_actual = addslashes($data['lineaActual']);
                    $nueva_linea = addslashes($data['nuevaLinea']);
                    $estilo = addslashes($data['estilo']);
                    $producto = $this->get_child('producto');
                    $or = $producto->existe_producto($linea_actual, $estilo);
                    $ex = $producto->existe_producto($nueva_linea, $estilo);
                    if (!$or) {
                        $res['succes'] = false;
                        $res['message'] = "Error: La linea y estilo especificados no existen";
                    } else {
                        if ($ex) {
                            $res['succes'] = false;
                            $res['message'] = "Advertencia: No se puede procesar, el estilo ya existe en la nueva linea";
                        } else {
                            $producto->cambio_linea($linea_actual, $nueva_linea, $estilo);
                            $or = $producto->existe_producto($linea_actual, $estilo);
                            $ex = $producto->existe_producto($nueva_linea, $estilo);
                            if (!$or && $ex) {
                                $res['succes'] = true;
                                $res['message'] = "El cambio se hizo con exito";
                            } else if (!$or && !$ex) {
                                $res['succes'] = false;
                                $res['message'] = "Error FALTAL: El producto se saco de su linea pero no se asigno a una nueva linea";
                            } else if ($or && $ex) {
                                $res['succes'] = false;
                                $res['message'] = "Error: Se genero un duplicado del producto";
                            } else {
                                $res['succes'] = false;
                                $res['message'] = "Advertencia: No se pudo procesar la peticion";
                            }
                        }
                    }
                } else {
                    $res['succes'] = false;
                    $res['message'] = "violacion de acceso detectada";
                }
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion lanzada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    //linea
    public function s_cambio_grupo($data, $json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "cambio de grupo"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            if (!isset($_POST) || empty($_POST)) {
                $res['succes'] = false;
                $res['message'] = "envio incorrecto de datos detectado";
            } else {
                if (Session::singleton()->getLevel() == 1) {
                    $grupo_actual = addslashes($data['grupoActual']);
                    $nuevo_grupo = addslashes($data['nuevoGrupo']);
                    $estilo = addslashes($data['estilo']);
                    $producto = $this->get_child('producto');
                    $or = $producto->existe_producto_g($grupo_actual, $estilo);
                    $ex = $producto->existe_producto_g($nuevo_grupo, $estilo);
                    if (!$or) {
                        $res['succes'] = false;
                        $res['message'] = "Error: El grupo y estilo especificados no existen";
                    } else {
                        if ($ex) {
                            $res['succes'] = false;
                            $res['message'] = "Advertencia: No se puede procesar, el estilo ya existe en el nuevo grupo";
                        } else {
                            $producto->cambio_grupo($grupo_actual, $nuevo_grupo, $estilo);
                            $or = $producto->existe_producto_g($grupo_actual, $estilo);
                            $ex = $producto->existe_producto_g($nuevo_grupo, $estilo);
                            if (!$or && $ex) {
                                $res['succes'] = true;
                                $res['message'] = "El cambio se hizo con exito";
                            } else if (!$or && !$ex) {
                                $res['succes'] = false;
                                $res['message'] = "Error FALTAL: El producto se saco de su grupo pero no se asigno a un nuevo grupo";
                            } else if ($or && $ex) {
                                $res['succes'] = false;
                                $res['message'] = "Error: Se genero un duplicado del producto";
                            } else {
                                $res['succes'] = false;
                                $res['message'] = "Advertencia: No se pudo procesar la peticion";
                            }
                        }
                    }
                } else {
                    $res['succes'] = false;
                    $res['message'] = "violacion de acceso detectada";
                }
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion lanzada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function obtenerBodega($id) {
        if($id == 0) $id = "0";
        $query = "SELECT nombre FROM bodega WHERE id = $id";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        return $data['nombre'];
    }

    public function bodegaCliente($id) {
        $ret = array();
        $query = "SELECT bodega.id as id, bodega.nombre as nombre FROM bodega where encargado=$id";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() >= 0) {
            $ret = data_model()->getResult()->fetch_assoc();
            $ret['STATUS'] = "OK";
        } else {
            $ret['STATUS'] = "NOTFOUND";
        }
        echo json_encode($ret);
    }

    public function get_oc($id) {
        $query = "SELECT * FROM orden_compra WHERE id=$id";
        return data_model()->cacheQuery($query);
    }

    public function get_oc_detalle($id) {
        $query = "SELECT * FROM detalle_orden_compra INNER JOIN producto ON (detalle_orden_compra.estilo = producto.estilo AND detalle_orden_compra.linea = producto.linea ) WHERE id_orden=$id";
        return data_model()->cacheQuery($query);
    }

    public function estado_oc($estado, $id) {
        $query = "UPDATE orden_compra SET estado = '{$estado}' WHERE id=$id";
        data_model()->executeQuery($query);
    }

    public function inicializarRecepcion($id){
        $query = "SELECT * FROM detalle_orden_compra WHERE id_orden = $id AND es_anexo = 0";
        return data_model()->cacheQuery($query);
    }

    public function obtenerAnexos($id){
        $query = "SELECT * FROM detalle_orden_compra WHERE id_orden = $id AND es_anexo = 1";
        return data_model()->cacheQuery($query);
    }

    public function itemsPendientesOC($id_orden){
        $query = "SELECT (SUM(cantidad) - SUM(recibidos) - SUM(cancelados)) as pendientes FROM detalle_orden_compra WHERE id_orden=$id_orden";
        data_model()->executeQuery($query);
        if(data_model()->getNumRows()>0){
            $res = data_model()->getResult()->fetch_assoc();
            return $res['pendientes'];
        }else{
            return 0;
        }
    }

    public function operaciones_oc($id){
        $query = "SELECT * FROM recepcion_orden_compra WHERE id_orden = $id";
        return data_model()->cacheQuery($query);
    }

    public function deshacer_operacion_oc($id_detalle){
        $query = "DELETE FROM recepcion_orden_compra WHERE id_detalle = $id_detalle";
        data_model()->executeQuery($query);
    }

    public function verificarProducto($linea, $estilo, $color, $talla) {
        $query = "SELECT * FROM control_precio WHERE linea=$linea AND control_estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
        $ret = array();
        $data = data_model()->getResult()->fetch_assoc();
        $ret['costo'] = $data['costo'];
        if (data_model()->getNumRows() > 0)
            $ret['status'] = "EXISTS";
        else
            $ret['status'] = "NOT_FOUND";
        return json_encode($ret);
    }

    public function info_oc($id) {
        $query = "SELECT * FROM orden_compra WHERE id = $id";
        return data_model()->cacheQuery($query);
    }

    public function detalleDocumento($id){
        $query = "SELECT * FROM tarjeta_costo WHERE NODOC=$id";
        return data_model()->cacheQuery($query);
    }

    public function comparativo_activo() {
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();

        $query = "SELECT * FROM teorico_fisico WHERE periodo_actual='{$pa}' AND mes=MONTH(CURDATE())";

        data_model()->executeQuery($query);

        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function crear_comparativo() {
        import('scripts.periodos');
        $query = "SELECT * FROM estado_bodega";
        data_model()->executeQuery($query);
        $items = array();
        while ($data = data_model()->getResult()->fetch_assoc()) {
            $items[] = $data;
        }
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();


        foreach ($items as $item) {
            $new_set = array();
            $new_set['linea']  = $item['linea'];
            $new_set['estilo'] = $item['estilo'];
            $new_set['color']  = $item['color'];
            $new_set['talla']  = $item['talla'];
            $new_set['bodega'] = $item['bodega'];
            $new_set['stock']  = $item['stock'];
            $new_set['mes']    = date("m");

            $new_set['periodo_actual'] = $pa;

            $estilo = $item['estilo'];
            $linea  = $item['linea'];
            $color  = $item['color'];
            $talla  = $item['talla'];

            $query = "SELECT id FROM teorico_fisico WHERE estilo='{$estilo}' AND linea=$linea AND color=$color AND talla=$talla";
            
            data_model()->executeQuery($query);

            if(data_model()->getNumRows()<=0){
                $tf = $this->get_child('teorico_fisico');
                $tf->get(0);
                $tf->change_status($new_set);
                $tf->save();
            }
        }
    }

    public function consultar_inventario($tipoQuery, $bodega, $Li, $Ls, $Pi, $Ps) {
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();
        $query = "";
        if ($bodega > 0) {
            switch ($tipoQuery) {
                case 1:
                    $query = "SELECT estado_bodega.bodega, nombre AS linea,SUM(stock) AS stock FROM estado_bodega join linea on linea=linea.id WHERE estado_bodega.bodega=$bodega AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) group by linea;";
                    break;
                case 2:
                    $query = "SELECT estado_bodega.bodega, linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) as stock FROM estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id WHERE estado_bodega.bodega=$bodega AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea;";
                    break;
                case 3:
                    $query = "SELECT estado_bodega.bodega, estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) AS stock from estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id WHERE estado_bodega.bodega=$bodega AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea,estado_bodega.estilo;";
                    break;
                case 4:
                    $query = "SELECT estado_bodega.bodega, color.nombre AS color, color.id as id_color,estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) AS stock from estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id join color on color.id=estado_bodega.color WHERE estado_bodega.bodega=$bodega AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps)  group by proveedor,estado_bodega.linea,estado_bodega.estilo,estado_bodega.color;";
                    break;
                case 5:
                    $query = "SELECT estado_bodega.bodega, estado_bodega.talla AS talla,color.nombre AS color, color.id as id_color,estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(estado_bodega.stock) AS stock,costo, fisico from estado_bodega join control_precio on(estado_bodega.estilo=control_precio.control_estilo AND estado_bodega.linea=control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla) join teorico_fisico on(estado_bodega.estilo=teorico_fisico.estilo AND estado_bodega.linea=teorico_fisico.linea AND estado_bodega.color = teorico_fisico.color AND estado_bodega.talla = teorico_fisico.talla) join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id join color on color.id=estado_bodega.color WHERE estado_bodega.bodega=$bodega AND periodo_actual = $pa AND mes=MONTH(CURDATE()) AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea,estado_bodega.estilo,estado_bodega.color,estado_bodega.talla;";
                    break;
                default:
                    $query = "SELECT estado_bodega.bodega, nombre AS linea,SUM(stock) AS stock FROM estado_bodega join linea on linea=linea.id WHERE estado_bodega.bodega=$bodega AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) group by linea;";
                    break;
            }
        } else {
            switch ($tipoQuery) {
                case 1:
                    $query = "SELECT estado_bodega.bodega, nombre AS linea,SUM(stock) AS stock FROM estado_bodega join linea on linea=linea.id WHERE (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) group by linea;";
                    break;
                case 2:
                    $query = "SELECT estado_bodega.bodega, linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) as stock FROM estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id WHERE (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea;";
                    break;
                case 3:
                    $query = "SELECT estado_bodega.bodega, estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) AS stock from estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id WHERE (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea,estado_bodega.estilo;";
                    break;
                case 4:
                    $query = "SELECT estado_bodega.bodega, color.nombre AS color, color.id as id_color,estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(stock) AS stock from estado_bodega join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id join color on color.id=estado_bodega.color WHERE (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps)  group by proveedor,estado_bodega.linea,estado_bodega.estilo,estado_bodega.color;";
                    break;
                case 5:
                    $query = "SELECT estado_bodega.bodega, estado_bodega.talla AS talla,color.nombre AS color, color.id as id_color,estado_bodega.estilo AS estilo,linea.nombre AS linea, linea.id as id_linea,proveedor.nombre AS proveedor,SUM(estado_bodega.stock) AS stock,costo, fisico from estado_bodega join control_precio on(estado_bodega.estilo=control_precio.control_estilo AND estado_bodega.linea=control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla) join teorico_fisico on(estado_bodega.estilo=teorico_fisico.estilo AND estado_bodega.linea=teorico_fisico.linea AND estado_bodega.color = teorico_fisico.color AND estado_bodega.talla = teorico_fisico.talla) join producto on (estado_bodega.estilo = producto.estilo and estado_bodega.linea = producto.linea) join proveedor on proveedor.id=proveedor join linea on estado_bodega.linea=linea.id join color on color.id=estado_bodega.color WHERE periodo_actual = $pa AND mes=MONTH(CURDATE()) AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) AND (producto.proveedor >= $Pi AND producto.proveedor<= $Ps) group by proveedor,estado_bodega.linea,estado_bodega.estilo,estado_bodega.color,estado_bodega.talla;";
                    break;
                default:
                    $query = "SELECT estado_bodega.bodega, nombre AS linea,SUM(stock) AS stock FROM estado_bodega join linea on linea=linea.id WHERE AND (estado_bodega.linea >= $Li AND estado_bodega.linea<= $Ls) group by linea;";
                    break;
            }
        }

        return data_model()->cacheQuery($query);
    }

    public function verificarTotales($referencia, $total_cliente, $pares_cliente) {
        $ret = array();


        $query = "SELECT total_costo AS t, total_pares AS p, total_costo_p AS total, total_pares_p AS cantidad,editable FROM traslado WHERE id = $referencia";

        data_model()->executeQuery($query);

        $data = data_model()->getResult()->fetch_assoc();

        $nuevo_total = $data['total'] + $total_cliente;
        $nueva_cantidad = $data['cantidad'] + $pares_cliente;

        if ($nueva_cantidad <= $data['p']) {
            if ($nuevo_total <= ( $data['t'] + 0.05 )) {
                $q2 = "UPDATE traslado SET total_costo_p = $nuevo_total, total_pares_p=$nueva_cantidad WHERE id=$referencia";
                $ret['q2'] = $q2;
                data_model()->executeQuery($q2);
                $ret['status'] = true;
            } else {
                $ret['status'] = false;
            }
        } else {
            $ret['status'] = false;
        }

        if ($data['editable'] == 0) {
            $ret['status'] = false;
        }

        return $ret;
    }

    public function trasladoEditable($id) {
        $query = "SELECT editable FROM traslado WHERE id = $id";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        if ($data['editable'] == 0) {
            return json_encode(array('status' => false));
        } else {
            return json_encode(array('status' => true));
        }
    }

    public function suplir_orden($estilo, $linea, $color, $talla, $cantidad) {
        $query = "SELECT * FROM detalle_orden_compra WHERE estilo='{$estilo}' AND linea=$linea AND talla=$talla AND color=$color";

        data_model()->executeQuery($query);

        if (data_model()->getNumRows() > 0) {
            $data = data_model()->getResult()->fetch_assoc();
            $id_orden = $data['id_orden'];
            $query = "SELECT id FROM orden_compra WHERE estado='ENTREGADO' AND id=$id_orden";
            data_model()->executeQuery($query);
            if (data_model()->getNumRows() > 0) {
                $id = $data['id'];
                # realizacion de actualizacion de orden de compra
                if (($cantidad + $data['recibidos']) > $data['cantidad']) {
                    $ct = $data['cantidad'];
                    $query = "UPDATE detalle_orden_compra SET recibidos = $ct WHERE id=$id";
                    $excedente = $cantidad - $data['cantidad'];
                    data_model()->executeQuery($query);
                    # preparando excedente
                    unset($data['id']);
                    $data['cantidad'] = $excedente;
                    unset($data['recibidos']);
                    $oExc = $this->get_child('excedente_orden_compra');
                    $oExc->get(0);
                    $oExc->change_status($data);
                    $oExc->save();
                } else {
                    $recibidos = $cantidad + $data['recibidos'];
                    $query = "UPDATE detalle_orden_compra SET recibidos = $recibidos WHERE id=$id";
                    data_model()->executeQuery($query);
                }
            }
        }
    }

    public function establecer_datos_traslado($ref = '') {
        $query = "SELECT total_costo_p as costo, total_pares_p as pares FROM traslado WHERE id = $ref";

        data_model()->executeQuery($query);

        $data = data_model()->getResult()->fetch_assoc();

        return $data;
    }

    public function establecer_datos_consigna($cliente = '') {
        $query = "SELECT credito,credito_usado FROM cliente WHERE codigo_afiliado = $cliente";

        data_model()->executeQuery($query);

        $data = data_model()->getResult()->fetch_assoc();

        return $data;
    }

    public function ofertas() {
        $query = "SELECT * FROM oferta WHERE estado = 0 AND fin >= CURRENT_DATE()";
        return data_model()->cacheQuery($query);
    }

    public function oferta_x_genero($genero, $oferta) {
        $query = "SELECT estado_bodega.linea,estado_bodega.estilo,estado_bodega.color, estado_bodega.talla 
                FROM estado_bodega INNER JOIN producto 
                ON (estado_bodega.linea = producto.linea AND estado_bodega.estilo = producto.estilo ) 
                WHERE genero = $genero;";

        data_model()->executeQuery($query);

        $items = array();

        while ($cache = data_model()->getResult()->fetch_assoc()) {
            $items[] = $cache;
        }

        foreach ($items as $item) {

            $linea = $item['linea'];
            $estilo = $item['estilo'];
            $color = $item['color'];
            $talla = $item['talla'];

            $query = "SELECT * FROM oferta_producto WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_oferta=$oferta";
            data_model()->executeQuery($query);

            if (data_model()->getNumRows() <= 0) {
                # asignamos la oferta
                $query = "INSERT INTO oferta_producto VALUES(null,$oferta,'{$estilo}',$linea,$color,$talla)";
                data_model()->executeQuery($query);
                $query = "UPDATE estado_bodega SET bodega = 3 WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND ( bodega = 1 OR bodega = 2)";
                data_model()->executeQuery($query);
            }
        }
    }

    public function oferta_x_detalle($linea, $estilo, $color, $talla, $oferta) {
        $arr = array();
        if (!empty($linea))
            $arr[] = "linea=$linea";
        if (!empty($estilo))
            $arr[] = "estilo='{$estilo}'";
        if (!empty($color))
            $arr[] = "color=$color";
        if (!empty($talla))
            $arr[] = "talla=$talla";

        $condicion = implode(' AND ', $arr);
        $query = "SELECT linea,estilo,color,talla FROM estado_bodega WHERE " . $condicion;

        //echo json_encode(array('query'=>$query));

        data_model()->executeQuery($query);

        $items = array();

        while ($cache = data_model()->getResult()->fetch_assoc()) {
            $items[] = $cache;
        }

        foreach ($items as $item) {

            $linea = $item['linea'];
            $estilo = $item['estilo'];
            $color = $item['color'];
            $talla = $item['talla'];

            $query = "SELECT * FROM oferta_producto INNER JOIN oferta ON oferta_producto.id_oferta = oferta.id WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND fin >= CURRENT_DATE()";
            data_model()->executeQuery($query);

            if (data_model()->getNumRows() <= 0) {
                //echo "SE GUARDAN";
                # asignamos la oferta
                $query = "INSERT INTO oferta_producto VALUES(null,$oferta,'{$estilo}',$linea,$color,$talla)";
                data_model()->executeQuery($query);
                $query = "UPDATE estado_bodega SET bodega = 3 WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND ( bodega = 1 OR bodega = 2)";
                echo $query;
                data_model()->executeQuery($query);
            }
        }
    }

    public function actualizar_fisico($linea, $estilo, $color, $talla, $cantidad) {
        $arr = array();
        if (!empty($linea))
            $arr[] = "linea=$linea";
        if (!empty($estilo))
            $arr[] = "estilo='{$estilo}'";
        if (!empty($color))
            $arr[] = "color=$color";
        if (!empty($talla))
            $arr[] = "talla=$talla";

        $condicion = implode(' AND ', $arr);
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();
        $query = "UPDATE teorico_fisico SET fisico = $cantidad WHERE periodo_actual='{$pa}' AND mes=MONTH(CURDATE()) AND " . $condicion;
        data_model()->executeQuery($query);
    }

    public function cambiarBodega($linea, $estilo, $color, $talla) {
        $query = "UPDATE estado_bodega SET bodega=3 WHERE estilo='{$estilo}' AND linea=$linea AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
    }

    public function datos_oferta($id) {
        $query = "SELECT * FROM oferta WHERE id = $id";
        data_model()->executeQuery($query);
        $num = data_model()->getNumRows();
        $dat = data_model()->getResult()->fetch_assoc();
        if ($num > 0) {
            if (trim($id) != "")
                $dat['status'] = true;
        }else {
            $dat['status'] = false;
        }
        return $dat;
    }

    public function cambiosPorEstiloLinea($estilo, $linea, $precio) {
        $query = "SELECT * FROM control_precio WHERE linea = $linea AND control_estilo = '{$estilo}'";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}'";
            data_model()->executeQuery($query);
        } else {
            $query = "SELECT * FROM talla_producto WHERE talla_estilo_producto = '{$estilo}'";
            data_model()->executeQuery($query);
            if (data_model()->getNumRows() > 0) {
                $items = array();
                while ($res = data_model()->getResult()->fetch_assoc()) {
                    $items[] = $res;
                }
                foreach ($items as $item) {
                    $color = $item['color'];
                    $talla = $item['talla'];
                    $query = "INSERT INTO control_precio VALUES(null,'{$estilo}',$linea,$talla,$color,$precio,0.0) ";
                    data_model()->executeQuery($query);
                }
            }
        }
    }

    public function cambiosPorEstiloLineaTalla($estilo, $linea, $talla, $precio) {
        $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND talla=$talla";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaCorrida($estilo, $linea, $talla1, $talla2, $precio) {
        $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND (talla>=$talla1 AND talla<=$talla2)";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaCorridaColor($estilo, $linea, $talla1, $talla2, $color, $precio) {
        $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND color=$color AND (talla>=$talla1 AND talla<=$talla2)";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaTallaColor($estilo, $linea, $talla, $color, $precio) {
        $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND talla=$talla AND color=$color";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaColor($estilo, $linea, $color, $precio) {
        $query = "UPDATE control_precio SET precio = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND color=$color";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaC($estilo, $linea, $precio) {
        $query = "SELECT * FROM control_precio WHERE linea = $linea AND control_estilo = '{$estilo}'";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}'";
            data_model()->executeQuery($query);
        } else {
            $query = "SELECT * FROM talla_producto WHERE talla_estilo_producto = '{$estilo}'";
            data_model()->executeQuery($query);
            if (data_model()->getNumRows() > 0) {
                $items = array();
                while ($res = data_model()->getResult()->fetch_assoc()) {
                    $items[] = $res;
                }
                foreach ($items as $item) {
                    $color = $item['color'];
                    $talla = $item['talla'];
                    $query = "INSERT INTO control_precio VALUES(null,'{$estilo}',$linea,$talla,$color,0.0,$costo) ";
                    data_model()->executeQuery($query);
                }
            }
        }
    }

    public function cambiosPorEstiloLineaTallaC($estilo, $linea, $talla, $precio) {
        $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND talla=$talla";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaCorridaC($estilo, $linea, $talla1, $talla2, $precio) {
        $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND (talla>=$talla1 AND talla<=$talla2)";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaCorridaColorC($estilo, $linea, $talla1, $talla2, $color, $precio) {
        $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND color=$color AND (talla>=$talla1 AND talla<=$talla2)";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaTallaColorC($estilo, $linea, $talla, $color, $precio) {
        $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND talla=$talla AND color=$color";
        data_model()->executeQuery($query);
    }

    public function cambiosPorEstiloLineaColorC($estilo, $linea, $color, $precio) {
        $query = "UPDATE control_precio SET costo = $precio WHERE linea = $linea AND control_estilo = '{$estilo}' AND color=$color";
        data_model()->executeQuery($query);
    }

    public function cambiarDatosGenerales($linea, $estilo, $data) {
        $con = "estilo=$estilo AND linea=$linea";
        $query = MysqliHandler::get_update_query('producto', $data, $con);
        data_model()->executeQuery($query);
    }

    public function CatalogoPorId($id) {
        $query = "SELECT * FROM catalogo WHERE id=$id";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();
        return $ret;
    }

    public function DatosGeneralesProducto($linea, $estilo) {
        $query = "SELECT proveedor,catalogo,n_pagina, propiedad FROM producto WHERE estilo = $estilo AND linea=$linea";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();
        return $ret;
    }

    public function valorFlete() {
        $query = "SELECT precio FROM flete";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        return $data['precio'] + 0.0;
    }

    public function performPSearch($term) {
        $sqlQuery = "SELECT * FROM estado_bodega WHERE estilo=$term";
        data_model()->executeQuery($sqlQuery);
        $elem = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $elem[] = $data;
        endwhile;
        return $elem;
    }

    public function salvarCambiosDocumento($doc) {

        $sql = "SELECT * FROM documento_producto WHERE numero_documento={$doc}";   # extraer productos del documento
        $prod_model = $this->get_child('producto');  # objeto producto
        $items = array();                              # contenedor de productos
        // ejecucion de seleccion
        data_model()->executeQuery($sql);

        // Llenar contenedor de productos
        while ($data = data_model()->getResult()->fetch_assoc()):
            unset($data['numero_documento']);   # no ingresar numero de documento al contenedor
            unset($data['visible']);            # no establecer visibilidad al contendor
            $items[] = $data;                   # agregando datos al contendor
        endwhile;

        //var_dump($items);
        // para cada producto en el documento
        foreach ($items as $item):
            $prod_model->setVirtualId('estilo');
            if(!$prod_model->exists($item['estilo'])):
                $prod_model->get(null);                # inicializacion del modelo
                $prod_model->change_status($item);  # asignacion de datos
                $prod_model->save();                # guardado de datos
            endif;
        endforeach;

        // para cada producto en el documento
        foreach ($items as $item):
            $estilo = $item['estilo'];  # se captura el estilo
            $colores = array();          # se prepara un contenedor para colores
            // seleccion de los colores asociados el producto
            $sql = "SELECT * FROM documento_color_producto WHERE color_estilo_producto='{$estilo}'";
            data_model()->executeQuery($sql);

            // por cada color asocuado al producto
            while ($data = data_model()->getResult()->fetch_assoc()):
                $colores[] = $data; # almacenamos el color en el contenedor
            endwhile;

            //echo var_dump($colores);

            $color_model = $this->get_child('color_producto'); # creacion de modelo asociado a la tabla color_producto
            // por cada color en el contenedor
            foreach ($colores as $color):
                $color_model->get(0);                 # inicializamos el modelo
                $color_model->change_status($color);  # se cambian los datos
                $color_model->force_save();                 # se guardan los datos
            endforeach;

            
            $tallas = array();          # creamos un contenedor para las tallas

            $sql = "SELECT * FROM documento_talla_producto WHERE talla_estilo_producto='{$estilo}'";
            data_model()->executeQuery($sql);
            // por cada talla asociado al color - producto
            while ($data = data_model()->getResult()->fetch_assoc()):
                $tallas[]       = $data; # almacenamos la talla en el contenedor
            endwhile;
            //echo var_dump($tallas);

            $precio_model = $this->get_child('control_precio');
            $bodega_model = $this->get_child('estado_bodega');
            $talla_model  = $this->get_child('talla_producto'); # creacion del modelo asocuado a la tabla talla_producto
            // por cada talla en el contenedor
            foreach ($tallas as $talla):
                $talla_model->get(0);                 # inicializamos el model
                $talla_model->change_status($talla);  # aplicamos los cambios
                $talla_model->force_save();           # guardamos los cambios
                $estilo = $talla['talla_estilo_producto'];
                $linea  = $talla['linea'];
                $color  = $talla['color'];
                $talla  = $talla['talla'];
                $bodega = 1;
                $stock  = 0;
                $precio = 0;
                $costo  = 0;
                if(!$bodega_model->existe($linea, $estilo, $color, $talla)){
                    $bodega_model->get(0);
                    $bodega_model->estilo = $estilo;
                    $bodega_model->linea  = $linea;
                    $bodega_model->color  = $color;
                    $bodega_model->talla  = $talla;
                    $bodega_model->bodega = $bodega;
                    $bodega_model->stock  = $stock;
                    $bodega_model->save();
                }
                if(!$precio_model->existe($linea, $estilo, $color, $talla)){
                    $precio_model->get(0);
                    $precio_model->control_estilo = $estilo;
                    $precio_model->linea  = $linea;
                    $precio_model->color  = $color;
                    $precio_model->talla  = $talla;
                    $precio_model->precio = $precio;
                    $precio_model->costo  = $costo;
                    $precio_model->save();
                }
            endforeach;
        endforeach;

        /* BORRADO DE DATOS TEMPORALES */

        // por cada producto en el documento
        foreach ($items as $item):
            $estilo = $item['estilo'];  # selecionamos el estilo
            $colores = array();          # creamos un contenedor para colores
            // seleccionamos los colores asociados al producto
            $sql = "SELECT * FROM documento_color_producto WHERE color_estilo_producto='{$estilo}'";
            data_model()->executeQuery($sql);

            // almacenamos los colores en el contenedor
            while ($data = data_model()->getResult()->fetch_assoc()):
                $colores[] = $data;
            endwhile;

            $sql = "DELETE FROM documento_talla_producto WHERE talla_estilo_producto='{$estilo}'";
            data_model()->executeQuery($sql);
            

            // borramos todos los colores asociados al producto
            $sql = "DELETE FROM documento_color_producto WHERE color_estilo_producto='{$estilo}'";
            data_model()->executeQuery($sql);

            // borramos el producto del documento
            $sql = "DELETE FROM documento_producto WHERE estilo='{$estilo}'";
            data_model()->executeQuery($sql);
        endforeach;

        // bloqueamos el documento
        $sql = "UPDATE documento SET estado=1 WHERE id_documento={$doc}";
        data_model()->executeQuery($sql);
    }

    public function performCSearch($term) {
        $sqlQuery = "SELECT * FROM cliente WHERE codigo_afiliado=$term";
        data_model()->executeQuery($sqlQuery);
        $elem = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $elem[] = $data;
        endwhile;
        return $elem;
    }

    public function ClonarControlPrecio($doc) {
        $sql = "SELECT * FROM control_precio_documento WHERE documento=$doc;";
        data_model()->executeQuery($sql);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $retA[] = $data;
        endwhile;
        foreach ($retA as $data):
            unset($data['id']);
            $des = $this->get_child('control_precio');
            $des->get(0);
            $des->change_status($data);
            $des->save();
        endforeach;
    }

    public function ClonarEstadoBodega($doc) {
        $sql = "SELECT * FROM estado_bodega_documento 
              WHERE CONCAT(estilo,linea,talla,color)  
              IN (SELECT CONCAT(estilo,linea,talla,color) as cod 
                FROM estado_bodega) AND documento=$doc";

        $del = "DELETE FROM estado_bodega_documento 
              WHERE CONCAT(estilo,linea,talla,color)  
              IN (SELECT CONCAT(estilo,linea,talla,color) as cod 
                FROM estado_bodega) AND documento=$doc";

        data_model()->executeQuery($sql);
        $retA = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $retA[] = $data;
        endwhile;
        foreach ($retA as $data):
            $concat = $data['estilo'] . $data['linea'] . $data['talla'] . $data['color'];
            $stock = $data['stock'];
            $squery = "UPDATE estado_bodega SET stock = (stock + $stock) WHERE CONCAT(estilo,linea,talla,color) = $concat";
            data_model()->executeQuery($squery);
        endforeach;
        data_model()->executeQuery($del);
        $new = "SELECT * FROM estado_bodega_documento WHERE documento = $doc";
        data_model()->executeQuery($new);
        $retB = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $retB[] = $data;
        endwhile;
        foreach ($retB as $data):
            unset($data['id']);
            unset($data['documento']);
            $ca = $this->get_child('estado_bodega');
            $ca->get(0);
            $ca->change_status($data);
            $ca->save();
        endforeach;
    }

    public function nuevoPrecioProducto($precio, $estilo, $color, $talla, $linea) {
        $sql = "UPDATE control_precio SET precio = $precio WHERE control_estilo='{$estilo}' AND color=$color AND talla=$talla AND linea=$linea";
        data_model()->executeQuery($sql);
    }

    public function actualizarControlPrecio($doc) {
        $sql = "SELECT * FROM control_precio WHERE CONCAT(control_estilo,linea,talla,color) NOT IN 
              (SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio_documento where documento=$doc); 
            ";
        data_model()->executeQuery($sql);
        $retA = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $retA[] = $data;
        endwhile;
        foreach ($retA as $data):
            $data['documento'] = $doc;
            $csModel = $this->get_child('control_precio_documento');
            $csModel->get(0);
            unset($data['id']);
            $csModel->change_status($data);
            $csModel->save();
        endforeach;
    }

    public function registrar_estilo($estilo, $doc, $fecha) {
        $sql = "INSERT INTO documento_producto(estilo,fecha_ingreso,numero_documento) VALUES ($estilo,$fecha,$doc)";
        return data_model()->executeQuery($sql);
    }

    public function producto_temporal($estilo) {
        $sql = "SELECT * FROM documento_producto WHERE estilo=$estilo";
        data_model()->executeQuery($sql);
        if (data_model()->getNumRows() > 0):
            return true;
        else:
            return false;
        endif;
    }

    public function cantidad($tblname) {
        $sql = "SELECT * FROM $tblname";
        data_model()->executeQuery($sql);
        return data_model()->getNumRows();
    }

    public function sql_existencia($data) {
        return "SELECT id,stock FROM estado_bodega_documento WHERE estilo={$data['estilo']} AND linea={$data['linea']} AND color={$data['color']} AND talla={$data['talla']} AND bodega=1 ";
    }

    public function get_documents($usuario) {
        $sql = "SELECT * FROM documento WHERE propietario='{$usuario}' AND modulo='inventario' AND estado=0";
        return data_model()->cacheQuery($sql);
    }

    public function borrar_traslado_detalle($linea, $estilo, $color, $talla, $id_ref) {
        $query = "DELETE FROM detalle_traslado WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_ref=$id_ref ";
        data_model()->executeQuery($query);
    }

    public function reducir_costos_y_pares($cantidad, $total, $id) {
        $query = "UPDATE traslado SET total_costo_p = (total_costo_p - $total), total_pares_p = (total_pares_p - $cantidad) WHERE id=$id ";
        data_model()->executeQuery($query);
    }

    public function reducir_costos_y_pares2($cantidad, $total, $id) {
        $query = "UPDATE traslado SET total_costo = (total_costo - $total), total_pares = (total_pares - $cantidad) WHERE id=$id ";
        data_model()->executeQuery($query);
    }

    public function borrarOferta($linea, $estilo, $color, $talla) {
        $query = "DELETE FROM oferta_producto WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla";
        data_model()->executeQuery($query);
        $query = "UPDATE estado_bodega SET bodega = 1 WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega = 3";
        data_model()->executeQuery($query);
    }

    public function ingresos($id_ref, $bodega_origen, $bodega_destino) {
        $query = "SELECT linea, estilo, color, talla, cantidad, costo FROM detalle_traslado WHERE id_ref = $id_ref";
        $productos = array();
        $stop_flag = false;
        $traslado = $this->get_child('traslado');
        $traslado->get($id_ref);
        $hoja_retaceo = $this->get_child('hoja_retaceo');
        if($hoja_retaceo->exists($traslado->referencia_retaceo)){
            $hoja_retaceo->get($traslado->referencia_retaceo);
            $gasto_indirecto = $hoja_retaceo->total_gastos;
            $total_pares     = $traslado->total_pares;
            $gasto_indirecto_unitario = $gasto_indirecto / $total_pares;
        }else{
            $gasto_indirecto_unitario = 0;   
        }

        data_model()->executeQuery($query);

        while ($data = data_model()->getResult()->fetch_assoc()) {
            $productos[] = $data;
        }

        foreach ($productos as $producto) {
            $linea = $producto['linea'];
            $estilo = $producto['estilo'];
            $color = $producto['color'];
            $talla = $producto['talla'];
            $cantidad = $producto['cantidad'];
            $costo = $producto['costo'];

            //$kardex = $this->get_child('kardex');
            //$kardex->generar_entrada($linea, $estilo, $color, $talla, $cantidad, $costo);

            $existe = "SELECT id,stock FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_destino";
            data_model()->executeQuery($existe);
            $ect = data_model()->getResult()->fetch_assoc();
            $id_destino = $ect['id'];

            if (data_model()->getNumRows() > 0) {
                $up = "UPDATE estado_bodega SET stock = (stock + $cantidad) WHERE id=$id_destino ";
                data_model()->executeQuery($up);
            } else {
                $ins_dat = array();
                $ins_dat['estilo'] = $estilo;
                $ins_dat['linea'] = $linea;
                $ins_dat['color'] = $color;
                $ins_dat['talla'] = $talla;
                $ins_dat['stock'] = $cantidad;
                $ins_dat['bodega'] = $bodega_destino;

                $newItem = $this->get_child('estado_bodega');
                $newItem->get(0);
                $newItem->change_status($ins_dat);
                $newItem->save();
            }

            if(isInstalled("kardex")){
                    
                $prod = $this->get_child('producto');
                $prod->get(array("estilo"=>$estilo, "linea"=>$linea));
                $prov = $this->get_child('proveedor');
                $prov->get($prod->proveedor);

                data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
                data_model()->setActiveConnection(1);

                $system = $this->get_child('system');
                $system->get(1);

                data_model()->newConnection(HOST, USER, PASSWORD, "db_kardex");
                data_model()->setActiveConnection(2);
                $kardex   = connectTo("kardex", "mdl.model.kardex", "kardex");
                $articulo = connectTo("kardex", "objects.articulo", "articulo");
                $articulo->nuevo_articulo($linea, $estilo, $color, $talla);
                    
                $dato_articulo = array(
                    'codigo'=>$articulo->no_articulo($linea, $estilo, $color, $talla),
                    'articulo'=>"$linea-$estilo-$color-$talla",
                    'descripcion'=> $prod->descripcion
                );

                $dato_proveedor = array(
                    'nombre_proveedor'=> $prov->nombre,
                    'nacionalidad_proveedor'=> $prov->nacionalidad
                );

                $dato_entrada = array(
                    "ent_cantidad"=> $cantidad,
                    "ent_costo_unitario"=> $costo + $gasto_indirecto_unitario,
                    "ent_costo_total"=> $cantidad * ($costo + $gasto_indirecto_unitario)
                );


                $kardex->nueva_entrada(
                    date("Y-m-d"), 
                    $traslado->concepto, 
                    $dato_articulo, 
                    0, 
                    1000, 
                    0, 
                    $dato_proveedor,
                    $system->periodo_actual,
                    0, 
                    $dato_entrada,
                    "TR-".$traslado->transaccion."-".$traslado->cod,
                    $bodega_destino
                );        

                list($kcantidad, $kcosto_unitario, $kcosto_total) = $kardex->estado_actual($articulo->no_articulo($linea, $estilo, $color, $talla), $bodega_destino); 

                data_model()->setActiveConnection(0);

                $this->get_child('control_precio')->cambiar_costo($linea, $estilo, $color, $talla, $kcosto_unitario);
            }
        }

        $close_q = "UPDATE traslado SET editable = 0 WHERE id = $id_ref";
        data_model()->executeQuery($close_q);
    }

    public function nombre_transaccion($id) {
        $query = "SELECT nombre FROM transacciones WHERE cod='{$id}';";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        return $data['nombre'];
    }

    public function reporteTraslado($id) {
        $query = "SELECT proveedor.nombre AS proveedor,CONCAT(d.linea,'-',d.estilo,'-',d.color,'-',d.talla) AS codigo,cantidad,d.costo,total,precio,margen_usual,propiedad FROM detalle_traslado AS d join control_precio AS c ON d.linea=c.linea AND d.estilo=c.control_estilo AND d.color=c.color AND d.talla=c.talla INNER JOIN producto AS p ON d.linea=p.linea AND d.estilo=p.estilo join proveedor ON proveedor=proveedor.id  WHERE id_ref = $id";
        return data_model()->cacheQuery($query);
    }

    public function salidas($id_ref, $bodega_origen, $bodega_destino) {
        $query = "SELECT * FROM detalle_traslado WHERE id_ref = $id_ref";
        $productos = array();
        $stop_flag = false;
        $traslado = $this->get_child('traslado');
        $traslado->get($id_ref);

        data_model()->executeQuery($query);

        while ($data = data_model()->getResult()->fetch_assoc()) {
            $productos[] = $data;
        }

        foreach ($productos as &$producto) {
            $linea = $producto['linea'];
            $estilo = $producto['estilo'];
            $color = $producto['color'];
            $talla = $producto['talla'];
            $cantidad = $producto['cantidad'];
            $costo = $producto['costo'];
            $total = $producto['total'];
            $id = $producto['id'];

            $existe = "SELECT id,stock FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_origen";

            data_model()->executeQuery($existe);
            $dat_ = data_model()->getResult()->fetch_assoc();

            //$kardex = $this->get_child('kardex');
            //$kardex->generar_salida($linea, $estilo, $color, $talla, $cantidad);
            if (data_model()->getNumRows() <= 0) {
                $stop_flag = true;
            } else {
                if ($dat_['stock'] < $cantidad) {
                    $cantidad = $dat_['stock'];
                    $excedente = $cantidad - $dat_['stock'];
                    $correccion = "UPDATE detalle_traslado SET cantidad = $cantidad, total = ($cantidad*$costo) WHERE id=$id";
                    data_model()->executeQuery($correccion);
                    $correccion = "UPDATE traslado SET total_pares = (total_pares - $excedente), total_costo = (total_costo - ($excedente*$costo) ), total_pares_p = (total_pares_p - $excedente), total_costo_p = (total_costo_p - ($excedente*$costo) ) WHERE id=$id_ref";
                    $producto['cantidad'] = $cantidad;
                    $producto['total'] = $cantidad * $costo;
                }
            }

        }

        if (!$stop_flag) {

            foreach ($productos as $producto) {
                $linea = $producto['linea'];
                $estilo = $producto['estilo'];
                $color = $producto['color'];
                $talla = $producto['talla'];
                $cantidad = $producto['cantidad'];

                $existe = "SELECT id FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_origen";
                data_model()->executeQuery($existe);
                $ect = data_model()->getResult()->fetch_assoc();
                $id_origen = $ect['id'];

                if(isInstalled("kardex")){
                    
                    $prod = $this->get_child('producto');
                    $prod->get(array("estilo"=>$estilo, "linea"=>$linea));
                    $prov = $this->get_child('proveedor');
                    $prov->get($prod->proveedor);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
                    data_model()->setActiveConnection(1);

                    $system = $this->get_child('system');
                    $system->get(1);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_kardex");
                    data_model()->setActiveConnection(2);
                    $kardex   = connectTo("kardex", "mdl.model.kardex", "kardex");
                    $articulo = connectTo("kardex", "objects.articulo", "articulo");
                    $articulo->nuevo_articulo($linea, $estilo, $color, $talla);
                        
                    $dato_articulo = array(
                        'codigo'=>$articulo->no_articulo($linea, $estilo, $color, $talla),
                        'articulo'=>"$linea-$estilo-$color-$talla",
                        'descripcion'=> $prod->descripcion
                    );

                    $dato_proveedor = array(
                        'nombre_proveedor'=> $prov->nombre,
                        'nacionalidad_proveedor'=> $prov->nacionalidad
                    );

                    $dato_salida = array(
                        "sal_cantidad"=> $cantidad
                    );


                    $kardex->nueva_salida(
                        date("Y-m-d"), 
                        $traslado->concepto, 
                        $dato_articulo, 
                        0, 
                        1000, 
                        0, 
                        $dato_proveedor,
                        $system->periodo_actual,
                        0, 
                        $dato_salida,
                        "TR-".$traslado->transaccion."-".$traslado->cod,
                        $bodega_origen
                    );        

                    data_model()->setActiveConnection(0);
                }

                $up = "UPDATE estado_bodega SET stock = (stock - $cantidad) WHERE id=$id_origen ";
                data_model()->executeQuery($up);
            }
            $close_q = "UPDATE traslado SET editable = 0 WHERE id = $id_ref";
            data_model()->executeQuery($close_q);
        }

        return $stop_flag;
    }

    public function transaccionLibre($id_ref, $bodega_origen, $bodega_destino, $transaccion) {
        /* Cierra el traslado */
        $close_q = "UPDATE traslado SET editable = 0 WHERE id = $id_ref";
        data_model()->executeQuery($close_q);
        
        $query   = "INSERT INTO traslado(fecha, proveedor_origen, proveedor_nacional, bodega_origen, bodega_destino, concepto, transaccion, total_pares, total_costo, total_pares_p, total_costo_p, editable, consigna, usuario, concepto_alternativo, cliente, cod) (SELECT fecha, proveedor_origen, proveedor_nacional, bodega_origen, bodega_destino, concepto, transaccion, total_pares, total_costo, total_pares_p, total_costo_p, editable, consigna, usuario, concepto_alternativo, cliente, cod FROM traslado WHERE id = $id_ref)";
        // genera la copia
        data_model()->executeQuery($query);

        // obtener el id del traslado nuevo
        $query = "SELECT MAX(id) AS id FROM traslado";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        $id = $data['id'];

        $tras = $this->get_child('traslado');
        $tras->get($id);

        // asigna la transaccion contraria
        $tras->transaccion = ($transaccion=="1C") ? "2C": "1C";
        $transaccion = $this->get_child('transacciones');
        $transaccion->setVirtualId('cod');
        $transaccion->get($tras->transaccion);
        $tras->cod = $transaccion->get_attr('ultimo') + 1;
        $alcod = $tras->cod; 
        $transaccion->set_attr('ultimo', $tras->cod);
        $transaccion->save();
        $tras->save();
        // termina copia del traslado
        
        $query = "SELECT * FROM detalle_traslado WHERE id_ref = $id_ref";
        $productos = array();
        $stop_flag = false;
        $traslado  = $this->get_child('traslado');
        $traslado->get($id_ref);
        $hoja_retaceo = $this->get_child('hoja_retaceo');
        if($hoja_retaceo->exists($traslado->referencia_retaceo)){
            $hoja_retaceo->get($traslado->referencia_retaceo);
            $gasto_indirecto = $hoja_retaceo->total_gastos;
            $total_pares     = $traslado->total_pares;
            $gasto_indirecto_unitario = $gasto_indirecto / $total_pares;
        }else{
            $gasto_indirecto_unitario = 0;   
        }
        
        
        data_model()->executeQuery($query);

        while ($data = data_model()->getResult()->fetch_assoc()) {
            $productos[] = $data;
        }

        foreach ($productos as &$producto) {
            $linea = $producto['linea'];
            $estilo = $producto['estilo'];
            $color = $producto['color'];
            $talla = $producto['talla'];
            $cantidad = $producto['cantidad'];
            $costo = $producto['costo'];
            $total = $producto['total'];
            $id = $producto['id'];

            $existe = "SELECT id,stock FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_origen";


            data_model()->executeQuery($existe);
            $dat_ = data_model()->getResult()->fetch_assoc();


            if (data_model()->getNumRows() <= 0) {
                $stop_flag = true;
            } else {
                if ($dat_['stock'] < $cantidad) {
                    $cantidad = $dat_['stock'];
                    $excedente = $cantidad - $dat_['stock'];
                    $correccion = "UPDATE detalle_traslado SET cantidad = $cantidad, total = ($cantidad*$costo) WHERE id=$id";
                    data_model()->executeQuery($correccion);
                    $correccion = "UPDATE traslado SET total_pares = (total_pares - $excedente), total_costo = (total_costo - ($excedente*$costo) ), total_pares_p = (total_pares_p - $excedente), total_costo_p = (total_costo_p - ($excedente*$costo) ) WHERE id=$id_ref";
                    $producto['cantidad'] = $cantidad;
                    $producto['total'] = $cantidad * $costo;
                }
            }
        }

        if (!$stop_flag) {

            foreach ($productos as $producto) {
                $linea = $producto['linea'];
                $estilo = $producto['estilo'];
                $color = $producto['color'];
                $talla = $producto['talla'];
                $cantidad = $producto['cantidad'];

                if(isInstalled("kardex")){
                    
                    $prod = $this->get_child('producto');
                    $prod->get(array("estilo"=>$estilo, "linea"=>$linea));
                    $prov = $this->get_child('proveedor');
                    $prov->get($prod->proveedor);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
                    data_model()->setActiveConnection(1);

                    $system = $this->get_child('system');
                    $system->get(1);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_kardex");
                    data_model()->setActiveConnection(2);
                    $kardex   = connectTo("kardex", "mdl.model.kardex", "kardex");
                    $articulo = connectTo("kardex", "objects.articulo", "articulo");
                    $articulo->nuevo_articulo($linea, $estilo, $color, $talla);
                    
                    $dato_articulo = array(
                        'codigo'=>$articulo->no_articulo($linea, $estilo, $color, $talla),
                        'articulo'=>"$linea-$estilo-$color-$talla",
                        'descripcion'=> $prod->descripcion
                    );

                    $dato_proveedor = array(
                        'nombre_proveedor'=> $prov->nombre,
                        'nacionalidad_proveedor'=> $prov->nacionalidad
                    );

                    $dato_entrada = array(
                        "ent_cantidad"=> $cantidad,
                        "ent_costo_unitario"=> $costo + $gasto_indirecto_unitario,
                        "ent_costo_total"=> $cantidad * ($costo + $gasto_indirecto_unitario)
                    );

                    $dato_salida = array(
                        "sal_cantidad"=> $cantidad
                    );

                    $ptransaccion = ($traslado->transaccion=="1C") ? "2C" : "1C";
                    $pcod         = $alcod;

                    $kardex->nueva_salida(
                        date("Y-m-d"), 
                        $traslado->concepto, 
                        $dato_articulo, 
                        0, 
                        1000, 
                        0, 
                        $dato_proveedor,
                        $system->periodo_actual,
                        0, 
                        $dato_salida,
                        "TR-".$ptransaccion."-".$pcod,
                        $bodega_origen
                    );

                    $kardex->nueva_entrada(
                        date("Y-m-d"), 
                        $traslado->concepto, 
                        $dato_articulo, 
                        0, 
                        1000, 
                        0, 
                        $dato_proveedor,
                        $system->periodo_actual,
                        0, 
                        $dato_entrada,
                        "TR-".$traslado->transaccion."-".$traslado->cod,
                        $bodega_destino
                    );
        

                    list($kcantidad, $kcosto_unitario, $kcosto_total) = $kardex->estado_actual($articulo->no_articulo($linea, $estilo, $color, $talla), $bodega_destino); 

                    data_model()->setActiveConnection(0);

                    $this->get_child('control_precio')->cambiar_costo($linea, $estilo, $color, $talla, $kcosto_unitario);
                }

                $existe = "SELECT id FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_origen";
                data_model()->executeQuery($existe);
                $ect = data_model()->getResult()->fetch_assoc();
                $id_origen = $ect['id'];

                $existe = "SELECT id,stock FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_destino";
                data_model()->executeQuery($existe);
                $ect = data_model()->getResult()->fetch_assoc();
                $id_destino = $ect['id'];

                if (data_model()->getNumRows() > 0) {
                    $up = "UPDATE estado_bodega SET stock = (stock + $cantidad) WHERE id=$id_destino ";
                    data_model()->executeQuery($up);
                } else {
                    $ins_dat = array();
                    $ins_dat['estilo'] = $estilo;
                    $ins_dat['linea'] = $linea;
                    $ins_dat['color'] = $color;
                    $ins_dat['talla'] = $talla;
                    $ins_dat['stock'] = $cantidad;
                    $ins_dat['bodega'] = $bodega_destino;

                    $newItem = $this->get_child('estado_bodega');
                    $newItem->get(0);
                    $newItem->change_status($ins_dat);
                    $newItem->save();
                }

                $up = "UPDATE estado_bodega SET stock = (stock - $cantidad) WHERE id=$id_origen ";
                data_model()->executeQuery($up);
            }
            

            foreach ($productos as &$producto) {
                $linea    = $producto['linea'];
                $estilo   = $producto['estilo'];
                $color    = $producto['color'];
                $talla    = $producto['talla'];
                $cantidad = $producto['cantidad'];
                $costo    = $producto['costo'];
                $total    = $producto['total'];
                $bodega   = $producto['bodega'];

                $query = "INSERT INTO detalle_traslado VALUES(null, $id, $linea, '{$estilo}', $color, $talla, $costo, $cantidad,$total, $bodega)";

                data_model()->executeQuery($query);
            }
        }

        return $stop_flag;
    }

    public function ingresoCompra($id_ref, $bodega_destino) {
        /* Obtener los productos del detalle del traslado */
        $traslado  = $this->get_child('traslado');
        $traslado->get($id_ref);
        $query     = "SELECT linea, estilo, color, talla, cantidad, costo FROM detalle_traslado WHERE id_ref = $id_ref";
        $productos = array();
        $stop_flag = false;
        $hoja_retaceo = $this->get_child('hoja_retaceo');
        if($hoja_retaceo->exists($traslado->referencia_retaceo)){
            $hoja_retaceo->get($traslado->referencia_retaceo);
            $gasto_indirecto = $hoja_retaceo->total_gastos;
            $total_pares     = $traslado->total_pares;
            $gasto_indirecto_unitario = $gasto_indirecto / $total_pares;
        }else{
            $gasto_indirecto_unitario = 0;   
        }
        /**
        * Usando id_ref se puede obtener los datos del traslado, obtener la descripcion del traslado y luego insertarla en la entrada del kardex
        */

        // ejecuto la consulta para obtener los productos del traslado
        data_model()->executeQuery($query);

        while ($data = data_model()->getResult()->fetch_assoc()) {
            $productos[] = $data; // almacena los productos en el arreglo
        }

        // recorro el arreglo de productos
        foreach ($productos as $producto) {
            $linea    = $producto['linea'];
            $estilo   = $producto['estilo'];
            $color    = $producto['color'];
            $talla    = $producto['talla'];
            $cantidad = $producto['cantidad'];
            $costo    = $producto['costo'];

            // verificar si el producto existe y obtiene la bodega donde se encuentra el producto y su stock
            $existe = "SELECT id,stock,bodega FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla";
            data_model()->executeQuery($existe);
            
            $bs = array();

            while ($gt = data_model()->getResult()->fetch_assoc()) {
                // obtiene las bodegas
                $bs[] = $gt['bodega'];
            }

            // los datos de las compras no pueden entrar en las bodegas 2 o 3
            if (in_array(2, $bs) || in_array(3, $bs)) {
                $stop_flag = true;
            }
        }

        if (!$stop_flag) {
            // volvemos a recorrer los productos
            foreach ($productos as $producto) {

                $linea    = $producto['linea'];
                $estilo   = $producto['estilo'];
                $color    = $producto['color'];
                $talla    = $producto['talla'];
                $cantidad = $producto['cantidad'];
                $costo    = $producto['costo'];

                /*$kardex = $this->get_child('kardex'); // obtenemos una instancia del kardex

                // genera el registro de la transaccion en el kardex
                $kardex->generar_entrada($linea, $estilo, $color, $talla, $cantidad, $costo);*/

                if(isInstalled("kardex")){
                    
                    $prod = $this->get_child('producto');
                    $prod->get(array("estilo"=>$estilo, "linea"=>$linea));
                    $prov = $this->get_child('proveedor');
                    $prov->get($prod->proveedor);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
                    data_model()->setActiveConnection(1);

                    $system = $this->get_child('system');
                    $system->get(1);

                    data_model()->newConnection(HOST, USER, PASSWORD, "db_kardex");
                    data_model()->setActiveConnection(2);
                    $kardex   = connectTo("kardex", "mdl.model.kardex", "kardex");
                    $articulo = connectTo("kardex", "objects.articulo", "articulo");
                    $articulo->nuevo_articulo($linea, $estilo, $color, $talla);
                    
                    $dato_articulo = array(
                        'codigo'=>$articulo->no_articulo($linea, $estilo, $color, $talla),
                        'articulo'=>"$linea-$estilo-$color-$talla",
                        'descripcion'=> $prod->descripcion
                    );

                    $dato_proveedor = array(
                        'nombre_proveedor'=> $prov->nombre,
                        'nacionalidad_proveedor'=> $prov->nacionalidad
                    );

                    $dato_entrada = array(
                        "ent_cantidad"=> $cantidad,
                        "ent_costo_unitario"=> $costo + $gasto_indirecto_unitario,
                        "ent_costo_total"=> $cantidad * ($costo + $gasto_indirecto_unitario)
                    );


                    $kardex->nueva_entrada(
                        date("Y-m-d"), 
                        $traslado->concepto, 
                        $dato_articulo, 
                        $traslado->referencia_retaceo, 
                        1000, 
                        0, 
                        $dato_proveedor,
                        $system->periodo_actual,
                        0, 
                        $dato_entrada,
                        "TR-".$traslado->transaccion."-".$traslado->cod,
                        $bodega_destino
                    );        

                    list($kcantidad, $kcosto_unitario, $kcosto_total) = $kardex->estado_actual($articulo->no_articulo($linea, $estilo, $color, $talla), $bodega_destino); 

                    data_model()->setActiveConnection(0);

                    $this->get_child('control_precio')->cambiar_costo($linea, $estilo, $color, $talla, $kcosto_unitario);
                }

                // verifica si el producto existe en la bodega de destino
                $existe = "SELECT id,stock FROM estado_bodega WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND bodega=$bodega_destino";
                
                data_model()->executeQuery($existe); // ejecuta la consulta

                $ect    = data_model()->getResult()->fetch_assoc();
                $id     = $ect['id']; // obtiene el id de la fila correspondiente al control del stock

                if (data_model()->getNumRows() > 0) {
                    // si la consulta devuelve filas entonces existe
                    $up = "UPDATE estado_bodega SET stock = (stock + $cantidad) WHERE id=$id "; 
                    data_model()->executeQuery($up); // aumenta el stock del producto
                } else {
                    $ins_dat           = array();
                    $ins_dat['estilo'] = $estilo;
                    $ins_dat['linea']  = $linea;
                    $ins_dat['color']  = $color;
                    $ins_dat['talla']  = $talla;
                    $ins_dat['stock']  = $cantidad;
                    $ins_dat['bodega'] = $bodega_destino;

                    $newItem = $this->get_child('estado_bodega');
                    $newItem->get(0);
                    $newItem->change_status($ins_dat); // caso contrario crea una entrada en el control de stock
                    $newItem->save();
                }
            }
            
            // el documento de traslado ya no se podra editar si llega a este punto ya que ha sido procesado
            $close_q = "UPDATE traslado SET editable = 0 WHERE id = $id_ref";
            data_model()->executeQuery($close_q);
        }

        return $stop_flag;
    }

    public function document_acces($usuario, $documento) {
        $documento = set_type($documento);
        $sql = "SELECT * FROM documento WHERE propietario='{$usuario}' AND id_documento={$documento} ";
        data_model()->executeQuery($sql);
        if (data_model()->getNumRows() > 0):
            return true;
        else:
            return false;
        endif;
    }

    public function colores_actuales($estilo) {
        $str_buffer = "";
        $array_buffer = array();
        $sql = "SELECT id,nombre FROM documento_color_producto INNER JOIN color on id=color WHERE color_estilo_producto={$estilo}";
        $sql2 = "SELECT id,nombre FROM color_producto INNER JOIN color on id=color WHERE color_estilo_producto={$estilo}";
        data_model()->executeQuery($sql);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $array_buffer[] = $data['nombre'] . "(" . $data['id'] . ")";
        endwhile;
        data_model()->executeQuery($sql2);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $array_buffer[] = $data['nombre'] . "(" . $data['id'] . ")";
        endwhile;
        $str_buffer = implode(",", $array_buffer);
        return $str_buffer;
    }

    public function coloresXestilo($estilo){
        $sql = "SELECT id,nombre FROM documento_color_producto INNER JOIN color on id=color WHERE color_estilo_producto={$estilo}";
        data_model()->executeQuery($sql);
        $response = array();
        while($data = data_model()->getResult()->fetch_assoc()){
            $response[] = $data;
        }

        echo json_encode($response);
    }

    public function del_detalle($linea, $estilo, $color, $talla, $id) {
        $query = "DELETE FROM detalle_orden_compra WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_orden=$id";
        data_model()->executeQuery($query);
    }

    public function seleccion_oc($id) {
        $query = "SELECT * FROM orden_compra WHERE id=$id";
        return data_model()->cacheQuery($query);
    }

    public function borrar_color($estilo, $color) {
        $sql = "DELETE FROM documento_color_producto WHERE color_estilo_producto='{$estilo}' AND color={$color}";
        if (!data_model()->executeQuery($sql)) {
            echo "No se puede eliminar, otros datos pueden depender de este registro";
        }
    }

    public function existsBodega($bodega) {
        $bodega = set_type($bodega);
        $sql = "SELECT linea,nombre FROM estado_bodega INNER JOIN linea ON linea=linea.id WHERE bodega=$bodega AND stock > 0 GROUP BY linea";
        data_model()->executeQuery($sql);
        $res = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $res['linea'][] = array('id' => $data['linea'], 'nombre' => $data['nombre']);
        endwhile;
        if (data_model()->getNumRows() > 0):
            $res['STATUS'] = 'OK';
            return $res;
        else:
            $res['STATUS'] = 'NOTFOUND';
            return $res;
        endif;
    }

    public function transito($filtro, $limitInf, $tamPag) {
        $query = "SELECT * FROM orden_compra WHERE estado = '%p' OR estado = '%r' OR estado = '%e' ORDER BY id DESC  LIMIT $limitInf, $tamPag; ";
        if ($filtro['pendiente'] == 'true') {
            $query = str_replace('%p', 'PENDIENTE', $query);
        } else {
            $query = str_replace('%p', ' ', $query);
        }
        if ($filtro['rechazado'] == 'true') {
            $query = str_replace('%r', 'RECHAZADO', $query);
        } else {
            $query = str_replace('%r', ' ', $query);
        }
        if ($filtro['entregado'] == 'true') {
            $query = str_replace('%e', 'ENTREGADO', $query);
        } else {
            $query = str_replace('%e', ' ', $query);
        }
        return data_model()->cacheQuery($query);
    }

    public function cantidadOc($filtro) {
        $query = "SELECT * FROM orden_compra WHERE estado = '%p' OR estado = '%r' OR estado = '%e'; ";
        if ($filtro['pendiente'] == 'true') {
            $query = str_replace('%p', 'PENDIENTE', $query);
        } else {
            $query = str_replace('%p', ' ', $query);
        }
        if ($filtro['rechazado'] == 'true') {
            $query = str_replace('%r', 'RECHAZADO', $query);
        } else {
            $query = str_replace('%r', ' ', $query);
        }
        if ($filtro['entregado'] == 'true') {
            $query = str_replace('%e', 'ENTREGADO', $query);
        } else {
            $query = str_replace('%e', ' ', $query);
        }
        
        data_model()->executeQuery($query);

        return data_model()->getNumRows();
    }

    public function ver_producto_en_transito($estilo, $limitInf, $tamPag){
        if($estilo==""){
            $query = "SELECT estilo, linea.nombre as linea, color.nombre as color, talla, cantidad, fecha_espera FROM orden_compra INNER JOIN detalle_orden_compra ON id_orden=orden_compra.id INNER JOIN linea ON linea.id = detalle_orden_compra.linea INNER JOIN color ON color.id = detalle_orden_compra.color WHERE estado='PENDIENTE' LIMIT $limitInf, $tamPag";
        }else{
            $query = "SELECT estilo, linea.nombre as linea, color.nombre as color, talla, cantidad, fecha_espera FROM orden_compra INNER JOIN detalle_orden_compra ON id_orden=orden_compra.id INNER JOIN linea ON linea.id = detalle_orden_compra.linea INNER JOIN color ON color.id = detalle_orden_compra.color WHERE estado='PENDIENTE' AND estilo='{$estilo}' LIMIT $limitInf, $tamPag";
        }
        return data_model()->cacheQuery($query);
    }

    public function cantidadTransito($estilo){
        if($estilo==""){
            $query = "SELECT estilo, linea.nombre as linea, color.nombre as color, talla, cantidad, fecha_espera FROM orden_compra INNER JOIN detalle_orden_compra ON id_orden=orden_compra.id INNER JOIN linea ON linea.id = detalle_orden_compra.linea INNER JOIN color ON color.id = detalle_orden_compra.color WHERE estado='PENDIENTE' ";
        }else{
            $query = "SELECT estilo, linea.nombre as linea, color.nombre as color, talla, cantidad, fecha_espera FROM orden_compra INNER JOIN detalle_orden_compra ON id_orden=orden_compra.id INNER JOIN linea ON linea.id = detalle_orden_compra.linea INNER JOIN color ON color.id = detalle_orden_compra.color WHERE estado='PENDIENTE' AND estilo='{$estilo}'";
        }
        data_model()->executeQuery($query);

        return data_model()->getNumRows();
    }

    public function existsLinea($bodega, $linea) {
        $bodega = set_type($bodega);
        $linea = set_type($linea);
        $sql = "SELECT * FROM estado_bodega WHERE bodega=$bodega AND linea=$linea AND stock > 0 GROUP BY linea,estilo";
        data_model()->executeQuery($sql);
        $res = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $res['estilo'][] = $data['estilo'];
        endwhile;
        if (data_model()->getNumRows() > 0):
            $res['STATUS'] = 'OK';
            return $res;
        else:
            $res['STATUS'] = 'NOTFOUND';
            return $res;
        endif;
    }

    public function existsEstilo($bodega, $linea, $estilo) {
        $bodega = set_type($bodega);
        $linea = set_type($linea);
        $estilo = set_type($estilo);
        $sql = "SELECT * FROM estado_bodega WHERE bodega=$bodega AND linea=$linea AND estilo=$estilo AND stock > 0 GROUP BY linea,estilo,color";
        data_model()->executeQuery($sql);
        $res = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $res['ccolor'][] = $data['color'];
        endwhile;
        if (data_model()->getNumRows() > 0):
            $res['STATUS'] = 'OK';
            return $res;
        else:
            $res['STATUS'] = 'NOTFOUND';
            return $res;
        endif;
    }

    public function existsColor($bodega, $linea, $estilo, $color) {
        $bodega = set_type($bodega);
        $linea = set_type($linea);
        $estilo = set_type($estilo);
        $color = set_type($color);
        $sql = "SELECT * FROM estado_bodega WHERE bodega=$bodega AND linea=$linea AND estilo=$estilo AND color=$color AND stock > 0 GROUP BY linea,estilo,color,talla";
        data_model()->executeQuery($sql);
        $res = array();
        while ($data = data_model()->getResult()->fetch_assoc()):
            $res['talla'][] = $data['talla'];
        endwhile;
        if (data_model()->getNumRows() > 0):
            $res['STATUS'] = 'OK';
            return $res;
        else:
            $res['STATUS'] = 'NOTFOUND';
            return $res;
        endif;
    }

    public function existsTalla($bodega, $linea, $estilo, $color, $talla) {
        $bodega = set_type($bodega);
        $linea = set_type($linea);
        $estilo = set_type($estilo);
        $color = set_type($color);
        $talla = set_type($talla);
        $sql_stock = "SELECT * FROM estado_bodega WHERE bodega=$bodega AND linea=$linea AND estilo=$estilo AND color=$color AND talla=$talla AND stock > 0";
        data_model()->executeQuery($sql_stock);
        $res = array();
        $res["STOCK"] = data_model()->getResult()->fetch_assoc();
        $ct = data_model()->getNumRows();
        $sql_precio = "SELECT * FROM control_precio WHERE linea=$linea AND control_estilo=$estilo AND color=$color AND talla=$talla";
        data_model()->executeQuery($sql_precio);
        $res = array();
        $res["PRECIO"] = data_model()->getResult()->fetch_assoc();
        if (data_model()->getNumRows() > 0 && $ct > 0):
            $res['STATUS'] = 'OK';
            return $res;
        else:
            $res['STATUS'] = 'NOTFOUND';
            return $res;
        endif;
    }

    public function documentoStock($user) {
        $Query = "SELECT * FROM documento WHERE modulo='stock' AND propietario ='{$user}' AND estado=0";
        return data_model()->cacheQuery($Query);
    }

}

?>