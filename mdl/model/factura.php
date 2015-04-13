<?php

class facturaModel extends object {

	//@override

	public function init_globals(){
		// declaracion de variables miembro globales
	}

    public function reservar_factura() {
        $query = "SELECT MAX(id_factura) as id FROM factura LOCK IN SHARE MODE";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        $num = 0;
        if (data_model()->getNumRows() == 0):
            $num = 1;
        else:
            $num = $data['id'] + 1;
        endif;
        $query = "INSERT INTO reserva_numero(id_factura_reservada) VALUES($num);";
        data_model()->executeQuery($query);
        $fecha = date('y-m-d');
        $query = "INSERT INTO factura(fecha) VALUES('{$fecha}')";
        data_model()->executeQuery($query);
        return $num;
    }

    public function traer_cambio($data) {
        $cambio = $data['cambio'];
        $cliente = $data['cliente'];
        $pedido = $data['pedido'];
        $pedido_r = $data['pedido_r'];
        $caja = $data['caja'];
        $serie = $data['serie'];
        $response = array();
        $oCambio = $this->get_child('cambio');
        if ($oCambio->exists($cambio)) {
            $oCambio->get($cambio);
            if ($oCambio->get_attr('cliente') == $cliente) {
                if ($oCambio->get_attr('en_factura') == 0) {
                    $query = "SELECT * FROM devolucion WHERE cambio=$cambio";
                    data_model()->executeQuery($query);
                    $items = array();
                    while ($tr = data_model()->getResult()->fetch_assoc()) {
                        $items[] = $tr;
                    }

                    foreach ($items as $item) {
                        $oDetalle = $this->get_child('detalle_factura');
                        $info = array();
                        $info['descripcion'] = "CAMBIO NO." . $cambio . " PROCESADO, Factura NO." . $item['factura'];
                        $info['precio'] = $this->get_child('control_precio')->consultar_precio($item['linea'], $item['estilo'], $item['color'], $item['talla']);
                        $info['cantidad'] = $item['devueltos'];
                        $info['importe'] = $info['cantidad'] * $info['precio'];
                        $info['id_factura'] = $pedido;
                        $info['bodega'] = $item['bodega'];
                        $info['linea '] = $item['linea'];
                        $info['estilo '] = $item['estilo'];
                        $info['color '] = $item['color'];
                        $info['talla '] = $item['talla'];
                        $oDetalle->get(0);
                        $oDetalle->change_status($info);
                        $oDetalle->save();
                        $this->get($pedido);
                        $this->set_attr('total', $this->get_attr('total') + $info['importe']);
                        $this->set_attr('subtotal', $this->get_attr('subtotal') + $info['importe']);
                        $this->save();
                    }

                    $oCambio->set_attr('en_factura', true);
                    $oCambio->set_attr('pedido', $pedido_r);
                    $oCambio->set_attr('caja', $caja);
                    $oCambio->set_attr('serie', $serie);
                    $oCambio->save();
                } else {
                    $response['message'] = "Este cambio ya esta cargado a otro pedido";
                }
            } else {
                $response['message'] = "Este cambio no corresponde con el cliente que lo solicita";
            }
        } else {
            $response['message'] = "Error: codigo no valido";
        }

        echo json_encode($response);
    }

    public function totales($codPedido) {
        $query = "SELECT total,subtotal,descuento FROM factura WHERE id_factura=$codPedido";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        echo json_encode($data);
    }

    public function tieneCaja($usuario) {
        $query = "SELECT * FROM caja WHERE encargado='{$usuario}' ";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        if (data_model()->getNumRows() > 0)
            return array(true, $data);
        else
            return array(false, null);
    }

    public function cargar_datos_caja($id) {
        $query = "SELECT * FROM caja WHERE id=$id ";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();

        echo json_encode($ret);
    }

    public function eliminar_detalle($id) {
		$query = "SELECT bodega,linea,estilo,color,talla,cantidad,descuento,importe,id_factura FROM detalle_factura WHERE id = $id";
		data_model()->executeQuery($query);
		$data = data_model()->getResult()->fetch_assoc();

		$linea     = $data['linea'];
		$estilo    = $data['estilo'];
		$color     = $data['color'];
		$talla     = $data['talla'];
		$bodega    = $data['bodega'];
		$cantidad  = $data['cantidad'];
		$descuento = $data['descuento'];
		$importe   = $data['importe'];
		$factura   = $data['id_factura'];

		$flg = false;
		$inv = $this->get_child('bodega');
		$flg = $inv->existe($linea, $estilo, $color, $talla, $bodega);

		if ($flg) {
			$inv->act_stock($linea, $estilo, $color, $talla, $bodega, $cantidad);
		} else {
			$inv->ins_stock($linea, $estilo, $color, $talla, $bodega, $cantidad);
		}

		$query = "DELETE FROM detalle_factura WHERE id = $id";
		data_model()->executeQuery($query);
		
		$system = $this->get_child('system');
		$system->get(1);
		$iva    = $system->get_attr('iva');
		$poriva = $iva / 100;
		$total  = $importe;
		$monto  = $total / (1 + $poriva);
		$mntiva = $total - $monto;
		
		$query = "UPDATE factura SET iva = (iva - $mntiva), monto = (monto - $monto), subtotal=(subtotal - $total) , descuento = ( descuento - $descuento), total= (total - ( $total + $descuento ) ) WHERE id_factura=$factura";
		data_model()->executeQuery($query);

		echo json_encode(array("msg"=>""));
    }

