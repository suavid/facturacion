<?php

import('mdl.view.factura');
import('mdl.model.factura');

class facturaController extends controller
{

    private static $SOAP_OPTIONS = array("trace" => 1, "exception" => true, "soap_version"=>SOAP_1_1);

    private function ValidateSession()
    {
        if (!Session::ValidateSession())
            HttpHandler::redirect(DEFAULT_DIR);
    }

    public function principal()     
    {
        $this->ValidateSession();
        $this->view->principal(Session::singleton()->getUser());
    }

    public function ObtenerBanner()
    {
        $this->ValidateSession();
        if(isset($_POST) && !empty($_POST))
        {
            $id = $_POST['id'];
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            $result = $client->VerMensajesBienvenida(array("id"=>$id));

            echo  $result->{"VerMensajesBienvenidaResult"};
        }
    }

    public function series() 
    {
        $this->ValidateSession();
        $usuario = Session::singleton()->getUser();
        $this->view->series($usuario);
    }

    public function cajas() 
    {
        $this->ValidateSession();
        $usuario = Session::singleton()->getUser();
        $this->view->cajas($usuario);
    }

    public function CategoriasDeSeries()
    {
        $this->ValidateSession();
        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->CategoriasDeSeries();

        echo  $result->{"CategoriasDeSeriesResult"};
    }

    public function InsertarSerie()
    {
        if(isset($_POST) && !empty($_POST))
        {
            $this->ValidateSession();
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            
            $fechaResolucion = $_POST['fechaResolucion'];
            $del = $_POST['autorizadoDel'];
            $al = $_POST['autorizadoAl'];
            $serie = $_POST['serie'];
            $descripcion = $_POST['descripcion'];
            $resolucion = $_POST['numeroResolucion'];
            $tipo = $_POST['tipo'];

            $result = $client->InsertarSerie(
                array(
                    "fechaResolucion" => $fechaResolucion
                    , "tipo" => $tipo
                    , "serie" => $serie
                    , "descripcion" => $descripcion
                    , "resolucion" => $resolucion
                    , "del" => $del
                    , "al" => $al
                )
            );

            echo  $result->{"InsertarSerieResult"};
        }
    }

