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
	}
?>