    public function esta_vacia($codPedido) {
        $ret = array();
        $query = "SELECT * FROM detalle_factura WHERE id_factura = $codPedido";
        data_model()->executeQuery($query);
        $ret['vacia'] = true;
        if (data_model()->getNumRows() > 0)
            $ret['vacia'] = false;
        echo json_encode($ret);
    }

    public function contado($id_factura, $serie) {
		$query = "START TRANSACTION";
		data_model()->executeQuery($query);
		$query = "UPDATE factura SET efectivo = monto, venta = monto,  facturado = 1, formapago = 1 WHERE id_factura = $id_factura";
		data_model()->executeQuery($query);
		$query = "UPDATE serie SET ultimo_utilizado = (ultimo_utilizado + 1) WHERE id = $serie";
		data_model()->executeQuery($query);
		$query = "SELECT ultimo_utilizado FROM serie WHERE id = $serie";
		data_model()->executeQuery($query);
		$ret   = data_model()->getResult()->fetch_assoc();
			
		$nofac = $ret['ultimo_utilizado'];  // numero de factura que corresponde
		
		/* creacion de modelos e inicializacion de los mismos */
		$this->get($id_factura); 					// inicializacion de pedidos
		$facmesh = $this->get_child('facmesh');		// creacion de modelo para cabecera de facturas
		$facmesh->get(0);							// inicializacion de modelo para cabecera de facturas
		$facmesd = $this->get_child('facmesd');		// creacion de modelo para detalle de facturas
		$facmesd->get(0);							// inicializacion de modelo para detalle de facturas
		$seriemd   = $this->get_child('serie');		// creacion de modelo para serie de documentos 
		$seriemd->get($serie);							// se inicializa para la serie actual de factura
		$cpr	 = $this->get_child('caja_pedido_referencia'); // creacion de modelo para caja_pedido_referencia	
		$cliente = $this->get_sibling('cliente');   // creacion de modelo para cliente
		$cliente->get($this->get_attr('id_cliente'));
		$producto = $this->get_child('producto');
		
		/* proceso de creacion de la cabecera de la factura */
		$facmesh->set_attr('caja', $this->get_attr('caja'));
		$facmesh->set_attr('codtra', '2A');
		$facmesh->set_attr('serie', $seriemd->get_attr('serie'));
		$facmesh->set_attr('nofac', $nofac);
		$facmesh->set_attr('fefac', date('Y-m-d'));
		$facmesh->set_attr('noped', $cpr->obtener_pedido($this->get_attr('caja'), $id_factura));
		$facmesh->set_attr('rd_cod', $this->get_attr('id_cliente'));
		
		$facmesh->set_attr('codven', '0');  // por el momento ya que aun no se migran empleados
		
		$facmesh->set_attr('venta_b', $this->get_attr('subtotal'));
		$facmesh->set_attr('descuento', $this->get_attr('descuento'));
		$facmesh->set_attr('venta_n', $this->get_attr('total'));
		$facmesh->set_attr('formapago', $this->get_attr('formapago'));
		$facmesh->set_attr('efectivo', $this->get_attr('efectivo'));
		$facmesh->set_attr('cheque', $this->get_attr('cheque'));
		$facmesh->set_attr('tarjeta', $this->get_attr('tarjeta'));
		$facmesh->set_attr('ta_numero', $this->get_attr('ta_numero'));
		$facmesh->set_attr('ta_casa', $this->get_attr('ta_casa'));
		$facmesh->set_attr('deposito', $this->get_attr('deposito'));
		
		// esta siendo facturado al contado
		$credito = '0';
		
		$facmesh->set_attr('credito', $credito); // si formapago == 2 poner credito a 1, caso contrario a cero
		$facmesh->set_attr('financiado', $this->get_attr('financiado'));
		$facmesh->set_attr('financiera', $this->get_attr('financiera'));
		$facmesh->set_attr('facturado', '0'); // deberia ser 1 pero todas estan en cero
		$facmesh->set_attr('anulado', '0');   // se acaba de crear, no puede estar anulada
		$facmesh->set_attr('despacho', '0');  // no se ocupa al parecer
		$facmesh->set_attr('prepedido', '0'); // no se ocupa al parecer
		$facmesh->set_attr('usuario', '0');   // no se ocupa al parecer
		
		$nomcli = '';
		$nomcli .= $cliente->get_attr('primer_nombre').' ';
		$nomcli .= $cliente->get_attr('segundo_nombre').' ';
		$nomcli .= $cliente->get_attr('primer_apellido').' ';
		$nomcli .= $cliente->get_attr('segundo_apellido');
		
		$facmesh->set_attr('nomcli', $nomcli);
		$facmesh->set_attr('dircli', $cliente->get_attr('direccion'));
		$facmesh->set_attr('nitcli', $cliente->get_attr('nit'));
		$facmesh->set_attr('venta', $this->get_attr('venta'));
		$facmesh->set_attr('servicio', $this->get_attr('servicio'));
		$facmesh->set_attr('monto', $this->get_attr('monto'));
		$facmesh->set_attr('iva', $this->get_attr('iva'));
		$facmesh->set_attr('total', $this->get_attr('total'));
		$facmesh->set_attr('facturadop', '0');
		$facmesh->set_attr('fefacturad', '');
		$facmesh->set_attr('descuentop', '0');
		
		$facmesh->save();
		$kardex = $this->get_child('kardex');

		// obtencion de los detalles de las facturas
		$query = "SELECT * FROM detalle_factura WHERE id_factura=$id_factura";
		data_model()->executeQuery($query);
		$buffer_detail = array();
		while($result = data_model()->getResult()->fetch_assoc()){
			
			$buffer_detail[] = $result;
		}
		
		// proceso del detalle de la factura
		foreach($buffer_detail as $item){
			
			$info = array();
			$info['linea']  = $item['linea'];
			$info['estilo'] = $item['estilo'];
			$producto->get($info); 
			
			$facmesd->get(0);
			
			$facmesd->set_attr('cordia','0');  // por el momento parece que no se usa
			$facmesd->set_attr('caja',$this->get_attr('caja'));
			$facmesd->set_attr('codtra','2A');
			$facmesd->set_attr('serie',$seriemd->get_attr('serie'));
			$facmesd->set_attr('nofac',$nofac);
			$facmesd->set_attr('fefac',date('Y-m-d'));
			$facmesd->set_attr('hora',date('H:m:s'));
			$facmesd->set_attr('bodega', $item['bodega']);
			$facmesd->set_attr('linea', $item['linea']); 
			$facmesd->set_attr('cestilo', $item['estilo']);
			$facmesd->set_attr('estilo','0'); 
			$facmesd->set_attr('ccolor', $item['color']);
			$facmesd->set_attr('talla', $item['talla']);
			$facmesd->set_attr('precio', $item['precio']);
			$facmesd->set_attr('costo','0');      // por el momento no se toma en cuenta, consultar si es necesario
			$facmesd->set_attr('cantidad', $item['cantidad']);
			$facmesd->set_attr('pordes', $item['pordes']);
			$facmesd->set_attr('valdes','0');     // no se han implementado vales de descuento
			$facmesd->set_attr('importe', $item['importe']); 
			$facmesd->set_attr('propiedad', $item['propiedad']);
			$facmesd->set_attr('factura','0');    // al parece no se ocupa
			$facmesd->set_attr('caja_a','0');     // al parece no se ocupa
			$facmesd->set_attr('catalogo', $producto->get_attr('catalogo'));
			$facmesd->set_attr('entregado','0');  // al parece no se ocupa
			$facmesd->set_attr('item_inc','0');   // al parece no se ocupa
			$facmesd->set_attr('id_factura','0'); // al parece no se ocupa
			$facmesd->set_attr('id_prod','0');    // al parece no se ocupa
			$facmesd->set_attr('rd_cod','0');     // al parece no se ocupa
			$facmesd->set_attr('codpro','0');     // al parece no se ocupa  
			$facmesd->set_attr('pagina', $producto->get_attr('n_pagina'));
			$facmesd->set_attr('id_facd','0');    // al parece no se ocupa
			$facmesd->set_attr('devolucion','0'); // al parece no se ocupa
			$facmesd->set_attr('dsv_caja','0');   // al parece no se ocupa
			$facmesd->set_attr('dsv_num','0');    // al parece no se ocupa
			$facmesd->set_attr('descuentop','0'); // al parece no se ocupa
			
			$facmesd->save();
			$kardex->generar_salida($item['linea'], $item['estilo'], $item['color'], $item['talla'], $item['cantidad'], "Facturacion de producto al contado");
		}
		
		$query = "COMMIT";
		data_model()->executeQuery($query);
    }

