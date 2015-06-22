<?php

import('mdl.view.factura');
import('mdl.model.factura');

class facturaController extends controller {

    public function test() {
        echo "This resource works!!";
    }

    private function validar() {
        if (!Session::ValidateSession())
            HttpHandler::redirect(DEFAULT_DIR);
        //if (!isset($_SESSION['factura']))
        //    HttpHandler::redirect('/facturacion/modulo/listar');
    }

    public function principal() {
        $this->validar();
        $this->view->principal(Session::singleton()->getUser());
    }

    public function anular() {
        $this->validar();
        $this->view->anular(Session::singleton()->getUser());
    }

    public function resumen() {
        $this->validar();
        $this->view->resumen(Session::singleton()->getUser());
    }

    public function ver_fiscales() {
        $this->validar();
        $this->view->ver_fiscales(Session::singleton()->getUser());
    }

    public function ver_pendientes() {
        $this->validar();
        $this->view->ver_pendientes(Session::singleton()->getUser());
    }

    public function salidas() {
        $this->validar();
        $this->view->salidas(Session::singleton()->getUser());
    }

    public function reparaciones() {
        $this->validar();
        $this->view->reparaciones(Session::singleton()->getUser());
    }

    public function descuentos() {
        $this->validar();
        $this->view->descuentos(Session::singleton()->getUser());
    }

    public function notas_remision() {
        $this->validar();
        $this->view->notas_remision(Session::singleton()->getUser());
    }

    public function detalle_pedido() {
        $id_pedido = $_POST['id_pedido'];
        $this->model->detalle_pedido($id_pedido);
    }

    public function detalle_fac(){

        $response = array();
        $dataFac  = $this->model->detalle_fac($_POST['nofac'], $_POST['cambio']);
        $response['html'] = $this->view->detalle_fac($dataFac);
        echo json_encode($response);
    }

    public function set_entrada() {
        $dt = $this->model->get_child('detalle_factura');
        $dt->get($_POST['id']);
        $dt->set_attr('entran', $_POST['cantidad']);
        $dt->save();
    }

    public function reset_proceso() {
        $id = $_POST['id'];
        $this->model->get($id);
        $estado = $this->model->get_attr('estado');
        if ($estado == "PENDIENTE") {
            $query = "UPDATE detalle_factura SET entran = 0 WHERE id_factura=$id";
            data_model()->executeQuery($query);
        }

        echo json_encode(array("msg"=>""));
    }

    public function anular_nota_remision() {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "anulacion nota de remision"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* informacion asociada al registro de la operacion */
        $registro = array();
        $registro['tipo'] = "ANULACION";                          # tipo de la operacion
        $registro['fecha'] = date("Y-m-d");                        # fecha de la operacion
        $registro['usuario'] = Session::singleton()->getUser();    # usuario que lleva a cabo la operacion
        $registro['n_pedido'] = $_POST['pedido'];                     # referencia a pedido
        // datos de caja del usuario
        list($tieneCaja, $data) = $this->model->tieneCaja(Session::singleton()->getUser());


        // obteniendo estado de la factura
        $id = $_POST['id'];
        $this->model->get($id);
        $estado = $this->model->get_attr('estado');

        // verificar si la factura esta pendiente de procesar
        if ($estado == "PENDIENTE") {
            // anular inmediatamente
            $this->model->set_attr('estado', 'ANULADO');
            $this->model->save();

            // se regresa todo el producto a inventario y se revierten los cambios que 
            // fueron realizados a la cuenta del cliente
            $nt = $this->model->get_child('detalle_factura');
            $nt->anular_pedido_total($id);

            // se terminan de almacenar los datos asociados al registro
            $registro['caja'] = $this->model->get_attr('caja');
            $cj = $this->model->get_child('caja');
            $cj->get($this->model->get_attr('caja'));
            $registro['serie'] = $cj->get_attr('codigo_factura');
            // se ha registrado ademas del pedido la caja de facturacion y la serie que estaba activa
            // almacenamos el registros de la operacion realizada
            $op = $this->model->get_child('pnota_remision');
            $op->get(0);
            $op->change_status($registro);
            $op->save();

            $res['success'] = true;   # indicamos el exito de la operacion al cliente
        } else {
            $res['success'] = false;
            $res['message'] = "Error!, El pedido ya habia sido procesado";
        }

        echo json_encode($res);
    }

    public function anl($id) {
        $this->model->get_child('detalle_factura')->total_facturable($id);
    }

