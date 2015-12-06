<?php
	class reportView{

		public function imprimir_ticket_cambio($id_cambio, $caja, $cliente, $fecha, $nombre_cliente, $detalle, $empresa, $nombre_empleado){
			require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
			template()->buildFromTemplates('report/ticketcambio.html');
			page()->setTitle('Ticket de cambio');
			page()->addEstigma('ncaja', $caja);
			page()->addEstigma('nocli', $cliente);
			page()->addEstigma('detalle', array('SQL', $detalle));
			page()->addEstigma('nca', $id_cambio);
			page()->addEstigma('nombre_empresa', $empresa->nombre_comercial);
			page()->addEstigma('direccion_empresa', $empresa->direccion);
			page()->addEstigma('telefono_empresa', $empresa->telefono);
			page()->addEstigma('nombre_cli', $nombre_cliente);
			page()->addEstigma('nombreca', $nombre_empleado);
			page()->addEstigma('fecha_aplicacion', $fecha);
			@template()->parseOutput();
        	@template()->parseExtras();
        	$html2pdf = new HTML2PDF('L', 'lette', 'es');
        	$html2pdf->WriteHTML(page()->getContent());
        	$html2pdf->Output('ticket.pdf');
		}

		public function imprimirOrdenCompra($detalle, $anexos, $system, $id_orden){
			require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
			template()->buildFromTemplates('report/orden_compra.html');
			page()->setTitle('Orden de compra');
			page()->addEstigma("detalle", array('SQL', $detalle));
        	page()->addEstigma("anexos", array('SQL', $anexos));
        	page()->addEstigma("norden", $id_orden);
        	page()->addEstigma("nombre_empresa", $system->nombre_comercial);
        	page()->addEstigma("direccion_empresa", $system->direccion);
			@template()->parseOutput();
        	@template()->parseExtras();
        	$html2pdf = new HTML2PDF('L', 'lette', 'es');
        	$html2pdf->WriteHTML(page()->getContent());
        	$html2pdf->Output('ticket.pdf');
		}
	
        public function ReporteDeVentas()
        {
            template()->buildFromTemplates('template_nofixed.html');
			template()->addTemplateBit('content', 'report/ReporteDeVentas.html');
			page()->setTitle('Reporte -  Ventas');
			page()->addEstigma('TITULO', 'Reporte - Ventas');
			page()->addEstigma('back_url', '/facturacion/factura/principal');
			page()->addEstigma('username', Session::singleton()->getUser());
			template()->parseExtras();
			template()->parseOutput();
			print page()->getContent();
        }
		
		public function reporte_Ventas($ag, $cache)
		{
			if($ag==1){
				template()->buildFromTemplates('report/ventas_fecha.html');
				page()->addEstigma('fechas', array('SQL', $cache['fechas']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==2){
				template()->buildFromTemplates('report/ventas_caja.html');
				page()->addEstigma('cajas', array('SQL', $cache['cajas']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==3){
				template()->buildFromTemplates('report/ventas_bodega.html');
				page()->addEstigma('bodegas', array('SQL', $cache['bodegas']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==4){
				template()->buildFromTemplates('report/ventas_linea.html');
				page()->addEstigma('lineas', array('SQL', $cache['lineas']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==5){
				template()->buildFromTemplates('report/ventas_proveedor.html');
				page()->addEstigma('proveedores', array('SQL', $cache['proveedores']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==6){
				template()->buildFromTemplates('report/ventas_territorio.html');
				page()->addEstigma('territorios', array('SQL', $cache['territorios']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==7){
				template()->buildFromTemplates('report/ventas_producto.html');
				page()->addEstigma('productos', array('SQL', $cache['productos']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}

			if($ag==8){
				template()->buildFromTemplates('report/ventas_catalogo.html');
				page()->addEstigma('catalogos', array('SQL', $cache['catalogos']));
				page()->addEstigma('totales', array('SQL', $cache['totales']));
			}
			
			page()->addEstigma('usuario', Session::singleton()->getUser());
			page()->addEstigma('fecha', date("Y-m-d"));
			page()->addEstigma('hora', date("h:i:s A"));
			
			template()->parseOutput();
			
			//print page()->getContent();
			
			$fp = fopen(APP_PATH."/temp/".Session::singleton()->getUser()."_saldos.html", "w");
			fputs($fp, page()->getContent());
			fclose($fp);
			$str = APP_PATH.'common\plugins\phantomjs\bin\phantomjs '.APP_PATH.'static\js\html2pdfv.js file:///'.APP_PATH.'temp\\'.Session::singleton()->getUser().'_saldos.html '.APP_PATH.'temp\\'.Session::singleton()->getUser().'_saldos.pdf';
			system($str); 
			$file = APP_PATH.'temp\\'.Session::singleton()->getUser().'_saldos.pdf';
			$filename = Session::singleton()->getUser().'_saldos.pdf';
	
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $filename . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($file));
			header('Accept-Ranges: bytes');
	
			@readfile($file);
		}
    }
?>