    public function cf_contado($id_factura, $serie) {
        $query = "START TRANSACTION";
        data_model()->executeQuery($query);
        $query = "UPDATE factura SET estado = 'FACTURADO', tipo='CF_CONTADO' WHERE id_factura=$id_factura";
        data_model()->executeQuery($query);
        $query = "UPDATE serie SET ultimo_utilizado = (ultimo_utilizado + 1) WHERE id = $serie";
        data_model()->executeQuery($query);
        $query = "SELECT ultimo_utilizado FROM serie WHERE id = $serie";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();
        $ultimo = $ret['ultimo_utilizado'];
        $query = "INSERT INTO id_creditos_fiscales VALUES( $ultimo,$serie,$id_factura )";
        data_model()->executeQuery($query);
        $query = "COMMIT";
        data_model()->executeQuery($query);
    }

    public function credito($id_factura, $serie) {
        $ret = array();
        $query = "START TRANSACTION";
        data_model()->executeQuery($query);
		import('scripts.alias');
        $this->get($id_factura);
        $id_cliente = $this->get_attr('id_cliente');
        $cliente    = $this->get_sibling('cliente');
        $cliente->get($id_cliente);

        $credito = $cliente->get_attr('credito') + $cliente->get_attr('monto_extra');
        $usado   = $cliente->get_attr('credito_usado');
        $disponible = $credito - $usado;
        $monto      = $this->get_attr('total');

        if ($cliente->get_attr('tcredito') == 1) {
            if ($monto <= $disponible) {
                $cliente->set_attr('credito_usado', $usado + $monto);
                $cliente->set_attr('monto_extra', 0.0);
                $cliente->set_attr('extra_credito', 0);
                $cliente->save();
                import('scripts.alias');
				
                $this->get($id_factura);
                $this->set_attr('fecha_vence', sumar_dias_habiles($this->get_attr('fecha'), $cliente->get_attr('dias_credito')));
                $this->save();
				
                $query = "UPDATE factura SET financiado = total, saldofinanciar = total, cuota = total, facturado = 1, formapago = 2 WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
                $query = "UPDATE factura SET cuota = total WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
                $query = "UPDATE serie SET ultimo_utilizado = (ultimo_utilizado + 1) WHERE id = $serie ";
                data_model()->executeQuery($query);
                $query = "SELECT ultimo_utilizado FROM serie WHERE id = $serie";
                data_model()->executeQuery($query);
                $ret = data_model()->getResult()->fetch_assoc();
                $ultimo = $ret['ultimo_utilizado'];
               
				$nofac = $ultimo;
				
				/* creacion de modelos e inicializacion de los mismos */
				$this->get($id_factura); 					// inicializacion de pedidos
				$facmesh = $this->get_child('facmesh');		// creacion de modelo para cabecera de facturas
				$facmesh->get(0);							// inicializacion de modelo para cabecera de facturas
				$facmesd = $this->get_child('facmesd');		// creacion de modelo para detalle de facturas
				$facmesd->get(0);							// inicializacion de modelo para detalle de facturas
				$seriemd   = $this->get_child('serie');		// creacion de modelo para serie de documentos 
				$seriemd->get($serie);							// se inicializa para la serie actual de factura
				$cpr	 = $this->get_child('caja_pedido_referencia'); // creacion de modelo para caja_pedido_referencia	
				$cliente = $this->get_sibling('cliente');   // creacion de modelo para cliente
				$cliente->get($this->get_attr('id_cliente'));
				$producto = $this->get_child('producto');
				
				/* proceso de creacion de la cabecera de la factura */
				$facmesh->set_attr('caja', $this->get_attr('caja'));
				$facmesh->set_attr('codtra', '2A');
				$facmesh->set_attr('serie', $seriemd->get_attr('serie'));
				$facmesh->set_attr('nofac', $nofac);
				$facmesh->set_attr('fefac', date('Y-m-d'));
				$facmesh->set_attr('noped', $cpr->obtener_pedido($this->get_attr('caja'), $id_factura));
				$facmesh->set_attr('rd_cod', $this->get_attr('id_cliente'));
				
				$facmesh->set_attr('codven', '0');  // por el momento ya que aun no se migran empleados
				
				$facmesh->set_attr('venta_b', $this->get_attr('subtotal'));
				$facmesh->set_attr('descuento', $this->get_attr('descuento'));
				$facmesh->set_attr('venta_n', $this->get_attr('total'));
				$facmesh->set_attr('formapago', $this->get_attr('formapago'));
				$facmesh->set_attr('efectivo', $this->get_attr('efectivo'));
				$facmesh->set_attr('cheque', $this->get_attr('cheque'));
				$facmesh->set_attr('tarjeta', $this->get_attr('tarjeta'));
				$facmesh->set_attr('ta_numero', $this->get_attr('ta_numero'));
				$facmesh->set_attr('ta_casa', $this->get_attr('ta_casa'));
				$facmesh->set_attr('deposito', $this->get_attr('deposito'));
				
				// esta siendo facturado al contado
				$credito = '1';
				
				$facmesh->set_attr('credito', $credito); // si formapago == 2 poner credito a 1, caso contrario a cero
				$facmesh->set_attr('financiado', $this->get_attr('financiado'));
				$facmesh->set_attr('financiera', $this->get_attr('financiera'));
				$facmesh->set_attr('facturado', '0'); // deberia ser 1 pero todas estan en cero
				$facmesh->set_attr('anulado', '0');   // se acaba de crear, no puede estar anulada
				$facmesh->set_attr('despacho', '0');  // no se ocupa al parecer
				$facmesh->set_attr('prepedido', '0'); // no se ocupa al parecer
				$facmesh->set_attr('usuario', '0');   // no se ocupa al parecer
				
				$nomcli = '';
				$nomcli .= $cliente->get_attr('primer_nombre').' ';
				$nomcli .= $cliente->get_attr('segundo_nombre').' ';
				$nomcli .= $cliente->get_attr('primer_apellido').' ';
				$nomcli .= $cliente->get_attr('segundo_apellido');
				
				$facmesh->set_attr('nomcli', $nomcli);
				$facmesh->set_attr('dircli', $cliente->get_attr('direccion'));
				$facmesh->set_attr('nitcli', $cliente->get_attr('nit'));
				$facmesh->set_attr('venta', $this->get_attr('venta'));
				$facmesh->set_attr('servicio', $this->get_attr('servicio'));
				$facmesh->set_attr('monto', $this->get_attr('monto'));
				$facmesh->set_attr('iva', $this->get_attr('iva'));
				$facmesh->set_attr('total', $this->get_attr('total'));
				$facmesh->set_attr('facturadop', '0');
				$facmesh->set_attr('fefacturad', '');
				$facmesh->set_attr('descuentop', '0');
				
				$facmesh->save();
				$kardex = $this->get_child('kardex');
				
				/* Apertura de una cuenta por pagar */
				$ccdiah = $this->get_child('ccdiah');
				$ccdiah->get(0); 
				
				
				
				$ccdiah->set_attr('codrut',0); 
				$ccdiah->set_attr('codven',0); 
				$ccdiah->set_attr('codcli',$this->get_attr('id_cliente')); 
				$ccdiah->set_attr('tdoc',5); 
				$ccdiah->set_attr('ttra',4); 
				$ccdiah->set_attr('caja', $this->get_attr('caja')); 
				$ccdiah->set_attr('serie',$seriemd->get_attr('serie')); 
				$ccdiah->set_attr('nodoc',$nofac); 
				$ccdiah->set_attr('fedoc',date("Y-m-d")); 
				$ccdiah->set_attr('vence',sumar_dias_habiles(date("Y-m-d"), 21)); 
				$ccdiah->set_attr('femora',sumar_dias_habiles(date("Y-m-d"), 21)); 
				$ccdiah->set_attr('nmoras',0); 
				$ccdiah->set_attr('monto',$this->get_attr('total')); 
				$ccdiah->set_attr('cargos',$this->get_attr('total')); 
				$ccdiah->set_attr('abonos',0.0); 
				$ccdiah->set_attr('saldo',$this->get_attr('total')); 
				$ccdiah->set_attr('diascred',0); 
				$ccdiah->set_attr('concepto', $this->get_attr('concepto')); 
				$ccdiah->set_attr('operadopor',0); 
				$ccdiah->set_attr('feoperado',''); 
				
				$ccdiah->save();
				
				// obtencion de los detalles de las facturas
				$query = "SELECT * FROM detalle_factura WHERE id_factura=$id_factura";
				data_model()->executeQuery($query);
				$buffer_detail = array();
				while($result = data_model()->getResult()->fetch_assoc()){
					
					$buffer_detail[] = $result;
				}
				
				// proceso del detalle de la factura
				foreach($buffer_detail as $item){
					
					$info = array();
					$info['linea']  = $item['linea'];
					$info['estilo'] = $item['estilo'];
					$producto->get($info); 
					
					$facmesd->get(0);
					
					$facmesd->set_attr('cordia','0');  // por el momento parece que no se usa
					$facmesd->set_attr('caja',$this->get_attr('caja'));
					$facmesd->set_attr('codtra','2A');
					$facmesd->set_attr('serie',$seriemd->get_attr('serie'));
					$facmesd->set_attr('nofac',$nofac);
					$facmesd->set_attr('fefac',date('Y-m-d'));
					$facmesd->set_attr('hora',date('H:m:s'));
					$facmesd->set_attr('bodega', $item['bodega']);
					$facmesd->set_attr('linea', $item['linea']); 
					$facmesd->set_attr('cestilo', $item['estilo']);
					$facmesd->set_attr('estilo','0'); 
					$facmesd->set_attr('ccolor', $item['color']);
					$facmesd->set_attr('talla', $item['talla']);
					$facmesd->set_attr('precio', $item['precio']);
					$facmesd->set_attr('costo','0');      // por el momento no se toma en cuenta, consultar si es necesario
					$facmesd->set_attr('cantidad', $item['cantidad']);
					$facmesd->set_attr('pordes', $item['pordes']);
					$facmesd->set_attr('valdes','0');     // no se han implementado vales de descuento
					$facmesd->set_attr('importe', $item['importe']); 
					$facmesd->set_attr('propiedad', $item['propiedad']);
					$facmesd->set_attr('factura','0');    // al parece no se ocupa
					$facmesd->set_attr('caja_a','0');     // al parece no se ocupa
					$facmesd->set_attr('catalogo', $producto->get_attr('catalogo'));
					$facmesd->set_attr('entregado','0');  // al parece no se ocupa
					$facmesd->set_attr('item_inc','0');   // al parece no se ocupa
					$facmesd->set_attr('id_factura','0'); // al parece no se ocupa
					$facmesd->set_attr('id_prod','0');    // al parece no se ocupa
					$facmesd->set_attr('rd_cod','0');     // al parece no se ocupa
					$facmesd->set_attr('codpro','0');     // al parece no se ocupa  
					$facmesd->set_attr('pagina', $producto->get_attr('n_pagina'));
					$facmesd->set_attr('id_facd','0');    // al parece no se ocupa
					$facmesd->set_attr('devolucion','0'); // al parece no se ocupa
					$facmesd->set_attr('dsv_caja','0');   // al parece no se ocupa
					$facmesd->set_attr('dsv_num','0');    // al parece no se ocupa
					$facmesd->set_attr('descuentop','0'); // al parece no se ocupa
					
					$facmesd->save();
					$kardex->generar_salida($item['linea'], $item['estilo'], $item['color'], $item['talla'], $item['cantidad'], "Facturacion de producto al credito");
				}
				
				
                $query = "COMMIT";
                data_model()->executeQuery($query);
            
			} else {
                $ret['message'] = "No tiene suficiente credito";
            }
        } else {
            $ret['message'] = "No tiene credito";
        }
        echo json_encode($ret);
    }