    public function reportediarioventas(){
        $hoy = date("Y-m-d");
        $query = "SELECT *,CONCAT(primer_nombre,' ',primer_apellido) as nombrecliente,(monto + iva) as efectivos FROM factura INNER JOIN cliente ON id_cliente=codigo_afiliado  WHERE fecha = '$hoy' AND facturado = 1";
        $facturas = data_model()->cacheQuery($query);
        $nums = array();
        data_model()->executeQuery($query);
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $nums[] = $res['id_factura'];
        }
        $query = "SELECT SUM(monto) as monto, SUM(iva) as iva, SUM(total) as total FROM factura INNER JOIN cliente ON id_cliente=codigo_afiliado  WHERE fecha = '$hoy' AND facturado = 1";
        $sumatoria = data_model()->cacheQuery($query);
        $query = "SELECT SUM(efectivo) as efectivo, SUM(cheque) as cheque, SUM(tarjeta) as tarjeta, SUM(deposito) as deposito, SUM(nota) as nota, SUM(financiado) as credito FROM factura INNER JOIN cliente ON id_cliente=codigo_afiliado  WHERE fecha = '$hoy' AND facturado = 1";
        $datos = data_model()->cacheQuery($query);
        list($tieneCaja, $data) = $this->model->tieneCaja(Session::singleton()->getUser());       
        $this->view->reportediarioventas($facturas, $nums, $sumatoria, $datos, $data['id']);
    }

    public function procesar_nota_remision() {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res                = array();
        $res['transaction'] = "procesar nota de remision"; // operacion que se esta realizando
        $res['message']     = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success']     = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)
        $res['data']        = array();                 // se agrega esta variable solo si se quiere retornar informacion
		
		try{
			$id = $_POST['id'];       # id de referencia al pedido

			$this->model->get($id);   # cargar datos del pedido

			$cliente = $this->model->get_attr('id_cliente');   # cliente que ha realizado el pedido
			$estado = $this->model->get_attr('estado');       # estado de la nota de remision

			/* informacion asociada al registro de la operacion */
			$registro             = array();
			$registro['tipo']     = "PROCESAR";                         # tipo de la operacion
			$registro['fecha']    = date("Y-m-d");                      # fecha de la operacion
			$registro['usuario']  = Session::singleton()->getUser();  # usuario que lleva a cabo la operacion
			$registro['n_pedido'] = $_POST['pedido'];                   # referencia a pedido

			import('scripts.periodos');
			list(, $pactual) = cargar_periodos();                         # se carga el perido actual

			/* la nota solo se procesa en caso que este pendiente */
			if ($estado == "PENDIENTE") {
				$this->model->set_attr('estado', 'PROCESADO');
				$this->model->save();   # automaticamente su estado cambia a procesado para bloquear la nota
				// cargamos los datos de caja del usuario
				// un usuario sin caja no puede procesar notas de remision
				list($tieneCaja, $data) = $this->model->tieneCaja(Session::singleton()->getUser());

				// si no tiene caja se advierte de la falta de permisos para facturar
				if (!$tieneCaja) {
					$ret['message'] = "Lo sentimos, no posee permiso de facturacion";
					$res['success'] = false;
				} else {

					// se terminan de almacenar los datos asociados al registro
					$registro['caja']  = $this->model->get_attr('caja');
					$cj                = $this->model->get_child('caja');
					$registro['serie'] = $cj->get_attr('codigo_factura');
					
					$cj->get($this->model->get_attr('caja'));
					
					// se ha registrado ademas del pedido la caja de facturacion y la serie que estaba activa

					$nt = $this->model->get_child('detalle_factura');   # carga de objeto para proceso de rollback
					// anulacion parcial del pedido, todos aquellos productos marcados como 'entran' son
					// regresados al inventario y descontados de la cuenta del usuario (se abona), con el fin
					// de reducir la carga al credito que habia provocado la adquisicion del producto
					$nt->anular_pedido_parcial($id);

					// se calculan los productos faltantes, es decir aquellos productos que salieron y no regresaron
					// a nivel de registro se manejan como cantidad_que_salio - productos_que_entran = productos faltantes
					$pendiente = $nt->total_facturable($id);

					// si faltan productos, es decir que hay producto que no
					// ha regresado, se factura toda esta diferencia porque el cliente debe pagarla 

					if ($pendiente > 0) {
						$pedido = $nt->facturar_diferencia($id, $cliente, $data['id'], $pactual);
						$res['success'] = true;
						// retorna el numero de pedido que se ha creado
						// el numero de pedido retornado no es la referencia a la base de datos, sino la 
						// referencia de pedido por caja, para que la informacion de facturacion
						// pueda cargarse de forma automatica desde la caja para generar la factura
						// (este proceso debe ser automatico porque una nota de remision procesada no se puede
						// manipular en caja)
						$res['data']['pedido'] = $pedido;
					} else {
						// si no hay pendientes retorna -1 como numero de pedido en aviso de el estado del proceso
						// (ya que no existen pedidos negativos es una buena manera de notificar la accion)
						$res['data']['pedido'] = -1;
						$res['success'] = true;
					}
					$op = $this->model->get_child('pnota_remision');
					$op->get(0);
					$op->change_status($registro);
					$op->save();  // simplemente guarda el registro del suceso
				}
			} else {
				/* si la nota ya estaba en proceso se le comunica al respecto al usuario */
				$res['success'] = false;
				$res['message'] = "Error!, El pedido ya habia sido procesado";
				$res['data']['pedido'] = -1;
			}
		}catch(Exception $e){
			$res['message'] = $e->getMessage();
		}
        echo json_encode($res);
    }

    public function devolver() {
        $ret = array();
        $oCambio = $this->model->get_child('cambio');
        $oCambio->get($_POST['cambio']);
        if ($oCambio->get_attr('editable') == 1) {
            $mv = $this->model->get_child('devolucion');
            $gf = $mv->existe($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla'], $_POST['bodega'], $_POST['factura'], $_POST['cambio']);
            if ($gf) {
                if ($_POST['cantidad'] > 0) {
                    $mv->actualizar($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla'], $_POST['bodega'], $_POST['factura'], $_POST['cambio'], $_POST['cantidad']);
                } else {
                    $mv->borrar($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla'], $_POST['bodega'], $_POST['factura'], $_POST['cambio']);
                }
            } else {
                if ($_POST['cantidad'] > 0) {
                    
                    $linea  = $_POST['linea'];
                    $estilo = $_POST['estilo'];
                    $color  = $_POST['color'];
                    $talla  = $_POST['talla'];
                    $nofac  = $_POST['factura'];
                    $dsv_num = $_POST['cambio'];
                    
                    $query = "UPDATE facmesd SET dsv_num = $dsv_num WHERE linea=$linea AND cestilo='{$estilo}' AND ccolor = $color AND talla=$talla AND nofac=$nofac";
                    data_model()->executeQuery($query);

                    $mv->get(0);
                    $mv->change_status($_POST);
                    $mv->save();
                }
            }
        } else {
            $ret['message'] = "No se puede editar";
        }
        echo json_encode($ret);
    }

    public function consultar_serie() {
        $id = $_POST['id'];
        $caja = $this->model->get_child('caja');
        $caja->get($id);
        $response = array();
        $response['serie_factura'] = $caja->get_attr('serie_factura');
        $response['codigo_factura'] = $caja->get_attr('codigo_factura');
        echo json_encode($response);
    }

    public function cambios() {
        $this->validar();
        $this->view->cambios(Session::singleton()->getUser());
    }

    public function salvar_cambio($id_cambio, $cliente, $fecha) {
        $cambio = $this->model->get_child('cambio');
        $id_cambio = intval($id_cambio);
        $cambio->get($id_cambio);
        $data = array();
        $data['cliente'] = $cliente;
        $data['fecha'] = $fecha;
        $data['username'] = Session::singleton()->getUser();
        if ($id_cambio == 0) {
            $data['activo'] = 1;
            $data['editable'] = 1;
        }
        $cambio->change_status($data);
        $cambio->save();
        if ($id_cambio == 0) {
            $id = $cambio->last_insert_id();
        } else {
            $id = $id_cambio;
        }
        HttpHandler::redirect('/facturacion/factura/cambios_detalle?id=' . $id);
    }

    public function salvar_reparacion($id_reparacion, $cliente, $fecha) {
        $reparacion = $this->model->get_child('reparacion');
        $id_reparacion = intval($id_reparacion);
        $reparacion->get($id_reparacion);
        $data = array();
        $data['cliente'] = $cliente;
        $data['fecha'] = $fecha;
        $data['username'] = Session::singleton()->getUser();
        $reparacion->change_status($data);
        $reparacion->save();
        if ($id_reparacion == 0) {
            $id = $reparacion->last_insert_id();
        } else {
            $id = $id_reparacion;
        }
        HttpHandler::redirect('/facturacion/factura/reparacion_detalle?id=' . $id);
    }

    public function cancelar_cambio() {
        $ret = array();
        $ret['exito'] = false;
        $oCambio = $this->model->get_child('cambio');
        $oCambio->get($_POST['cambio']);
        if ($oCambio->get_attr('editable') == 1) {
            $this->model->get_child('devolucion')->eliminar($_POST['cambio']);
            $oCambio->delete($_POST['cambio']);
            $ret['exito'] = true;
        }

        echo json_encode($ret);
    }

    public function aplicar_devolucion($cambio) {
        $oCambio = $this->model->get_child('cambio');
        $oCambio->get($cambio);
        if ($oCambio->get_attr('editable') == 1) {
            $this->model->get_child('devolucion')->aplicar_devolucion($cambio);
        }
        HttpHandler::redirect('/facturacion/factura/cambios');
    }

    public function cambios_detalle() {
        $this->validar();
        $id_cambio      = $_GET['id'];
        $oCambio        = $this->model->get_child('cambio');

        $oCambio->get($id_cambio);
        
        $activo         = $oCambio->get_attr('activo');
        $editable       = $oCambio->get_attr('editable');
        $cliente        = $oCambio->cliente($id_cambio);
        $oCliente       = $this->model->get_sibling('cliente');
        
        $oCliente->get($cliente);
        
        $nombre_cliente = $oCliente->get_attr('primer_nombre') . ' ' . $oCliente->get_attr('primer_apellido');
        $cache          = array();



        import('scripts.paginacion');
        $numeroRegistros = $this->model->cantidadFacturas($cliente);
        $url_filtro = "/facturacion/factura/cambios_detalle?id=".$id_cambio."&";
        list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
        $cache[0] = $this->model->facturas_cliente($cliente, $limitInf, $tamPag);
        
        if ($activo == 0){
            $activo = "NO";
        }

        $this->view->cambios_detalle(Session::singleton()->getUser(), $cache, $id_cambio, $nombre_cliente, $activo, $editable, $paginacion_str);
    }

    public function reparacion_detalle() {
        $this->validar();
        $id_reparacion = $_GET['id'];
        $oReparacion = $this->model->get_child('reparacion');

        $oReparacion->get($id_reparacion);

        $activo = $oReparacion->get_attr('activo');
        $cliente = $oReparacion->cliente($id_reparacion);
        $oCliente = $this->model->get_sibling('cliente');

        $oCliente->get($cliente);

        $nombre_cliente = $oCliente->get_attr('primer_nombre') . ' ' . $oCliente->get_attr('primer_apellido');
        $cache = array();
        $cache[0] = $this->model->get_child('linea')->get_list('', '', array('nombre'));
        if ($activo == 0)
            $activo = "NO";
        $this->view->reparacion_detalle(Session::singleton()->getUser(), $cache, $id_reparacion, $nombre_cliente, $activo);
    }

    public function elemento_reparacion() {
        $ret = array();
        $ret['existe'] = false;
        $ret['existe'] = $this->model->get_child('estado_bodega')->existe($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla']);

        $data = $_POST;

        if ($ret['existe']) {
            if (empty($_POST['cantidad']))
                $data['cantidad'] = 1;
            $oReparacionD = $this->model->get_child('detalle_reparacion');
            $oReparacionD->get(0);
            $oReparacionD->change_status($data);
            $oReparacionD->save();
        }

        echo json_encode($ret);
    }

    public function facturas_cliente() {
        $this->model->facturas_cliente($_POST['id_cliente']);
    }

    public function cargar_impresion() {
        $system = $this->model->get_child('system');
        $system->get(1);
        $pedido = $_POST['pedido'];
        $oDetalle = $this->model->get_child('detalle_factura');
        $info = array();
        $info['descripcion'] = "Re-Impresion de factura";
        $info['precio'] = $system->get_attr('impresion');
        $info['cantidad'] = 1;
        $info['importe'] = $info['cantidad'] * $info['precio'];
        $info['id_factura'] = $pedido;
        $info['bodega'] = 1;
        $info['linea '] = 0;
        $info['estilo '] = 0;
        $info['color '] = 0;
        $info['talla '] = 0;
        $oDetalle->get(0);
        $oDetalle->change_status($info);
        $oDetalle->save();
        $this->model->get($pedido);
        $this->model->set_attr('total', $system->get_attr('impresion'));
        $this->model->set_attr('subtotal', $system->get_attr('impresion'));
        $this->model->save();
    }

    function cargar_cambio() {
        $id_cambio = $_POST['cambio'];
        $cambio = $this->model->get_child('cambio');
        $response = array();

        $cambio->get($id_cambio);
        $response['codigo_cliente'] = $cambio->get_attr('cliente');
        $response['fecha'] = $cambio->get_attr('fecha');

        echo json_encode($response);
    }

    function cargar_reparacion() {
        $id_reparacion = $_POST['reparacion'];
        $reparacion = $this->model->get_child('reparacion');
        $response = array();

        $reparacion->get($id_reparacion);
        $response['codigo_cliente'] = $reparacion->get_attr('cliente');
        $response['fecha'] = $reparacion->get_attr('fecha');

        echo json_encode($response);
    }

    public function cajas() {
        $this->validar();
        $usuario = Session::singleton()->getUser();
        $cache = array();
        $cache[0] = $this->model->get_child('serie')->get_by_type('FC');
        $cache[1] = $this->model->get_child('serie')->get_by_type('NC');
        $cache[2] = $this->model->get_child('serie')->get_by_type('RC');
        $cache[3] = $this->model->get_child('serie')->get_by_type('TI');
        $cache[4] = $this->model->get_child('bodega')->get_list('','', array('nombre'));
        $cache[5] = $this->model->get_child('empleado')->filter('modulo','facturacion');
        $cache[6] = $this->model->get_child('serie')->get_by_type('CF');
        $cache[7] = $this->model->get_child('serie')->get_by_type('NR');
        $this->view->cajas($usuario, $cache);
    }

    public function traer_cambio() {
        $this->model->traer_cambio($_POST);
    }

    public function productoEntrante(){
        if(isInstalled("compras")){
            $this->view->productoEntrante();
        }else{
            HttpHandler::redirect('/inventario/error/e403');
        }
    }
    
    public function consultaCambio(){
        
        $this->view->consultaCambio();
    }

    public function series() {
        $this->validar();
        $usuario = Session::singleton()->getUser();
        $this->view->series($usuario);
    }

    public function existe_serie() {
        $tipo = $_POST['tipo'];
        $serie = $_POST['serie'];
        $this->model->get_child('serie')->existe_serie($tipo, $serie);
    }

    public function guardar_serie() {
        $serie = $this->model->get_child('serie');
        $id_serie = $serie->get_id($_POST['tipo'], $_POST['serie']);
        $serie->get($id_serie);
        $serie->change_status($_POST);
        $serie->save();

        HttpHandler::redirect('/facturacion/factura/series');
    }

    public function guardar_caja() {
        $id = ( empty($_POST['id']) ) ? 0 : $_POST['id'];
        $caja = $this->model->get_child('caja');

        $data = $_POST;

        if (isset($_POST['p_cambio_bodega']))
            $data['p_cambio_bodega'] = 1;
        else
            $data['p_cambio_bodega'] = 0;

        $caja->get($id);
        $caja->change_status($data);
        $caja->save();
        HttpHandler::redirect('/facturacion/factura/cajas');
    }

    public function datos_anulacion() {
        $numero_factura = $_POST['numero_factura'];
        $numero_caja    = $_POST['numero_caja'];
        $serie_factura  = $_POST['serie_factura'];

        $this->model->datos_anulacion($numero_factura, $numero_caja, $serie_factura);
    }

    public function cargar_datos_caja() {
        $id = $_POST['id'];
        $this->model->cargar_datos_caja($id);
    }

    public function datos_despacho() {
        $serie = $_POST['serie'];
        $numero = $_POST['numero'];
        $this->model->datos_despacho($serie, $numero);
    }

    public function consultar_referencia() {
        $pedido = $_POST['pedido'];
        $caja = $_POST['caja'];
        $response = array();
        $response['referencia'] = -1;
        $response['existe'] = false;
        list($existe, $referencia) = $this->model->existe_pedido($pedido, $caja);
        $response['referencia'] = $referencia;
        $response['existe'] = $existe;
        $response['en_uso'] = false;
        echo json_encode($response);
    }

    public function nueva_factura() {
        $pedido  = (empty($_POST['pedido'])) ? 0 : $_POST['pedido']; // obtiene el numero del pedido 
        $cliente = $_POST['cliente'];							// obtiene el numero del cliente
        $fecha   = $_POST['fecha'];								// obtiene la fecha del pedido
        $caja = $_POST['caja'];									// obtiene la caja en la que se realiza el pedido
        $periodo_actual = $_POST['periodo_actual'];				// periodo en que se factura
        $ret = array();										// prepara la respuesta para el cliente

        $ret['NOTFOUND'] = false;                                    // bandera que indica si se ha encontrado el pedido solicitado								
        $ret['No']       = 0;									// indica la referencia al pedido (id auto incremento)
        $ret['cliente']  = 0;									// cliente que solicita el pedido
        $ret['fecha']    = "";									// fecha de solicitud del pedido
        $ret['estado']   = "PENDIENTE";							// estado del pedido si es nuevo
        $ret['flete']    = 0;									// por defecto no se marca el flete
        $ret['tipo']     = "";									// tipo de pedido

		// si el pedido es diferente de cero (se esta intentando cargar un pedido existente)
		if ($pedido != 0) {
			// verifica si el pedido existe, si existe obtiene la referencia
			// para la verificacion solo se necesita el pedido solicitado y la caja que lo solicita
			list($existe, $referencia) = $this->model->existe_pedido($pedido, $caja);
			if (!$existe)
				$ret['NOTFOUND'] = true; // indica al cliente que el pedido no existe
			else {
				/* si el pedido existe */
				$this->model->get($referencia);  // carga los datos del modelo
				$ret['No'] 		= $referencia; // indica el numero de referencia para futuras operaciones
				$ret['cliente'] 	= $this->model->get_attr('id_cliente'); 	// obtiene l codigo del cliente para el cual se hizo el pedido
				$ret['fecha'] 	= $this->model->get_attr('fecha');		// obtiene la fecha en la cual se hizo el pedido
				$ret['estado'] 	= $this->model->get_attr('facturado');	// obtiene el estado del pedido, 1 = facturado, 0 = pendiente 
				$ret['flete'] 	= $this->model->get_attr('flete');		// tiene flete
				$ret['tipo'] 		= $this->model->get_attr('formapago');;	// tipo
				$ret['ref'] 		= $pedido;							// referencia
			}
		} else {
			/* si el pedido es igual a cero se crea un nuevo pedido */
			$f_data 					= array();
			$f_data['id_cliente'] 		= $cliente;
			$f_data['fecha'] 	 		= $fecha;
			$f_data['caja'] 			= $caja;
			$f_data['estado'] 		= "PENDIENTE";
			$f_data['periodo_actual'] 	= $periodo_actual;
			
			$this->model->get(0);
			$this->model->change_status($f_data);	// se guarda el nuevo pedido
			$this->model->save();
			
			$ret['No'] 		= $this->model->last_insert_id();
			$p 				= array();
			$p['caja'] 		= $caja;
			$p['pedido'] 		= $this->model->crear_pedido($caja);
			$p['referencia'] 	= $ret['No'];
			
			$oj = $this->model->get_child('caja_pedido_referencia');	// se crea la nueva referencia
			
			$oj->get(0);
			$oj->change_status($p);
			$oj->save();
			
			$ret['ref'] 		= $p['pedido'];
			$ret['cliente'] 	= $cliente;
			$ret['fecha'] 	    = $fecha;
		}
		
		echo json_encode($ret);
    }

    public function nuevo() {
        $this->validar();
        $cache = array();

        /* Obener datos de cajero */

        $tieneCaja = false;
        $data = null;
        list($tieneCaja, $data) = $this->model->tieneCaja(Session::singleton()->getUser());

        if (!$tieneCaja)
            HttpHandler::redirect('/facturacion/factura/principal?error=900');

        $cache[0] = $this->model->get_child('bodega')->get_list('','', array('nombre'));
        $cache[1] = $this->model->get_child('linea')->get_list('','', array('nombre'));
        $cache[2] = $this->model->get_sibling('modulo')->obtener_actualizables();
        $numero_factura = $data['ultimo_pedido'] + 1;
        $this->view->formulario_facturacion($numero_factura, $cache, $data);
    }

    public function nueva_nota_remision() {
        $this->validar();
        $cache = array();

        /* Obener datos de cajero */

        $tieneCaja = false;
        $data = null;
        list($tieneCaja, $data) = $this->model->tieneCaja(Session::singleton()->getUser());

        if (!$tieneCaja)
            HttpHandler::redirect('/facturacion/factura/principal?error=900');

        $cache[0] = $this->model->get_child('bodega')->get_list();
        $cache[1] = $this->model->get_child('linea')->get_list();
        $cache[2] = $this->model->get_sibling('modulo')->obtener_actualizables();
        $numero_factura = $data['ultimo_pedido'] + 1;
        $this->view->formulario_remision($numero_factura, $cache, $data);
    }

    public function imprimir($NoFactura) {
        $id_factura = $NoFactura;
		$no_factura = 0;
		$no_pedido  = 0;
		
        $subtotal  = 0.0;
        $descuento = 0.0;
        $total     = 0.0;
		$monto 	   = 0.0;
		$iva 	   = 0.0;
		
		$caja 	   = 0;
		$serie     = '';
		$cpref     = null;
		
		$this->model->get($id_factura);
		$caja  = $this->model->get_attr('caja');
		$cpref = $this->model->get_child('caja_pedido_referencia');
		
		$no_pedido = $cpref->obtener_pedido($caja, $id_factura); 
		
        if($this->model->formapago==1 || $this->model->formapago==2){

            list( $monto, $iva, $subtotal, $descuento, $total, $no_factura, $serie ) = $this->model->datos_facturacion($no_pedido, $caja);
            $cache     = array();
            $cache[0]  = $this->model->DetalleFactura($no_factura, $serie);

        }else if($this->model->formapago==3){
            list( $monto, $iva, $subtotal, $descuento, $total, $no_factura, $serie ) = $this->model->datos_remision($no_pedido, $caja);
            $cache     = array();
            $cache[0]  = $this->model->DetalleRemision($no_factura, $serie);
        }
		
		
        $flete_c   = 0.0;
        $total = $total + 0.0;
        $total = $total + $flete_c;
        $this->view->impFactura($cache, $no_factura, $subtotal, $descuento, $total, $flete_c);
    }

    public function cerrarFactura($NoFactura) {
        $this->model->cerrarFactura($NoFactura);
        HttpHandler::redirect('/facturacion/factura/principal');
    }

    public function getData() {
        mysqli_connect("localhost", "root", "") or
                die("Could not connect: " . mysqli_error());
        mysqli_select_db("facturacion_test") or
                die("Could not select database: " . mysqli_error());
        //Execute a sql query.
        $sql = "select * from factura";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        //This is a 2d array, every element of which contains an array.
        $retArray = array();
        while ($row = mysqli_fetch_object($handle)) {
            $retArray[] = $row;
        }
        //return this 2d array
        return $retArray;
    }

    public function insertar_remision() {
        if (isset($_POST) && !empty($_POST)):
            $cliente = $_POST['cliente'];
            $factura = $_POST['factura'];
            $bodega = $_POST['bodega'];
            $talla = $_POST['talla'];
            $estilo = $_POST['estilo'];
            $linea = $_POST['linea'];
            $cantidad = $_POST['cantidad'];
            $color = $_POST['color'];
            $d = array();
            $d['descripcion'] = $_POST['descripcion'];
            $d['precio'] = $_POST['precio'];
            $d['cantidad'] = $_POST['cantidad'];
            $d['porcentaje'] = $_POST['porcentaje'];
            $d['importe'] = $cantidad * $_POST['precio'];
            $d['descuento'] = $d['importe'] * ($d['porcentaje'] / 100) * -1;
            $d['id_remision'] = $_POST['factura'];

            $response = array();
            $response['STATUS'] = "ERROR";
            unset($d['cliente']);
            unset($d['bodega']);
            $query = "UPDATE remision SET id_cliente=$cliente WHERE id_remision=$factura";
            data_model()->executeQuery($query);
            $query = "SELECT stock FROM estado_bodega WHERE bodega=$bodega AND estilo=$estilo AND linea=$linea AND talla=$talla AND color=$color";
            data_model()->executeQuery($query);
            $s = data_model()->getResult()->fetch_assoc();
            $stock = $s['stock'];
            if ($stock >= $cantidad):
                $query = "UPDATE estado_bodega SET stock=($stock-$cantidad) WHERE bodega=$bodega AND estilo=$estilo AND linea=$linea AND talla=$talla AND color=$color";
                data_model()->executeQuery($query);
                $detalle = $this->model->get_child('detalle_remision');
                $detalle->get(0);
                $detalle->change_status($d);
                $detalle->save();
                $response['STATUS'] = "OK";
            else:
                $response['STATUS'] = "OUT_OF_STOCK";
            endif;
            echo json_encode($response);
        else:
            echo "ERROR!, WRONG CALL PROCEDURE";
        endif;
    }

    public function insercionMultiple() {
		$json = json_decode(stripslashes($_POST["productos"]));
		$ret  = array();
		$ret["error"] = false;
		foreach ($json as $item) {
			$info = array();
			$info['descripcion'] = $item->{"descripcion"};
			$info['precio'] = $item->{"precio"};
			$info['cantidad'] = $item->{"cantidad"};
			$info['porcentaje'] = $item->{"porcentaje"};
			$info['descuento'] = $item->{"cantidad"} * $item->{"precio"} * ($item->{"porcentaje"} / 100) * -1;
			$info['importe'] = $item->{"cantidad"} * $item->{"precio"};
			$info['id_factura'] = $item->{"factura"};
			$info['bodega'] = $item->{"bodega"};
            $info['costo'] = $item->{"costo"};
			$factura = $item->{"factura"};

			$bodega = $item->{"bodega"};
			$estilo = $item->{"estilo"};
			$linea = $item->{"linea"};
			$color = $item->{"color"};
			$talla = $item->{"talla"};
			$cantidad = $info['cantidad'];
			$descuento = $info['descuento'];
			$importe = $info['importe'];

			$info['estilo'] = $item->{"estilo"};
			$info['linea'] = $item->{"linea"};
			$info['color'] = $item->{"color"};
			$info['talla'] = $item->{"talla"};

            $query = "SELECT id_oferta FROM oferta_producto INNER JOIN oferta ON id_oferta = oferta.id WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND vencida = 0";
			data_model()->executeQuery($query);

            $rep = array();

            while ($data = data_model()->getResult()->fetch_assoc()) {
                $rep[] = $data['id_oferta'];
            }


            $response = array();

            foreach ($rep as $id) {
                $query = "SELECT * FROM oferta WHERE id = $id";
                data_model()->executeQuery($query);
                $response[] = data_model()->getResult()->fetch_assoc();
            }

            if(isset($info['porcentaje']))
                $info['porcentaje'] = $response[0]['descuento'] * 100;
            else
                $info['porcentaje'] = 0;
            $info['descuento']  = $info['importe'] * ($info['porcentaje'] / 100) ;
            $info['importe']    = $info['importe'] - $info['descuento'];

            
			$query = "SELECT stock FROM estado_bodega WHERE bodega=$bodega AND estilo='$estilo' AND linea=$linea AND talla=$talla AND color=$color";
			data_model()->executeQuery($query);
			$s = data_model()->getResult()->fetch_assoc();
			$stock = $s['stock'];

			if ($stock >= $item->{"cantidad"}) {
				$cantidad = $item->{"cantidad"};
				$query = "UPDATE estado_bodega SET stock=(stock-$cantidad) WHERE bodega=$bodega AND estilo='$estilo' AND linea=$linea AND talla=$talla AND color=$color";
				data_model()->executeQuery($query);
				$detalle = $this->model->get_child('detalle_factura');
				if ($this->model->existe($linea, $estilo, $color, $talla, $factura)) {
					$this->model->actualizar($linea, $estilo, $color, $talla, $factura, $cantidad, $importe, $descuento);
				} else {
					$detalle->get(0);
					$detalle->change_status($info);
					$detalle->save();
				}
				
				$system = $this->model->get_child('system');
				$system->get(1);
				$iva    = $system->get_attr('iva');
				$poriva = $iva / 100;
				$total  = $info['importe'];
				$monto  = $total / (1 + $poriva);
				$mntiva = $total - $monto;
				$descuento = $info['descuento'];
				
				// Nota: en este punto no interese incluir el iva en los detalles, solamente se incluye el iva en el total
				// se actualizan los totales de la cabecera de factura
				$query = "UPDATE factura SET iva = (iva + $mntiva), monto=( monto + $monto) , descuento=( descuento + $descuento), total=(total + ( $total + $descuento )), subtotal=(subtotal + $total)  WHERE id_factura=$factura";
				data_model()->executeQuery($query);
                //$ret['sql'] = $query;
			} else {
				$ret["error"] = true;
			}
		}

		echo json_encode($ret);
    }

     public function contado($id_factura) {
        $serie = $_POST['serie'];
        $this->model->contado($id_factura, $serie);
        echo json_encode(array("msg"=>""));
     }

     public function consignar($id_factura) {
        $serie = $_POST['serie'];
        $this->model->consignar($id_factura, $serie);
        echo json_encode(array("msg"=>""));
     }

    public function cf_contado($id_factura) {
        $serie = $_POST['serie'];
        $this->model->cf_contado($id_factura, $serie);
        echo json_encode(array("msg"=>""));
    }

    public function credito($id_factura) {
        $serie = $_POST['serie'];
        $this->model->credito($id_factura, $serie);
    }

    public function cf_credito($id_factura) {
        $serie = $_POST['serie'];
        $this->model->cf_credito($id_factura, $serie);
        echo json_encode(array("msg"=>""));
    }

    public function nota_remision($id_factura) {
        $this->model->nota_remision($id_factura);
        echo json_encode(array("msg"=>""));
    }

    public function reservar($id_factura) {
        $this->model->reservar($id_factura);
        echo json_encode(array("msg"=>""));
    }

    public function p_anular() {
        $this->model->p_anular($_POST['serie'], $_POST['nofac']);
		/*$data = $_POST;
		unset($data['serie']);
        $an = $this->model->get_child('anulacion');
        $an->get(0);
        $an->change_status($data);
        $an->save();*/
    }
	
    public function totales($codPedido) {
        $this->model->totales($codPedido);
    }

    public function eliminar_detalle($id) {
        $this->model->eliminar_detalle($id);
    }

    public function esta_vacia($codPedido) {
        $this->model->esta_vacia($codPedido);
    }

    public function insertar() {
		if (isset($_POST) && !empty($_POST)):
			$cliente  = $_POST['cliente'];
			$factura  = $_POST['factura'];
			$bodega   = $_POST['bodega'];
			$talla    = $_POST['talla'];
			$estilo   = $_POST['estilo'];
			$linea    = $_POST['linea'];
			$cantidad = $_POST['cantidad'];
			$color    = $_POST['color'];

			$d = array();
			
			$d['descripcion'] = $_POST['descripcion'];
			$d['precio'] 		= $_POST['precio'];
            $d['costo']        = $_POST['costo'];
			$d['cantidad'] 	= $_POST['cantidad'];
			$d['porcentaje'] 	= $_POST['porcentaje'];
			$d['importe'] 	= $cantidad * $_POST['precio'];
			$d['descuento'] 	= round($d['importe'] * ($d['porcentaje'] / 100), 2) ;
            $d['importe']   = $d['importe'] - $d['descuento'];
			$d['id_factura'] 	= $_POST['factura'];
			$d['bodega'] 		= $_POST['bodega'];

			$response 		  = array();
			$response['STATUS'] = "ERROR";
			
			unset($d['cliente']);

			$d['linea']  = $linea;
			$d['estilo'] = $estilo;
			$d['color']  = $color;
			$d['talla']  = $talla;

			// inserta o actualiza el numero del cliente para el pedido 
			$query = "UPDATE factura SET id_cliente = $cliente WHERE id_factura = $factura";
			data_model()->executeQuery($query);
			
			// consulta el stock actual del producto solicitado
			$query = "SELECT stock FROM estado_bodega WHERE bodega=$bodega AND estilo=$estilo AND linea=$linea AND talla=$talla AND color=$color";
			data_model()->executeQuery($query);
			$s     = data_model()->getResult()->fetch_assoc();
			$stock = $s['stock'];
			
			// si hay suficiente stock para solventar el pedido
			if ($stock >= $cantidad):
				// disminuye el stock de bodega (aparta el producto)
				// salida no se registra en kardex porque en este punto el producto no se ha facturado (no ha salido de inventario, solo se reserva)
				$query = "UPDATE estado_bodega SET stock=(stock-$cantidad) WHERE bodega=$bodega AND estilo=$estilo AND linea=$linea AND talla=$talla AND color=$color";
				data_model()->executeQuery($query);
				
				// si el producto ya ha sido agregado a la factura se actualiza la cantidad, donde cantidad = cantidad + nueva_cantidad
				if ($this->model->existe($linea, $estilo, $color, $talla, $factura)) {
					$this->model->actualizar($linea, $estilo, $color, $talla, $factura, $cantidad, $d['importe'], $d['descuento']);
				} else {
					// si el producto no ha sido igresado previamente el mismo se inserta
					$detalle = $this->model->get_child('detalle_factura');
					$detalle->get(0);
					$detalle->change_status($d);
					$detalle->save();
				}
				
				$system = $this->model->get_child('system');
				$system->get(1);
				$iva    = $system->get_attr('iva');
				$poriva = $iva / 100;
				$total  = $d['importe'];
				$monto  = $total / (1 + $poriva);
				$mntiva = $total - $monto;
				$descuento = $d['descuento'];
				
				// Nota: en este punto no interese incluir el iva en los detalles, solamente se incluye el iva en el total
				// se actualizan los totales de la cabecera de factura
				$query = "UPDATE factura SET iva = (iva + $mntiva), monto=( monto + $monto) , descuento=( descuento + $descuento), total=(total + ( $total + $descuento )), subtotal=(subtotal + $total)  WHERE id_factura=$factura";
				data_model()->executeQuery($query);
			
				$response['STATUS'] = "OK";	// informa del exito del proceso al cliente
			else:
				// si no hay suficiente producto en bodega se envía un mensaje al cliente
				$response['STATUS'] = "OUT_OF_STOCK";
			endif;
			echo json_encode($response);
		else:
			echo "ERROR!, WRONG CALL PROCEDURE";
		endif;
    }

    function verLineas() {
        $this->validar();
        $this->view->verLineas();
    }

    function cargar_remision($factura) {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from detalle_remision WHERE id_remision = $factura ";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from detalle_remision WHERE id_remision = $factura limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
	
	function cargar_factura($serie, $nofac) {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from facmesd WHERE serie='$serie' AND nofac=$nofac ";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select descripcion, precio, cantidad, pordes, valdes, importe from facmesd as f join producto as p on (p.estilo = f.cestilo AND p.linea=f.linea)  WHERE serie='$serie' AND nofac=$nofac limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    function cargar_detalle($factura) {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from detalle_factura WHERE id_factura = $factura ";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from detalle_factura WHERE id_factura = $factura limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function resumenFacturas() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from facmesh ORDER BY nofac DESC";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from facmesh ORDER BY nofac DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function resumenFiscal() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from id_creditos_fiscales join factura on id_factura=id_pedido join caja on caja=caja.id join caja_pedido_referencia on id_factura=referencia";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select id_creditos_fiscales.id as id, factura.caja, caja_pedido_referencia.pedido as pedido,factura.fecha as fecha,factura.estado as estado, factura.tipo as tipo,codigo_factura as serie, factura.subtotal as subtotal, factura.descuento as descuento, factura.total as total from id_creditos_fiscales join factura on id_factura=id_pedido join caja on caja=caja.id join caja_pedido_referencia on id_factura=referencia limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function cargarPendientes() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0 ORDER BY id_factura DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function cargarRemisionPendiente() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND rd_cod=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from nota_remision WHERE facturado = 0 " . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from nota_remision WHERE facturado = 0 " . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function cargarRemisionAnulada() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND id_cliente=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE tipo='REMISION' AND estado='ANULADO'" . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE tipo='REMISION' AND estado='ANULADO'" . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function cargarRemisionProcesada() {
        $this->validar();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND id_cliente=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE tipo='REMISION' AND estado='ANULADO'" . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE tipo='REMISION' AND estado='PROCESADO'" . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = $row;
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function consultar_oferta() {
        $linea  = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color  = $_POST['color'];
        $talla  = $_POST['talla'];
        $this->model->get_child('oferta')->consultar_oferta($linea, $estilo, $color, $talla);
    }

}

?>