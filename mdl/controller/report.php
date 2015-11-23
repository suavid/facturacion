<?php

import('mdl.model.report');
import('mdl.view.report');

class reportController extends controller{
	
	public function imprimir_ticket_cambio($id_cambio){
		$queryCambioCabecera = "SELECT caja, fecha, cliente, devolucion.factura FROM devolucion INNER JOIN cambio ON cambio=cambio.id WHERE cambio=$id_cambio";
		$cache = array();
		data_model()->executeQuery($queryCambioCabecera);
        if(data_model()->getNumRows()>0){
            $res = data_model()->getResult()->fetch_assoc();
            $cliente = $res['cliente'];
            $empresa = $this->model->get_child('system');
            $empresa->get(1);
            list($tieneCaja, $data) = $this->model->get_sibling('factura')->tieneCaja(Session::singleton()->getUser());
            $caja     =   $data['id'];
            $fecha    =   $res['fecha'];
            $empleado = $this->model->get_child('empleado');
            $empleado->get(Session::singleton()->getUser());
            $id_datos = $empleado->id_datos;
            $clienteObj = $this->model->get_sibling('cliente');
            $clienteObj->get($id_datos);
            $nombre_empleado = $clienteObj->primer_nombre ." ". $clienteObj->primer_apellido;
            $queryNombreCliente = "SELECT CONCAT(primer_nombre,' ', segundo_nombre,' ', primer_apellido, ' ', segundo_apellido) as nombre FROM cliente WHERE codigo_afiliado=$cliente";
            data_model()->executeQuery($queryNombreCliente);
            $res = data_model()->getResult()->fetch_assoc();
            $nombre_cliente = $res['nombre'];
            $queryDetalleCambio = "SELECT linea, estilo, color, talla, cantidad, precio FROM devolucion WHERE cambio=$id_cambio";
            $detalle = data_model()->cacheQuery($queryDetalleCambio);
            $this->view->imprimir_ticket_cambio($id_cambio,$caja, $cliente, $fecha, $nombre_cliente, $detalle, $empresa, $nombre_empleado);
        }else{
            echo "Este ticket de cambio est&aacute; vac&iacute;o o anulado";   
        }
	}

    public function imprimirOrdenCompra(){
        $id = $_GET['id'];
        $detalle = $this->model->get_sibling('inventario')->inicializarRecepcion($id);
        $anexos  = $this->model->get_sibling('inventario')->obtenerAnexos($id);
        $system  = $this->model->get_child('system');
        $system->get(1);
        $this->view->imprimirOrdenCompra($detalle, $anexos, $system, $id);
    }
    
    public function ReporteDeVentas()
    {
        $this->view->ReporteDeVentas();
    }
    