    public function cf_credito($id_factura, $serie) {
        $ret = array();
        $query = "START TRANSACTION";
        data_model()->executeQuery($query);

        $this->get($id_factura);
        $id_cliente = $this->get_attr('id_cliente');
        $cliente = $this->get_sibling('cliente');
        $cliente->get($id_cliente);

        $credito = $cliente->get_attr('credito') + $cliente->get_attr('monto_extra');
        $usado = $cliente->get_attr('credito_usado');
        $disponible = $credito - $usado;
        $monto = $this->get_attr('total') - $this->get_attr('descuento');

        if ($cliente->get_attr('tcredito') == 1) {
            if ($monto <= $disponible) {
                $cliente->set_attr('credito_usado', $usado + $monto);
                $cliente->set_attr('monto_extra', 0.0);
                $cliente->set_attr('extra_credito', 0);
                $cliente->save();
                import('scripts.alias');
                $this->get($id_factura);
                $this->set_attr('fecha_vence', sumar_dias_habiles($this->get_attr('fecha'), $cliente->get_attr('dias_credito')));
                $this->save();
                $query = "UPDATE factura SET estado = 'FACTURADO', tipo='CF_CREDITO' WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
                $query = "UPDATE factura SET saldo = total WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
                $query = "UPDATE serie SET ultimo_utilizado = (ultimo_utilizado + 1) WHERE id = $serie ";
                data_model()->executeQuery($query);
                $query = "SELECT ultimo_utilizado FROM serie WHERE id = $serie";
                data_model()->executeQuery($query);
                $ret = data_model()->getResult()->fetch_assoc();
                $ultimo = $ret['ultimo_utilizado'];
                $query = "INSERT INTO id_creditos_fiscales VALUES( $ultimo,$serie,$id_factura )";
                data_model()->executeQuery($query);
                $query = "COMMIT";
                data_model()->executeQuery($query);
            } else {
                $ret['message'] = "No tiene suficiente credito";
            }
        } else {
            $ret['message'] = "No tiene credito";
        }
        echo json_encode($ret);
    }