    public function ObtenerSeries()
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerSeries();
        echo  $result->{"ObtenerSeriesResult"};
    }

    public function ObtenerCaja()
    {
        $this->ValidateSession();
        if(isset($_POST) && !empty($_POST))
        {
            $id = $_POST["id"]; 
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            $result = $client->ObtenerCaja(array("id"=>$id));
            echo  $result->{"ObtenerCajaResult"};
        }
    }

    public function ObtenerListaDeSeries()
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerSeries();
        $data = json_decode($result->{"ObtenerSeriesResult"});

        $ret = "{data:" . $result->{"ObtenerSeriesResult"} . ",\n";
        $ret .= "pageInfo:{totalRowNum:" . count($data) . "},\n";
        $ret .= "recordType : 'object'}";

        echo  $ret;
    }

     public function ObtenerListaDeCajas()
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerCajas();
        $data = json_decode($result->{"ObtenerCajasResult"});

        $ret = "{data:" . $result->{"ObtenerCajasResult"} . ",\n";
        $ret .= "pageInfo:{totalRowNum:" . count($data) . "},\n";
        $ret .= "recordType : 'object'}";

        echo  $ret;
    }

    public function ListaBodegas()
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerBodegas(array("tipo_vista"=>0));
        echo $result->{"ObtenerBodegasResult"};
    }

    public function ObtenerEmpleados()
    {
        $this->ValidateSession();
        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerEmpleados(array());

        echo  $result->{"ObtenerEmpleadosResult"};
    }

    public function RegistrarCaja()
    {
        if(isset($_POST) && !empty($_POST))
        {
            $this->ValidateSession();
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            
            $id = $_POST['id'];
            $nombre = $_POST['nombre'];
            $encargado = $_POST['encargado'];
            $bodega_por_defecto = $_POST['bodegaPorDefecto'];
            $serie_factura = $_POST['serieFactura'];
            $serie_nota_credito = $_POST['serieNotaCredito'];
            $serie_recibo = $_POST['serieRecibo'];
            $serie_ticket = $_POST['serieTicket'];
            $p_cambio_bodega = $_POST['pCambioBodega'];
            $serie_credito_fiscal = $_POST['serieCreditofiscal'];
            $serie_nota_debito = $_POST['serieNotaDebito'];
            $serie_nota_remision = $_POST['serieNotaRemision'];

            $result = $client->InsertarCaja(
                array(
                      "id" => $id
                    , "nombre"=> $nombre
                    , "encargado"=> $encargado
                    , "bodega_por_defecto"=> $bodega_por_defecto
                    , "serie_factura"=> $serie_factura
                    , "serie_nota_credito"=> $serie_nota_credito
                    , "serie_recibo"=> $serie_recibo
                    , "serie_ticket"=> $serie_ticket
                    , "p_cambio_bodega"=> $p_cambio_bodega
                    , "serie_credito_fiscal"=> $serie_credito_fiscal 
                    , "serie_nota_debito"=> $serie_nota_debito
                    , "serie_nota_remision"=> $serie_nota_remision
                )
            );

            echo  $result->{"InsertarCajaResult"};
        }
    }

    public function nuevo() 
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

        $result = $client->ValidarCaja(array("usuario" => Session::singleton()->getUser()));

        $data = json_decode($result->{"ValidarCajaResult"});

        if(count($data)==0)
        {
            HttpHandler::redirect("/facturacion/error/e403");
        }

        $this->view->formulario_facturacion();
    }

    public function ValidarCliente()
    {
        $this->ValidateSession();
        if(isset($_POST) && !empty($_POST))
        {
            $idCliente = $_POST["idCliente"];
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            $result = $client->ObtenerInformacionDelCliente(
                array(
                      "id" => $idCliente
                )
            );

            echo  $result->{"ObtenerInformacionDelClienteResult"};
        }
    }

    public function ObtenerDatosDeCaja()
    {
        $this->ValidateSession();
        
        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

        $result = $client->ValidarCaja(array("usuario" => Session::singleton()->getUser()));

        echo  $result->{"ValidarCajaResult"};
    }

    public function GenerarNuevoPedido()
    {
        $this->ValidateSession();

        if(isset($_POST) && !empty($_POST))
        {
            $id_cliente = $_POST["id_cliente"];
            $concepto = $_POST["concepto"];
            $id_caja = $_POST["id_caja"];

            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

            $result = $client->GenerarPedido(array("idCliente"=>$id_cliente, "concepto"=>$concepto, "idCaja"=>$id_caja));

            echo  $result->{"GenerarPedidoResult"};
        }
    }

    public function CargarPedidoExistente()
    {
        $this->ValidateSession();

        if(isset($_POST) && !empty($_POST))
        {
            $id_pedido = $_POST["id_pedido"];

            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

            $result = $client->CargarPedido(array("idPedido"=>$id_pedido));

            echo  $result->{"CargarPedidoResult"};
        }
    }

    public function ListaLineas()
    {
        $this->ValidateSession();

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->VerDetalleCategoria(array("id"=>LINEA));

        echo  $result->{"VerDetalleCategoriaResult"};
    }

    public function CargarStock()
    {
        header('Content-type:text/javascript;charset=UTF-8');
        $this->ValidateSession();
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $temp = explode(',', $_POST["filtros"]);
        $filtros = array();
        foreach ($temp as $parts) {
            $tt = explode(':', $parts);
            $filtros[$tt[0]] = $tt[1];
        }

        $params = array(
            "estilo"=>(isset($filtros["estilo"]))?$filtros["estilo"]:null,
            "linea"=>(isset($filtros["linea"]))?$filtros["linea"]:null,
            "color"=>(isset($filtros["color"]))?$filtros["color"]:null,
            "talla"=>(isset($filtros["talla"]))?$filtros["talla"]:null,
            "bodega"=>(isset($filtros["bodega"]))?$filtros["bodega"]:null,
            "idcolor"=>COLOR
        );

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->CargarStock($params);
        $data = json_decode($result->{"CargarStockResult"});

        $ret = "{data:" . $result->{"CargarStockResult"} . ",\n";
        $ret .= "pageInfo:{totalRowNum:" . count($data) . "},\n";
        $ret .= "recordType : 'object'}";

        echo  $ret;
    }

    public function InsertarDetallePedido()
    {
         if(isset($_POST) && !empty($_POST))
        {
            $this->ValidateSession();
            $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
            
            $detalle = $_POST["data"];

            for($i = 0; $i < count($detalle);  $i++){
                $result = $client->InsertarDetallePedido(
                    array(
                        "id_factura" => $detalle[$i]["pedido"]
                        , "cantidad" => $detalle[$i]["cantidad"]
                        , "bodega" => $detalle[$i]["bodega"]
                        , "linea" => $detalle[$i]["linea"]
                        , "estilo" => $detalle[$i]["estilo"]
                        , "color" => $detalle[$i]["color"]
                        , "talla" => $detalle[$i]["talla"]
                    )
                );
            }

            echo  $result->{"InsertarDetallePedidoResult"};
        }
    }

    public function CargarDetallePedido()
    {
        header('Content-type:text/javascript;charset=UTF-8');
        $this->ValidateSession();
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $idPedido = $_POST["idPedido"];

        $params = array(
            "idPedido"=>$idPedido
        );

        $client  = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);
        $result = $client->ObtenerDetallePedido($params);
        $data = json_decode($result->{"ObtenerDetallePedidoResult"});

        $ret = "{data:" . $result->{"ObtenerDetallePedidoResult"} . ",\n";
        $ret .= "pageInfo:{totalRowNum:" . count($data) . "},\n";
        $ret .= "recordType : 'object'}";

        echo  $ret;
    }

    public function EliminarDetallePedido()
    {
        $this->ValidateSession();
        $data = $_POST;

        $client = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

        try
        {
            $result = $client->EliminarDetallePedido(array(
                    "id"=>$data["id"]
            ));

            $data = json_decode($result->{"EliminarDetallePedidoResult"});

        }
        catch(Exception $e)
        {
            $result["message"] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function ReservarPedido()
    {
        $this->ValidateSession();
        $data = $_POST;

        $client = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

        try
        {
            $result = $client->ReservarPedido(array(
                    "id"=>$data["id"]
            ));

            $data = json_decode($result->{"ReservarPedidoResult"});

        }
        catch(Exception $e)
        {
            $result["message"] = $e->getMessage();
        }

        echo json_encode($result);
    }

    public function Facturar()
    {
        $this->ValidateSession();
        $data = $_POST;

        $client = new SoapClient(SERVICE_URL, self::$SOAP_OPTIONS);

        try
        {
            $result = $client->Facturar(array(
                "id_factura"=>$data["id_factura"]
                , "tipo_pago"=>$data["tipo_pago"]
                , "credito_fiscal"=>$data["credito_fiscal"]
                , "id_boleta_pago"=>$data["id_boleta_pago"]
                , "monto_en_tarjeta"=>$data["monto_en_tarjeta"]
                , "monto_en_efectivo"=>$data["monto_en_efectivo"]
                , "monto_por_deposito"=>$data["monto_por_deposito"]
                , "monto_por_cheque"=>$data["monto_por_cheque"]
                , "monto_credito"=>$data["monto_credito"]
            ));

            $data = json_decode($result->{"FacturarResult"});

        }
        catch(Exception $e)
        {
            $result["message"] = $e->getMessage();
        }

        echo json_encode($result);
    }

    // SIN VALIDAR

    public function anular() {
        $this->ValidateSession();
        $this->view->anular(Session::singleton()->getUser());
    }

    public function resumen() {
        $this->ValidateSession();
        $this->view->resumen(Session::singleton()->getUser());
    }

    public function ver_fiscales() {
        $this->ValidateSession();
        $this->view->ver_fiscales(Session::singleton()->getUser());
    }

    public function ver_pendientes() {
        $this->ValidateSession();
        $this->view->ver_pendientes(Session::singleton()->getUser());
    }

    public function salidas() {
        $this->ValidateSession();
        $this->view->salidas(Session::singleton()->getUser());
    }

    public function reparaciones() {
        $this->ValidateSession();
        $this->view->reparaciones(Session::singleton()->getUser());
    }

    public function descuentos() {
        $this->ValidateSession();
        $this->view->descuentos(Session::singleton()->getUser());
    }

    public function notas_remision() {
        $this->ValidateSession();
        $this->view->notas_remision(Session::singleton()->getUser());
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

        HttpHandler::redirect('/facturacion/factura/series?success=true');
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
        HttpHandler::redirect('/facturacion/factura/cajas?success=true');
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
        $query = "UPDATE detalle_factura set entran = 0 WHERE id_factura = $referencia";
        data_model()->executeQuery($query);
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
				$ret['tipo'] 		= $this->model->get_attr('formapago');	// tipo
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

    public function nueva_nota_remision() {
        $this->ValidateSession();
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
                $query = "SELECT descuento FROM oferta WHERE id = $id";
                data_model()->executeQuery($query);
                $row = data_model()->getResult()->fetch_assoc();
                $response[] = $row['descuento'];
            }

            if(count($response) > 0){
                $info['porcentaje'] = $response[0] * 100;
            }else{
                $info['porcentaje'] = 0;
            }

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
				$monto  = $info['importe'];
                $mntiva = $monto * $poriva;
                $total  = $monto + $mntiva;
				$descuento = $info['descuento'];
                $org = $monto + $descuento;

				// Nota: en este punto no interese incluir el iva en los detalles, solamente se incluye el iva en el total
				// se actualizan los totales de la cabecera de factura
				$query = "UPDATE factura SET iva = (iva + $mntiva), monto=( monto + $monto) , descuento=( descuento + $descuento), total=(total + $total), subtotal=(subtotal + $org)  WHERE id_factura=$factura";
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
        $vale = $_POST['vale'];
        $cf = $_POST['CF'];
        $tipo = $_POST['tipo'];
        $boleta = $_POST['boleta'];
        $banco = $_POST['banco'];
        $this->model->contado($id_factura, $serie, $vale, $cf, $tipo, $boleta, $banco);
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
        $vale = $_POST['vale'];
        $cf = $_POST['CF'];
        $this->model->credito($id_factura, $serie, $vale, $cf);
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
                $monto  = $d['importe']; // Ya se ha aplicado el descuento
                $mntiva = $monto * $poriva;
				$total  = $monto + $mntiva;
				$descuento = $d['descuento'];
                $org = $monto + $d['descuento'];

				// Nota: en este punto no interese incluir el iva en los detalles, solamente se incluye el iva en el total
				// se actualizan los totales de la cabecera de factura
				$query = "UPDATE factura SET iva = (iva + $mntiva), monto=( monto + $monto) , descuento=( descuento + $descuento), total=(total + $total), subtotal=(subtotal + $org)  WHERE id_factura=$factura";
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
        $this->ValidateSession();
        $this->view->verLineas();
    }

    function cargar_remision($factura) {
        $this->ValidateSession();
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
        $this->ValidateSession();
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
        $this->ValidateSession();
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

    public function despachar(){
        $id = $_POST['id'];
        $query = "UPDATE facmesd SET despachado = 1 WHERE id=$id";
        data_model()->executeQuery($query);

        echo json_encode(array("msg"=>""));
    }

    function cargar_detalle_fac() {
        //$this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $serie = $_POST['serie'];
        $nofac = $_POST['nofac'];
        $sql = "select count(*) as cnt from facmesd WHERE serie = '{$serie}' AND nofac=$nofac AND despachado = 0 ";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from facmesd WHERE serie = '{$serie}' AND nofac=$nofac AND despachado = 0 limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from facmesh where cf=0 ORDER BY nofac DESC";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from facmesh where cf=0 ORDER BY nofac DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from facmesh where cf=1 ORDER BY nofac DESC";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from facmesh where cf=1 ORDER BY nofac DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0 and factura.fecha = CURRENT_DATE() and estado!='RESERVADO'";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0 and factura.fecha = CURRENT_DATE() and estado!='RESERVADO' ORDER BY id_factura DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargarReservas() {
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0 and estado='RESERVADO'";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from caja_pedido_referencia join factura on referencia=id_factura join caja on factura.caja=caja.id WHERE facturado=0 and estado='RESERVADO' ORDER BY id_factura DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND rd_cod=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from nota_remision WHERE facturado = 0 AND anulado = 0" . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from nota_remision WHERE facturado = 0 AND anulado = 0" . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND id_cliente=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from nota_remision WHERE anulado = 1" . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from nota_remision WHERE anulado = 1" . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        $this->ValidateSession();
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        $cliente = (isset($_POST['cliente'])) ? " AND id_cliente=" . $_POST['cliente'] : "";

        //to get how many records totally.
        $sql = "select count(*) as cnt from nota_remision WHERE facturado = 1" . $cliente;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from nota_remision WHERE facturado = 1" . $cliente . " limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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