    public function reporte_Ventas()
    {
        $filtros = json_decode($_GET['filtros']);
        if($filtros!=NULL){
            
            /* Cache para variables de agrupamiento */
            $cache = array();
            
            /* Se obtienen los limites para filtrado de agente */           
            $agente_min = (isset($filtros->{'agente'}->{'min'}) && !empty($filtros->{'agente'}->{'min'}) && is_numeric($filtros->{'agente'}->{'min'})) ? $filtros->{'agente'}->{'min'}: 0;
            $agente_max = (isset($filtros->{'agente'}->{'max'}) && !empty($filtros->{'agente'}->{'max'}) && is_numeric($filtros->{'agente'}->{'max'})) ? $filtros->{'agente'}->{'max'}: 999;
            // Cadena de consulta para agente
            $agente_str = " AND (agente.cage >= $agente_min AND agente.cage <= $agente_max ) ";
            
            
            /* Se obtienen los limites para filtrado de cliente */
            $cliente_min = (isset($filtros->{'cliente'}->{'min'}) && !empty($filtros->{'cliente'}->{'min'}) && is_numeric($filtros->{'cliente'}->{'min'})) ? $filtros->{'cliente'}->{'min'}: 0;
            $cliente_max = (isset($filtros->{'cliente'}->{'max'}) && !empty($filtros->{'cliente'}->{'max'}) && is_numeric($filtros->{'cliente'}->{'max'})) ? $filtros->{'cliente'}->{'max'}: 999999;
            // Cadena de consulta para cliente
            $cliente_str = " AND (fh.rd_cod >= $cliente_min AND fh.rd_cod <= $cliente_max ) ";
            
            /* Se obtienen los limites para filtrado de fechas */
            $fecha_min = (isset($filtros->{'fecha'}->{'min'}) && !empty($filtros->{'fecha'}->{'min'})) ? $filtros->{'fecha'}->{'min'}: 0;
            $fecha_max = (isset($filtros->{'fecha'}->{'max'}) && !empty($filtros->{'fecha'}->{'max'}) ) ? $filtros->{'fecha'}->{'max'}: 999999;
            // Cadena de consulta para fecha
            $fecha_str = " AND (fh.fefac >= '$fecha_min' AND fh.fefac <= '$fecha_max' ) ";
            
            /* Se obtienen los limites para filtrado de cajas */
            $caja_min = (isset($filtros->{'caja'}->{'min'}) && !empty($filtros->{'caja'}->{'min'}) && is_numeric($filtros->{'caja'}->{'min'})) ? $filtros->{'caja'}->{'min'}: 0;
            $caja_max = (isset($filtros->{'caja'}->{'max'}) && !empty($filtros->{'caja'}->{'max'}) && is_numeric($filtros->{'caja'}->{'max'})) ? $filtros->{'caja'}->{'max'}: 999999;
            // Cadena de consulta para caja
            $caja_str = " AND (fh.caja >= '$caja_min' AND fh.caja <= '$caja_max' ) ";
            
            /* Se obtienen los limites para filtrado de bodegas */
            $bodega_min = (isset($filtros->{'bodega'}->{'min'}) && !empty($filtros->{'bodega'}->{'min'}) && is_numeric($filtros->{'bodega'}->{'min'})) ? $filtros->{'bodega'}->{'min'}: 0;
            $bodega_max = (isset($filtros->{'bodega'}->{'max'}) && !empty($filtros->{'bodega'}->{'max'}) && is_numeric($filtros->{'bodega'}->{'max'})) ? $filtros->{'bodega'}->{'max'}: 999999;
            // Cadena de consulta para bodega
            $bodega_str = " AND (fd.bodega >= '$bodega_min' AND fd.bodega <= '$bodega_max' ) ";
            
            /* Se obtienen los limites para filtrado de lineas */
            $linea_min = (isset($filtros->{'linea'}->{'min'}) && !empty($filtros->{'linea'}->{'min'}) && is_numeric($filtros->{'linea'}->{'min'})) ? $filtros->{'linea'}->{'min'}: 0;
            $linea_max = (isset($filtros->{'linea'}->{'max'}) && !empty($filtros->{'linea'}->{'max'}) && is_numeric($filtros->{'linea'}->{'max'})) ? $filtros->{'linea'}->{'max'}: 999999;
            // Cadena de consulta para linea
            $linea_str = " AND (fd.linea >= '$linea_min' AND fd.linea <= '$linea_max' ) ";
            
            /* Se obtienen los limites para filtrado de proveedors */
            $proveedor_min = (isset($filtros->{'proveedor'}->{'min'}) && !empty($filtros->{'proveedor'}->{'min'}) && is_numeric($filtros->{'proveedor'}->{'min'})) ? $filtros->{'proveedor'}->{'min'}: 0;
            $proveedor_max = (isset($filtros->{'proveedor'}->{'max'}) && !empty($filtros->{'proveedor'}->{'max'}) && is_numeric($filtros->{'proveedor'}->{'max'})) ? $filtros->{'proveedor'}->{'max'}: 999999;
            // Cadena de consulta para proveedor
            $proveedor_str = " AND (pr.id >= '$proveedor_min' AND pr.id <= '$proveedor_max' ) ";
            
            /* Se obtienen los limites para filtrado de colors */
            $color_min = (isset($filtros->{'color'}->{'min'}) && !empty($filtros->{'color'}->{'min'}) && is_numeric($filtros->{'color'}->{'min'})) ? $filtros->{'color'}->{'min'}: 0;
            $color_max = (isset($filtros->{'color'}->{'max'}) && !empty($filtros->{'color'}->{'max'}) && is_numeric($filtros->{'color'}->{'max'})) ? $filtros->{'color'}->{'max'}: 999999;
            // Cadena de consulta para color
            $color_str = " AND (fd.ccolor >= '$color_min' AND fd.ccolor <= '$color_max' ) ";
            
            /* Se obtienen los limites para filtrado de lineas */
            $estilo_min = (isset($filtros->{'estilo'}->{'min'}) && !empty($filtros->{'estilo'}->{'min'}) && is_numeric($filtros->{'estilo'}->{'min'})) ? $filtros->{'estilo'}->{'min'}: '';
            $estilo_max = (isset($filtros->{'estilo'}->{'max'}) && !empty($filtros->{'estilo'}->{'max'}) && is_numeric($filtros->{'estilo'}->{'max'})) ? $filtros->{'estilo'}->{'max'}: 'ZZZZZZZZZZZZZ';
            // Cadena de consulta para estilo
            $estilo_str = " AND (fd.estilo >= '$estilo_min' AND fd.estilo <= '$estilo_max' ) ";
            
            $contado  = (isset($_GET['contado'])&&!empty($_GET['contado']))? $_GET['contado']: 'false';
            $credito  = (isset($_GET['credito'])&&!empty($_GET['credito']))? $_GET['credito']: 'false';
            
            if($contado=='false' && $credito=='false'){
                $contado = 'true';
                $credito = 'true';
            }
            
            if($credito=='true' && $contado=='false'){
                $query_formapago = " AND fh.credito=1 ";
            }else if($credito=='false' && $contado=='true'){
                $query_formapago = " AND fh.credito=0 ";
            }else{
                $query_formapago = " AND fh.credito=1 OR fh.credito=0 ";
            }
            
            $ag  = (isset($_GET['ag'])&&!empty($_GET['ag'])&&is_numeric($_GET['ag'])) ? $_GET['ag']:1;
            
            
            // fechas
            $fechas_query = "SELECT fefac FROM facmesh fh WHERE anulado=0 $query_formapago GROUP BY fefac";
            data_model()->executeQuery($fechas_query);
            $fechas_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $fechas_arr[] = $row['fefac'];
            }
            $cache['fechas'] = data_model()->cacheQuery($fechas_query);
            
            // cajas
            $cajas_query = "SELECT caja FROM facmesh fh WHERE anulado=0 $query_formapago GROUP BY caja";
            $cajas_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $cajas_arr[] = $row['caja'];
            }
            $cache['cajas'] = data_model()->cacheQuery($cajas_query);
            