    public function nota_remision($id_factura) {
        $ret = array();
        $this->get($id_factura);
        $id_cliente = $this->get_attr('id_cliente');
        $cliente = $this->get_sibling('cliente');
        $cliente->get($id_cliente);

        $credito = $cliente->get_attr('credito') + $cliente->get_attr('monto_extra');
        $usado = $cliente->get_attr('credito_usado');
        $disponible = $credito - $usado;
        $monto = $this->get_attr('total') - $this->get_attr('descuento');

        if ($cliente->get_attr('tcredito') == 1) {
            if ($monto <= $disponible) {
                $cliente->set_attr('credito_usado', $usado + $monto);
                $cliente->set_attr('monto_extra', 0.0);
                $cliente->set_attr('extra_credito', 0);
                $cliente->save();
                $query = "UPDATE factura SET estado = 'PENDIENTE', tipo='REMISION' WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
                $query = "UPDATE factura SET saldo = total WHERE id_factura=$id_factura";
                data_model()->executeQuery($query);
            } else {
                $ret['message'] = "No tiene suficiente credito";
            }
        } else {
            $ret['message'] = "No tiene credito";
        }

        echo json_encode($ret);
    }

    public function p_anular($serie, $nofac) {
        $query = "SELECT bodega,linea,cestilo as estilo,ccolor as color,talla,cantidad FROM facmesd WHERE nofac=$nofac AND serie='$serie'";
		data_model()->executeQuery($query);
        $items = array();
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $items[] = $res;
        }
        foreach ($items as $item) {
            $bodega   = $item['bodega'];
            $linea    = $item['linea'];
            $estilo   = $item['estilo'];
            $color    = $item['color'];
            $talla    = $item['talla'];
            $cantidad = $item['cantidad'];
            $id = $this->get_child('estado_bodega')->referencia($linea, $estilo, $color, $talla);
			$oBodega = $this->get_child('estado_bodega');
			if($id!=0){
				$oBodega->get($id);
				$oBodega->set_attr('stock', $oBodega->get_attr('stock') + $cantidad);
				$oBodega->save();
			}else{
				$oBodega->get(0);
				$data = array();
				$data['estilo'] = $item['estilo'];
				$data['linea']  = $item['linea'];
				$data['talla']  = $item['talla'];
				$data['color']  = $item['color'];
				$data['stock']  = $item['cantidad'];
				$data['bodega'] = 1;
				
				$oBodega->change_status($data);
				$oBodega->save();
			}
        }


