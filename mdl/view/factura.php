<?php

class facturaView 
{

    public function principal($usuario) 
    {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        page()->setTitle('Factura');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Página principal');
        template()->addTemplateBit('content', 'factura/principal.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function series($usuario) 
    {
        template()->buildFromTemplates(DEFAULT_LAYOUT);     
        page()->setTitle('Mantenimiento de Series');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Series de documentos');
        template()->addTemplateBit('content', 'facturacion/series.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cajas($usuario) 
    {
        template()->buildFromTemplates(DEFAULT_LAYOUT);     
        page()->setTitle('Mantenimiento de Cajas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Cajas');
        template()->addTemplateBit('content', 'facturacion/cajas.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function formulario_facturacion() 
    {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        page()->setTitle('Caja');
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Pedidos y facturación');
        page()->addEstigma("fecha", date("Y-m-d"));
        page()->addEstigma('username', Session::singleton()->getUser());
        template()->addTemplateBit('content', 'facturacion/formulario.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }
    
    // no validado
    public function productoEntrante(){
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        page()->setTitle('Producto en tránsito');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Producto en tránsito');
        template()->addTemplateBit('content', 'facturacion/transito.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }
    
    public function consultaCambio(){
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        page()->setTitle('Consulta rápida de cambios');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/facturacion/factura/principal');
        template()->addTemplateBit('content', 'facturacion/consulta_cambio.html');
        page()->addEstigma("TITULO", 'Consultar cambios');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function descuentos($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Descuentos');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Descuentos');
        template()->addTemplateBit('content', 'facturacion/descuento.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function detalle_fac($cache){
        template()->buildFromTemplates('facturacion/detalle_fac.html');
        page()->addEstigma("detalle", array('SQL', $cache));
        template()->parseOutput();
        return page()->getContent();
    }

    public function notas_remision($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Notas de remision');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Notas de remision');
        template()->addTemplateBit('content', 'facturacion/notas_remision.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function resumen($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Resumen');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/nuevo');
        page()->addEstigma("TITULO", 'Resumen');
        template()->addTemplateBit('content', 'facturacion/resumen.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function ver_fiscales($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Creditos fiscales');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/nuevo');
        page()->addEstigma("TITULO", 'Creditos fiscales');
        template()->addTemplateBit('content', 'facturacion/resumen_credito_fiscal.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function reportediarioventas($facturas, $nums, $sumatoria, $datos,$caja){
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('report/diarioventas.html');
        
        page()->addEstigma('detalle', array('SQL', $facturas));
        page()->addEstigma('sumatoria', array('SQL', $sumatoria));
        page()->addEstigma('datos', array('SQL', $datos));
        page()->addEstigma('usuario', Session::getUser());
        page()->addEstigma('caja', $caja);
        page()->addEstigma('timestamp', date("Y-m-d H:m:s"));
        $corr = 1;
        foreach ($nums as $num) {
            page()->addEstigma('c_'.$num, $corr++);
        }
        @template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('L', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('diarioventas.pdf');
    }

    public function ver_pendientes($usuario) {
		template()->buildFromTemplates(DEFAULT_LAYOUT);
		
		page()->setTitle('Pendientes');
		page()->addEstigma("username", $usuario);
		page()->addEstigma('back_url', '/facturacion/factura/nuevo');
		page()->addEstigma("TITULO", 'Pendientes');
		template()->addTemplateBit('content', 'facturacion/pendientes.html');
		template()->parseOutput();
		template()->parseExtras();
		print page()->getContent();
    }

    public function salidas($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Salida de mercaderia');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Salida de mercaderia');
        template()->addTemplateBit('content', 'facturacion/salida.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function reparaciones($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Reparaciones');
        page()->addEstigma("username", $usuario);
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Reparaciones');
        page()->addEstigma("fecha", date("Y-m-d"));
        template()->addTemplateBit('content', 'facturacion/reparaciones.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function anular($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Anular facturas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("time_stamp", date("Y-m-d H:i:s "));
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Anular factura');
        template()->addTemplateBit('content', 'facturacion/anular.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cambios($usuario) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Cambios');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("time_stamp", date("Y-m-d H:i:s "));
        page()->addEstigma("fecha", date("Y-m-d"));
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Cambio de productos');
        template()->addTemplateBit('content', 'facturacion/cambios.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cambios_detalle($usuario, $cache, $id_cambio, $nombre_cliente, $activo, $editable, $paginacion_str) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Cambios');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("activo", $activo);
        page()->addEstigma("paginacion_str", $paginacion_str);
        page()->addEstigma("editable", $editable);
        page()->addEstigma("time_stamp", date("Y-m-d H:i:s "));
        page()->addEstigma('back_url', '/facturacion/factura/cambios');
        page()->addEstigma("TITULO", 'Cambios');
        page()->addEstigma("cambio", $id_cambio);
        page()->addEstigma("nombre_cliente", $nombre_cliente);
        page()->addEstigma("general", array('SQL', $cache[0]));
        template()->addTemplateBit('content', 'facturacion/cambios_detalle.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function reparacion_detalle($usuario, $cache, $id_reparacion, $nombre_cliente, $activo) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        page()->setTitle('Reparacion');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("activo", $activo);
        page()->addEstigma("time_stamp", date("Y-m-d H:i:s "));
        page()->addEstigma('back_url', '/facturacion/factura/reparaciones');
        page()->addEstigma("TITULO", 'Reparacion');
        page()->addEstigma("cambio", $id_reparacion);
        page()->addEstigma("lineas", array('SQL', $cache[0]));
        page()->addEstigma("nombre_cliente", $nombre_cliente);
        template()->addTemplateBit('content', 'facturacion/reparacion_detalle.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }


    public function remision($numero_remision) {
        template()->buildFromTemplates('template_table.html');
        
        page()->setTitle('Caja');
        
        page()->addEstigma('back_url', '/facturacion/factura/principal');
        page()->addEstigma("TITULO", 'Notas de remisión');
        page()->addEstigma("numero_factura", $numero_remision);
        page()->addEstigma('username', Session::singleton()->getUser());
        template()->addTemplateBit('content', 'facturacion/remision.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function formulario_remision($numero_factura, $cache, $data) {
        template()->buildFromTemplates(DEFAULT_LAYOUT);
        
        $campos_str = "";
        page()->setTitle('Caja');
        page()->addEstigma('back_url', '/facturacion/factura/notas_remision');
        page()->addEstigma("TITULO", 'Cajero');
        page()->addEstigma("n_caja", $data['id']);
        page()->addEstigma("t_caja", $data['nombre']);
        page()->addEstigma("serie_factura", $data['serie_factura']);
        page()->addEstigma("serie_credito_fiscal", $data['serie_credito_fiscal']);
        page()->addEstigma("codigo_serie_factura", $data['codigo_factura']);
        page()->addEstigma("numero_factura", $numero_factura);
        page()->addEstigma("bodegas", array('SQL', $cache[0]));
        page()->addEstigma("lineas", array('SQL', $cache[1]));
        foreach ($cache[2] as $campos) {
            $val = "";
            if (strpos($campos['nombre_campo'], 'telef') > -1 || strpos($campos['nombre_campo'], 'celul') > -1)
                $val = " onkeyup=\"mascara(this,'-',patron_telefono,true)\" maxlength=\"9\" ";
            if (strpos($campos['nombre_campo'], 'dui') > -1)
                $val = " onkeyup=\"mascara(this,'-',patron_dui,true)\" maxlength=\"9\" ";
            if (strpos($campos['nombre_campo'], 'nit') > -1)
                $val = " onkeyup=\"mascara(this,'-',patron_nit,true)\" maxlength=\"14\" ";
            $campos_str.="{$campos['label']} <input type=\"text\" name=\"{$campos['nombre_campo']}\" id=\"{$campos['nombre_campo']}\" class=\"itemActUser\" $val /><br/>";
        }
        page()->addEstigma("campos_actualizables", $campos_str);
        page()->addEstigma("fecha", date("Y-m-d"));
        page()->addEstigma('username', Session::singleton()->getUser());
        template()->addTemplateBit('content', 'facturacion/formulario_remision.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function verLineas() {
        template()->buildFromTemplates('facturacion/verLineas.html');
        template()->parseExtras();
        print page()->getContent();
    }

    public function impFactura($cache, $NoFactura, $subtotal, $descuento, $total, $flete) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('facturacion/factura.html');
        page()->addEstigma('detalle', array('SQL', $cache[0]));
        page()->addEstigma('NoFactura', $NoFactura);
        page()->addEstigma('_subtotal', $subtotal);
        page()->addEstigma('_descuento', $descuento);
        page()->addEstigma('_flete', $flete);
        page()->addEstigma('_total', $total);
        template()->parseOutput();
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('exemple.pdf');
    }

}

?>