            // bodegas
            $bodegas_query = "SELECT fd.bodega as bodega FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac WHERE anulado=0 $query_formapago GROUP BY fd.bodega";
            $bodegas_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $bodegas_arr[] = $row['bodega'];
            }
            $cache['bodegas'] = data_model()->cacheQuery($bodegas_query);
            
            // lineas
            $lineas_query = "SELECT fd.linea as linea FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac WHERE anulado=0 $query_formapago GROUP BY fd.linea";
            $lineas_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $lineas_arr[] = $row['linea'];
            }
            $cache['lineas'] = data_model()->cacheQuery($lineas_query);
            
            // proveedores
            $proveedores_query = "SELECT p.proveedor as proveedor FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac  INNER JOIN producto p ON fd.cestilo = p.estilo AND fd.linea = p.linea WHERE anulado=0 $query_formapago GROUP BY p.proveedor";
            $proveedores_arr = array();  
            while($row = data_model()->getResult()->fetch_assoc()){
                $proveedores_arr[] = $row['proveedor'];
            }
            $cache['proveedores'] = data_model()->cacheQuery($proveedores_query);
            
            // territorios
            $territorios_query = "SELECT r.codru as territorio FROM facmesh fh INNER JOIN cliente ON fh.rd_cod = cliente.codigo_afiliado INNER JOIN agente ON cliente.agente = agente.cage INNER JOIN ruta r ON r.agente = agente.cage WHERE anulado=0 $query_formapago GROUP BY r.codru";
            $territorios_arr = array();  
            while($row = data_model()->getResult()->fetch_assoc()){
                $territorios_arr[] = $row['territorio'];
            }
            $cache['territorios'] = data_model()->cacheQuery($territorios_query);
            
            // productos
            $productos_query = "SELECT fd.cestilo as estilo FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac WHERE anulado=0 $query_formapago GROUP BY fd.cestilo";
            $productos_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $productos_arr[] = $row['estilo'];
            }
            $cache['productos'] = data_model()->cacheQuery($productos_query);
            
            // catalogos
            $catalogos_query = "SELECT p.catalogo as catalogo FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac  INNER JOIN producto p ON fd.cestilo=p.estilo AND p.linea=fd.linea WHERE anulado=0 $query_formapago GROUP BY p.catalogo";
            $catalogos_arr = array();
            while($row = data_model()->getResult()->fetch_assoc()){
                $catalogos_arr[] = $row['catalogo'];
            }
            $cache['catalogos'] = data_model()->cacheQuery($catalogos_query);
            
            /* Aca se obtienen los detalles a manera de arreglo (para consulta) */
            $detalles_query = "SELECT fh.caja as caja,fh.serie as serie,fh.nofac as nofac,fh.fefac as fefac,fh.rd_cod as rd_cod,fh.formapago as formapago,fh.credito as credito,fh.anulado as anulado,fh.nomcli as nomcli,fh.dircli as dircli,fh.nitcli as nitcli,SUM(fh.monto) as monto,SUM(fh.iva) as iva,SUM(fh.total) as total,SUM(fd.costo * fd.cantidad) as costo,(((fh.monto - SUM(fd.costo * fd.cantidad)) / SUM(fd.costo * fd.cantidad))*100) as margen 
                            FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac 
                            INNER JOIN cliente ON cliente.codigo_afiliado = fh.rd_cod
                            INNER JOIN agente ON cliente.agente = agente.cage
                            INNER JOIN producto p ON p.estilo = fd.cestilo AND p.linea = fd.linea
                            INNER JOIN proveedor pr ON p.proveedor = pr.id
                            WHERE fh.anulado = 0 
                            $query_formapago 
                            $cliente_str $estilo_str $color_str $linea_str $fecha_str $caja_str $bodega_str $agente_str $proveedor_str
                            GROUP BY nofac";
            
            data_model()->executeQuery($detalles_query);
            
            $detalles_arr = array();
            
            //while($row = data_model()->getResult()->fetch_assoc()){
            //    $detalles_arr[]['cliente'] = $row['codcli'];
            //    $detalles_arr[count($detalles_arr)-1]['agente'] = $row['cage'];
             //   $detalles_arr[count($detalles_arr)-1]['departamento'] = $row['id'];
            //}
            
            /* Aca se usa la misma consulta para obtener los detalles en forma de cache */
            $cache['detalles'] = data_model()->cacheQuery($detalles_query);
            
            $seleccion_arr = array();
            
            if($ag==1){
                foreach($fechas_arr as $fecha){
                    $query = "SELECT fh.caja as caja,fh.serie as serie,fh.nofac as nofac,fh.fefac as fefac,fh.rd_cod as rd_cod,fh.formapago as formapago,fh.credito as credito,fh.anulado as anulado,fh.nomcli as nomcli,fh.dircli as dircli,fh.nitcli as nitcli,SUM(fh.monto) as monto,SUM(fh.iva) as iva,SUM(fh.total) as total,SUM(fd.costo * fd.cantidad) as costo,(((fh.monto - SUM(fd.costo * fd.cantidad)) / SUM(fd.costo * fd.cantidad))*100) as margen 
                            FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac 
                            INNER JOIN cliente ON cliente.codigo_afiliado = fh.rd_cod
                            INNER JOIN agente ON cliente.agente = agente.cage
                            INNER JOIN producto p ON p.estilo = fd.cestilo AND p.linea = fd.linea
                            INNER JOIN proveedor pr ON p.proveedor = pr.id
                            WHERE fh.anulado = 0 AND
                            fh.fefac = '$fecha'
                            $query_formapago 
                            $cliente_str $estilo_str $color_str $linea_str $fecha_str $caja_str $bodega_str $agente_str $proveedor_str
                            GROUP BY fh.fefac"; 
                    
                    $cache['res']['fecha_'.$fecha] = data_model()->cacheQuery($query);
                }   
                
                $seleccion_arr = $fechas_arr;
            }
            
            if($ag==2){
                foreach($cajas_arr as $caja){
                    $query = "SELECT fh.caja as caja,fh.serie as serie,fh.nofac as nofac,fh.fefac as fefac,fh.rd_cod as rd_cod,fh.formapago as formapago,fh.credito as credito,fh.anulado as anulado,fh.nomcli as nomcli,fh.dircli as dircli,fh.nitcli as nitcli,SUM(fh.monto) as monto,SUM(fh.iva) as iva,SUM(fh.total) as total,SUM(fd.costo * fd.cantidad) as costo,(((fh.monto - SUM(fd.costo * fd.cantidad)) / SUM(fd.costo * fd.cantidad))*100) as margen 
                            FROM facmesh fh INNER JOIN facmesd fd ON fd.nofac = fh.nofac 
                            INNER JOIN cliente ON cliente.codigo_afiliado = fh.rd_cod
                            INNER JOIN agente ON cliente.agente = agente.cage
                            INNER JOIN producto p ON p.estilo = fd.cestilo AND p.linea = fd.linea
                            INNER JOIN proveedor pr ON p.proveedor = pr.id
                            WHERE fh.anulado = 0
                            fh.caja = '$caja'
                            $query_formapago 
                            $cliente_str $estilo_str $color_str $linea_str $fecha_str $caja_str $bodega_str $agente_str $proveedor_str
                            GROUP BY fh.caja"; 
                    
                    $cache['res']['caja_'.$caja] = data_model()->cacheQuery($query);
                }   
                
                $seleccion_arr = $cajas_arr;
            }
                        
            $this->view->reporte_Ventas($ag, $cache, $seleccion_arr);
            
        }else{
           HttpHandler::redirect('/cobros/error/not_found');
        }
    }

}

?>