        $query = "UPDATE facmesh SET anulado = 'VERDADERO' WHERE nofac=$nofac AND serie='$serie'";
        data_model()->executeQuery($query);
    }

    public function datos_anulacion($numero_factura, $numero_caja, $serie_factura) {
        $query = "select * from facmesh where nofac=$numero_factura AND caja = $numero_caja AND serie = '$serie_factura'";
        $ret = array();
		data_model()->executeQuery($query);
		$ret['notfound']   = false;
		if(data_model()->getNumRows()<=0) 
			$ret['notfound']   = true;
		else{
			$ret   = data_model()->getResult()->fetch_assoc();
			$noped = $ret['noped'];
			$caja  = $ret['caja'];
			$cpref = $this->get_child('caja_pedido_referencia');
			$refer = $cpref->obtener_referencia($caja, $noped);
			$ret['referencia'] = $refer;
        }
		echo json_encode($ret);
    }

    public function detalle_fac($nofac){
    	$query = "SELECT facmesd.linea AS linea, facmesd.cestilo as estilo, facmesd.ccolor as color, facmesd.talla as talla, facmesd.cantidad as cantidad, devueltos, facmesd.id as fid, nofac, facmesd.precio as precio FROM facmesd  LEFT JOIN devolucion ON 
              (facmesd.linea = devolucion.linea AND facmesd.cestilo = devolucion.estilo AND facmesd.ccolor = devolucion.color AND facmesd.talla = devolucion.talla) 
              WHERE nofac = $nofac";
        return data_model()->cacheQuery($query);
    }

    public function detalle_pedido($id_pedido) {
        $query = "SELECT *, facmesd.linea AS CONCAT(descripcion) FROM facmesd  LEFT JOIN devolucion ON 
              (facmesd.linea = devolucion.linea AND facmesd.cestilo = devolucion.estilo AND facmesd.ccolor = devolucion.color AND facmesd.talla = devolucion.talla) 
              WHERE nofac = $id_pedido";
        data_model()->executeQuery($query);
        $response = array();
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $response[] = $res;
        }
        echo json_encode($response);
    }

    public function facturas_cliente($id_cliente, $limitInf, $tamPag) {
        //$query = "SELECT * FROM id_facturas INNER JOIN factura ON id_pedido = id_factura WHERE id_cliente = $id_cliente AND fecha >= DATE_SUB(CURDATE(),INTERVAL 30 DAY) AND estado='FACTURADO' AND (tipo='CONTADO' OR tipo='CREDITO')";
        $query = "SELECT * FROm facmesh WHERE rd_cod = $id_cliente LIMIT $limitInf, $tamPag";
        return data_model()->cacheQuery($query);
        /*
          $response = array();
          while( $ret = data_model()->getResult( )->fetch_assoc() )
          {
          $response[] = $ret;
          }

          $len =  count($response);

          for($i = 0; $i < $len; $i++) {
          $id = $response[$i]['id_factura'];
          $response[$i]['detalle'] = array();
          $query = "SELECT * FROM detalle_factura WHERE id_factura = $id";
          data_model()->executeQuery( $query );
          while( $detail = data_model()->getResult( )->fetch_assoc() )
          {
          $response[$i]['detalle'][] = $detail;
          }
          }// */

        //echo json_encode($response);
    }

     public function cantidadFacturas($id_cliente) {
     	$query = "SELECT * FROm facmesh WHERE rd_cod = $id_cliente";
        data_model()->executeQuery($query);

        return data_model()->getNumRows();
     }

    public function datos_despacho($serie, $numero) {
        $query = "select id_pedido from id_facturas join serie on serie.id = id_facturas.serie WHERE serie.serie = '{$serie}' AND tipo='FC' AND id_facturas.id = $numero";
        data_model()->executeQuery($query);
        $dat = data_model()->getResult()->fetch_assoc();
        $pedido = $dat['id_pedido'];
        $query = "SELECT * FROM factura WHERE id_factura = $pedido";
        data_model()->executeQuery($query);
        $response = data_model()->getResult()->fetch_assoc();
        $response['detalle'] = array();
        $response['total_pares'] = 0;
        $response['nombre_cliente'] = "";
        $cliente = $response['id_cliente'];
        $query = "SELECT CONCAT(primer_nombre,' ',segundo_nombre,' ',primer_apellido,' ',segundo_apellido) AS nm FROM cliente WHERE codigo_afiliado = $cliente ";
        data_model()->executeQuery($query);
        $ld = data_model()->getResult()->fetch_assoc();
        $response['nombre_cliente'] = $ld['nm'];
        $query = "SELECT * FROM detalle_factura WHERE id_factura = $pedido";
        data_model()->executeQuery($query);
        while ($st = data_model()->getResult()->fetch_assoc()) {
            $response['detalle'][] = $st;
            $response['total_pares'] += $st['cantidad'];
        }

        echo json_encode($response);
    }

    public function reservar($id_factura) {
        $query = "UPDATE factura SET estado = 'RESERVADO', tipo='' WHERE id_factura=$id_factura";
        data_model()->executeQuery($query);
    }

    function existe_pedido($pedido, $caja) {
        $query = "SELECT referencia FROM caja_pedido_referencia WHERE pedido=$pedido AND caja=$caja";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $data = data_model()->getResult()->fetch_assoc();
            return array(true, $data['referencia']);
        } else {
            return array(false, 0);
        }
    }

    public function crear_pedido($caja) {
        $query = "UPDATE caja SET ultimo_pedido = (ultimo_pedido + 1) WHERE id=$caja";
        data_model()->executeQuery($query);
        $ped = "SELECT MAX(ultimo_pedido) AS pedido FROM caja WHERE id=$caja";
        data_model()->executeQuery($ped);
        $data = data_model()->getResult()->fetch_assoc();
        return $data['pedido'];
    }

    public function reservar_remision() {
        $query = "SELECT MAX(id_remision) as id FROM remision LOCK IN SHARE MODE";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        $num = 0;
        if (data_model()->getNumRows() == 0):
            $num = 1;
        else:
            $num = $data['id'] + 1;
        endif;
        $query = "INSERT INTO reserva_remision(id_remision_reservada) VALUES($num);";
        data_model()->executeQuery($query);
        $fecha = date('y-m-d');
        $query = "INSERT INTO remision(fecha) VALUES('{$fecha}')";
        data_model()->executeQuery($query);
        return $num;
    }

    public function DetalleFactura($NoFactura, $serie) {
        $query = "SELECT pordes as porcentaje, valdes as descuento, importe, cantidad, descripcion, precio FROM facmesd as f JOIN producto as p ON (f.linea = p.linea AND f.cestilo = p.estilo  ) WHERE nofac=$NoFactura AND serie='$serie'";
        return data_model()->cacheQuery($query);
    }
	
	public function datos_facturacion($noped, $caja){
		$query = "SELECT monto, iva, venta_b, descuento, total, nofac, serie FROM facmesh WHERE noped=$noped AND caja=$caja";
		data_model()->executeQuery($query);
		$result = data_model()->getResult()->fetch_assoc();
		
		return array($result['monto'], $result['iva'], $result['venta_b'], $result['descuento'], $result['total'], $result['nofac'], $result['serie']);
	}

    public function cerrarFactura($NoFactura) {
        $query = "UPDATE factura SET estado = 1 WHERE id_factura=$NoFactura";
        data_model()->executeQuery($query);
    }

    public function existe($linea, $estilo, $color, $talla, $id_factura) {
        $query = "SELECT * FROM detalle_factura WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_factura=$id_factura";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function actualizar($linea, $estilo, $color, $talla, $id_factura, $cantidad, $importe, $descuento) {
        $query = "UPDATE detalle_factura SET cantidad = (cantidad + $cantidad), importe = (importe + $importe), descuento=( descuento + $descuento) WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_factura=$id_factura";
        data_model()->executeQuery($query);
    }

}

?>