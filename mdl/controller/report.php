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

}

?>