<?php

class inventarioView {

    private function load_settings() {
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();
        page()->addEstigma("periodo_fiscal", $pf);
        page()->addEstigma("periodo_actual", $pa);
        page()->addEstigma("fecha_sistema", date('d/m/Y'));
    }

    public function principal(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Módulo de control de inventario');
        template()->addTemplateBit('content', 'inventario/principal.html');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function construir_plantilla($usuario, $admin) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Control de inventario');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/destruirSesion/inventario');
        page()->addEstigma("TITULO", 'Inventario');
        page()->addEstigma("acceso", $admin);
        template()->addTemplateBit('content', 'inventario/inventario.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function recibir_oc($detalle, $id, $orden, $proveedor, $anexos){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Control de inventario');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/orden_compra');
        page()->addEstigma("TITULO", 'Inventario');
        page()->addEstigma("detalle", array('SQL', $detalle));
        page()->addEstigma("anexos", array('SQL', $anexos));
        page()->addEstigma("norden", $id);
        page()->addEstigma("orden_estado", $orden->estado);
        page()->addEstigma("orden_proveedor", $orden->proveedor);
        page()->addEstigma("orden_total", $orden->total);
        page()->addEstigma("orden_total_costo", $orden->total_costo);
        page()->addEstigma("codprov", $orden->proveedor);
        page()->addEstigma("proveedor_nombre", $proveedor->nombre);
        page()->addEstigma("proveedor_telefono", $proveedor->telefono);
        page()->addEstigma("proveedor_direccion", $proveedor->direccion);
        page()->addEstigma("proveedor_contacto", $proveedor->nombre_contacto);
        page()->addEstigma("proveedor_telefono_contacto", $proveedor->telefono_contacto);
        if(empty($orden->concepto)) $orden->concepto = "ND";
        page()->addEstigma("orden_concepto", $orden->concepto);
        template()->addTemplateBit('content', 'inventario/orden_compra/recepcion.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function operaciones_oc($operaciones){
        template()->buildFromTemplates('inventario/orden_compra/operaciones_oc.html');
        page()->addEstigma("operaciones", array('SQL', $operaciones));
        template()->parseOutput();
        return page()->getContent();
    }

    public function segmentacion(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Control de inventario');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/inventario/inventario/principal');
        page()->addEstigma("TITULO", 'Inventario');
        template()->addTemplateBit('content', 'inventario/menu/segmentacion.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function kits(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Creación de kits');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/inventario/inventario/principal');
        template()->addTemplateBit('content', 'inventario/kits.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }
    
    public function hoja_retaceo(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Creación de hojas de retaceo');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/inventario/inventario/principal');
        template()->addTemplateBit('content', 'inventario/hoja_retaceo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }
    
    public function editar_hoja_retaceo($id_hoja, $total, $detalle){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Editar hoja de retaceo');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("id_hoja", $id_hoja);
        page()->addEstigma("total", $total);
        page()->addEstigma("detalle", array('SQL', $detalle));
        page()->addEstigma("back_url", '/inventario/inventario/hoja_retaceo');
        template()->addTemplateBit('content', 'inventario/editar_hoja_retaceo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }
    
    public function ver_hoja_retaceo($id_hoja, $total, $detalle){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Ver hoja de retaceo');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("id_hoja", $id_hoja);
        page()->addEstigma("total", $total);
        page()->addEstigma("detalle", array('SQL', $detalle));
        page()->addEstigma("back_url", '/inventario/inventario/hoja_retaceo');
        template()->addTemplateBit('content', 'inventario/ver_hoja_retaceo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function movKardex(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Kardex');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/inventario/inventario/principal');
        template()->addTemplateBit('content', 'inventario/mov_kardex.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function noKardex(){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Kardex');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/inventario/inventario/principal');
        template()->addTemplateBit('content', 'inventario/no_kardex.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function productosSugeridos($lineas){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Productos sugeridos');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        page()->addEstigma("TITULO", 'Inventario');
        page()->addEstigma("linea", array('SQL', $lineas));
        template()->addTemplateBit('content', 'inventario/producto_sugerido.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function detalle_de_producto($estilo, $general, $n, $lineas, $cache_stock, $cache_transito, $cache_sugeridos){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Detalle de producto ');
        template()->addTemplateBit('content', 'inventario/detalle_producto.html');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/listar');
        page()->addEstigma("general", array('SQL', $general));
        page()->addEstigma("estilo", $estilo);
        page()->addEstigma("n", $n);
        

        foreach ($lineas as $linea) {
            page()->addEstigma("estado_bodega_".$linea, array('SQL', $cache_stock[$linea]));
        }

        foreach ($lineas as $linea) {
            page()->addEstigma("producto_transito_".$linea, array('SQL', $cache_transito[$linea]));
        }

        foreach ($lineas as $linea) {
            page()->addEstigma("producto_sugerido_".$linea, array('SQL', $cache_sugeridos[$linea]));
        }

        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();   
    }

    public function alertasDeStock($cache){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Alertas de stock');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/alerta');
        page()->addEstigma("TITULO", 'Inventario');
        page()->addEstigma("detalle", array('SQL', $cache));
        template()->addTemplateBit('content', 'inventario/alertas_de_stock.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function actualizarFoto($lineas){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Control de inventario');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        page()->addEstigma("TITULO", 'Inventario');
        page()->addEstigma("lineas", array('SQL', $lineas));
        template()->addTemplateBit('content', 'inventario/actualizar_foto.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function segmentacionLinea(){
        template()->buildFromTemplates('inventario/lineas.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionGrupo(){
        template()->buildFromTemplates('inventario/grupos.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionConcepto(){
        template()->buildFromTemplates('inventario/conceptos.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionTacon(){
        template()->buildFromTemplates('inventario/tacones.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionSuela(){
        template()->buildFromTemplates('inventario/suelas.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionMaterial(){
        template()->buildFromTemplates('inventario/materiales.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionColor(){
        template()->buildFromTemplates('inventario/colores.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionGenero(){
        template()->buildFromTemplates('inventario/generos.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function segmentacionMarca(){
        template()->buildFromTemplates('inventario/marcas.html');
        template()->parseOutput();
        return page()->getContent();   
    }

    public function cambio_de_linea($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Cambio de linea');
        page()->addEstigma("linea01", array('SQL', $cache[0]));
        page()->addEstigma("linea02", array('SQL', $cache[1]));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("username", $usuario);
        template()->addTemplateBit('content', 'inventario/cambio_de_linea.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cambio_de_grupo($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Cambio de grupo');
        page()->addEstigma("grupo01", array('SQL', $cache[0]));
        page()->addEstigma("grupo02", array('SQL', $cache[1]));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("username", $usuario);
        template()->addTemplateBit('content', 'inventario/cambio_de_grupo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function listadoTraslado($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Traslados');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/traslados');
        page()->addEstigma("TITULO", 'Traslados');
        template()->addTemplateBit('content', 'inventario/traslado_listado.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function listadoConsigna($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Consignas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/consigna');
        page()->addEstigma("TITULO", 'Consignas');
        template()->addTemplateBit('content', 'inventario/consigna_listado.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function promociones($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Promociones');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("lineas", array('SQL', $cache[0]));
        page()->addEstigma("fecha", date("Y/m/d"));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Promociones');
        template()->addTemplateBit('content', 'inventario/promociones.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function consigna($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('consigna');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Consignas');
        page()->addEstigma("bodega", array('SQL', $cache[0]));
        page()->addEstigma("proveedor1", array('SQL', $cache[1]));
        page()->addEstigma("proveedor2", array('SQL', $cache[2]));
        template()->addTemplateBit('content', 'inventario/consigna.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function imprimirTraslado($id, $usuario, $cache, $transaccion, $nt, $fecha, $concepto, $total_pares, $total_costo, $ntdoc) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('inventario/trasladoExport.html');
        page()->addEstigma('usuario', $usuario);
        page()->addEstigma('t_pares', $total_pares);
        page()->addEstigma('t_costo', $total_costo);
        page()->addEstigma('ntdoc', $ntdoc);
        page()->addEstigma('timestamp', date("d/m/Y h:m:s A"));
        page()->addEstigma('cod', $transaccion);
        page()->addEstigma('ncod', $nt);
        page()->addEstigma('documento', $id);
        page()->addEstigma('fecha', $fecha);
        page()->addEstigma('concepto', $concepto);
        page()->addEstigma("traslado", array('SQL', $cache[0]));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('L', 'letter', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('traslado.pdf');
    }

    public function reporteDocumentoPr($detalle, $id, $system) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('report/detalle_documento.html');
        
        page()->addEstigma('timestamp', date("d/m/Y h:m:s A"));
        page()->addEstigma('nodoc', $id);

        page()->addEstigma('nombre_empresa', $system->nombre_comercial);
        page()->addEstigma('direccion_empresa', $system->direccion);
        
        page()->addEstigma("detalle", array('SQL', $detalle));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('L', 'letter', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('traslado.pdf');
    }

    public function detalle_traslado($usuario, $id, $id_bodega, $nombre_bodega, $id_bodega_, $nombre_bodega_, $transaccion, $total_costo, $total_pares, $cache, $cliente, $consigna, $tolerancia, $proveedor) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Traslado No.' . $id);
        page()->addEstigma("username", $usuario);
        page()->addEstigma("idencabezado", $id);
        page()->addEstigma("idcliente", $cliente);
        page()->addEstigma("idb", $id_bodega);
        page()->addEstigma("nb", $nombre_bodega);
        page()->addEstigma("idb_", $id_bodega_);
        page()->addEstigma("nb_", $nombre_bodega_);
        page()->addEstigma("totalcosto", $total_costo);
        page()->addEstigma("consigna", $consigna);
        page()->addEstigma("tolerancia", $tolerancia);
        page()->addEstigma("totalpares", $total_pares);
        page()->addEstigma("proveedor", $proveedor);
        page()->addEstigma("tipoTransaccion", $transaccion);
        page()->addEstigma("lineas", array('SQL', $cache[0]));
        if ($consigna == 0)
            page()->addEstigma("back_url", '/'.MODULE.'/inventario/traslados');
        else
            page()->addEstigma("back_url", '/'.MODULE.'/inventario/consigna');
        page()->addEstigma("TITULO", 'Traslados');
        template()->addTemplateBit('content', 'inventario/traslado_detalle.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function traslados($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Traslados');
        template()->addTemplateBit('content', 'inventario/traslados.html');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Traslados');
        page()->addEstigma("proveedor1", array('SQL', $cache[0]));
        page()->addEstigma("proveedor2", array('SQL', $cache[1]));
        page()->addEstigma("bodega1", array('SQL', $cache[2]));
        page()->addEstigma("bodega2", array('SQL', $cache[3]));
        page()->addEstigma("transacciones", array('SQL', $cache[4]));
        page()->addEstigma("retaceos", array('SQL', $cache[5]));
        page()->addEstigma("fecha", date("Y-m-d"));
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cargar_tabla($tipoQuery, $cache) {
        $ret = array();
        switch ($tipoQuery) {
            case 1:
                template()->buildFromTemplates('inventario/comparativos/c1.html');
                break;
            case 2:
                template()->buildFromTemplates('inventario/comparativos/c2.html');
                break;
            case 3:
                template()->buildFromTemplates('inventario/comparativos/c3.html');
                break;
            case 4:
                template()->buildFromTemplates('inventario/comparativos/c4.html');
                break;
            case 5:
                template()->buildFromTemplates('inventario/comparativos/c5.html');
                break;
            default:
                template()->buildFromTemplates('inventario/comparativos/c1.html');
                break;
        }

        page()->addEstigma("inventario", array('SQL', $cache[0]));
        template()->parseOutput();
        $ret['html'] = page()->getContent();
        echo json_encode($ret);
    }

    public function p_transito($cache, $paginacion_str) {
        template()->buildFromTemplates('inventario/orden_compra/resumen.html');
        page()->addEstigma("listado", array('SQL', $cache[0]));
        page()->addEstigma("paginacion", $paginacion_str);
        template()->parseOutput();
        return page()->getContent();
    }

    public function edicion_oc($cache) {
        template()->buildFromTemplates('inventario/orden_compra/editar.html');
        page()->addEstigma("general", array('SQL', $cache[0]));
        page()->addEstigma("detalle", array('SQL', $cache[1]));
        page()->addEstigma("linea", array('SQL', $cache[2]));
        template()->parseOutput();
        return page()->getContent();
    }

    public function oc_data($cache) {
        $ret = array();
        template()->buildFromTemplates('inventario/orden_compra/oc_detalle.html');
        page()->addEstigma("general", array('SQL', $cache[0]));
        template()->parseOutput();
        $ret['html'] = page()->getContent();
        echo json_encode($ret);
    }

    public function orden_compra($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Orden de compra');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Orden de compra');
        page()->addEstigma("proveedores", array('SQL', $cache[0]));
        page()->addEstigma("colores", array('SQL', $cache[1]));
        page()->addEstigma("fecha", date("Y-m-d"));
        template()->addTemplateBit('content', 'inventario/orden_compra.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function ver_producto_en_transito($cache, $paginacion_str){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Producto en tránsito');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("paginacion", $paginacion_str);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/orden_compra');
        page()->addEstigma("detalle", array('SQL', $cache));
        template()->addTemplateBit('content', 'inventario/orden_compra/producto_en_transito.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }   

    public function imprimirReportesOC($id_orden, $traslado){
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Orden de compra');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Orden de compra');
        page()->addEstigma("id_orden", $id_orden);
        page()->addEstigma("traslado", $traslado);
        template()->addTemplateBit('content', 'inventario/orden_compra/orden_compra_report.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function comparativo_fisico_teorico($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Comparativo');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Comparativo');
        page()->addEstigma("bodegas", array('SQL', $cache[0]));
        template()->addTemplateBit('content', 'inventario/comparativo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function creacion_comparativo($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Comparativo');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Comparativo');
        template()->addTemplateBit('content', 'inventario/creacion_comparativo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function ofertas($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Ofertas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Ofertas');
        page()->addEstigma("ofertas", array('SQL', $cache[0]));
        page()->addEstigma("aOferta", array('SQL', $cache[1]));
        page()->addEstigma("genero", array('SQL', $cache[2]));
        page()->addEstigma("vOferta", array('SQL', $cache[3]));
        template()->addTemplateBit('content', 'inventario/ofertas.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function catalogos($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Catalogos');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/listar');
        page()->addEstigma("TITULO", 'Catálogos');
        template()->addTemplateBit('content', 'inventario/catalogo.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function resumenGeneralProducto($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Productos');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        page()->addEstigma("TITULO", 'Resumen general');
        template()->addTemplateBit('content', 'inventario/resumenGeneralProducto.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function resumenGeneralStock($usuario) {
        template()->buildFromTemplates('template_table.html');
        page()->setTitle('Stock');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/stock_documentos');
        page()->addEstigma("TITULO", 'Resumen general');
        template()->addTemplateBit('content', 'inventario/resumenGeneralStock.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function exportarEstadoBodegaImg($cache, $usuario) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('inventario/bodegaExportImg.html');
        page()->addEstigma('resumen', array('SQL', $cache[0]));
        page()->addEstigma('usuario', $usuario);
        page()->addEstigma('resource', APP_PATH);
        page()->addEstigma('fecha', date("d/m/y - h:m:s a"));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('exemple.pdf');
    }

    public function exportarResumenProducto($cache, $usuario) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('inventario/productoExport.html');
        page()->addEstigma('resumen', array('SQL', $cache[0]));
        page()->addEstigma('usuario', $usuario);
        page()->addEstigma('fecha', date("d/m/y - h:m:s a"));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('exemple.pdf');
    }

    public function exportarEstadoBodega($cache, $usuario) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('inventario/bodegaExport.html');
        page()->addEstigma('resumen', array('SQL', $cache[0]));
        page()->addEstigma('usuario', $usuario);
        page()->addEstigma('fecha', date("d/m/y - h:m:s a"));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('exemple.pdf');
    }

    public function exportarHistorial($cache, $usuario) {
        require_once(APP_PATH . 'common/plugins/sigma/demos/export_php/html2pdf/html2pdf.class.php');
        template()->buildFromTemplates('inventario/historialExport.html');
        page()->addEstigma('historial', array('SQL', $cache[0]));
        page()->addEstigma('usuario', $usuario);
        page()->addEstigma('fecha', date("d/m/y - h:m:s a"));
        template()->parseOutput();
        template()->parseExtras();
        $html2pdf = new HTML2PDF('P', 'A4', 'es');
        $html2pdf->WriteHTML(page()->getContent());
        $html2pdf->Output('exemple.pdf');
    }

    public function inventarioHistorial($user, $cache, $pag) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Historial');
        page()->addEstigma("username", $user);
        page()->addEstigma("paginacion", $pag);
        page()->addEstigma("modulo", "inventario");
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        page()->addEstigma("TITULO", 'Historial de eventos');
        template()->addTemplateBit('content', 'inventario/historial.html');
        page()->addEstigma('historial', array('SQL', $cache[0]));
        page()->addEstigma('1', 'Aplicado');
        page()->addEstigma('0', 'Pendiente');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function stockHistorial($user, $cache, $pag) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Historial');
        page()->addEstigma("username", $user);
        page()->addEstigma("paginacion", $pag);
        page()->addEstigma("modulo", "stock");
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/stock_documentos');
        page()->addEstigma("TITULO", 'Historial de eventos');
        template()->addTemplateBit('content', 'inventario/historial.html');
        page()->addEstigma('historial', array('SQL', $cache[0]));
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function passProveedor() {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Proveedores');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/listar');
        page()->addEstigma("TITULO", 'Acceso restringido');
        template()->addTemplateBit('content', 'inventario/verificar.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function passPrecio() {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Cambios');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        page()->addEstigma("TITULO", 'Acceso restringido');
        template()->addTemplateBit('content', 'inventario/verificarPrecio.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function nuevo_producto($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de productos');
        page()->addEstigma("TITULO", 'Nuevo producto');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("documentos", array('SQL', $cache[0]));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        template()->addTemplateBit('content', 'inventario/documentoProducto.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_bodegas($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de bodegas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("empleados", array('SQL', $cache[0]));
        page()->addEstigma("back_url", '/'.MODULE.'/modulo/listar');
        page()->addEstigma("TITULO", 'Bodegas');
        template()->addTemplateBit('content', 'inventario/bodegas.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_colores($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Regitro de colores');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Colores');
        template()->addTemplateBit('content', 'inventario/colores.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_lineas($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de lineas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Lineas');
        template()->addTemplateBit('content', 'inventario/lineas.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_marcas($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de marcas');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Marcas');
        template()->addTemplateBit('content', 'inventario/marcas.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_proveedores($usuario, $paises) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de proveedores');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("paises", array('SQL', $paises));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/destroyProv');
        page()->addEstigma("TITULO", 'Proveedores');
        template()->addTemplateBit('content', 'inventario/proveedores.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function mantenimiento_de_generos($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Mantenimiento de generos');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("TITULO", 'Generos');
        template()->addTemplateBit('content', 'inventario/generos.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function historial($cache) {
        template()->buildFromTemplates('inventario/historial.html');
        page()->addEstigma('historial', array('SQL', $cache[0]));
        template()->parseOutput();
        print page()->getContent();
    }

    public function actualizarStock($usuario, $doc) {
        template()->buildFromTemplates('template_table.html');
        page()->setTitle('Control de stock');
        page()->addEstigma('documento', $doc);
        template()->addTemplateBit('content', 'inventario/stock.html');
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("TITULO", 'Stock');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function administrar($doc, $usuario) {
        template()->buildFromTemplates('template.html');
        page()->setTitle('Administracion de documento - ' . $doc);
        page()->addEstigma("TITULO", 'Administracion');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/seleccion_documento?documento=' . $doc);
        template()->addTemplateBit('content', 'inventario/administracion.html');
        page()->addEstigma("documento", $doc);
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function vista_documento($usuario, $doc) {
        template()->buildFromTemplates('template.html');
        page()->setTitle('Control de Inventario - ' . $usuario);
        page()->addEstigma("TITULO", 'Opciones');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        template()->addTemplateBit('content', 'inventario/doc_inventario.html');
        template()->addTemplateBit('footer', 'footer.html');
        page()->addEstigma("usuario", $usuario);
        page()->addEstigma("documento", $doc);
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function abrir_documento($usuario, $cache) {
        template()->buildFromTemplates('inventario/doc_existentes.html');
        page()->addEstigma("usuario", $usuario);
        page()->addEstigma("documentos", array('SQL', $cache[0]));
        template()->parseOutput();
        print page()->getContent();
    }

    public function mantenimiento_de_productos($cache) {
        template()->buildFromTemplates('template_table.html');
        page()->setTitle('Productos');
        page()->addEstigma("menu", '');
        template()->addTemplateBit('content', 'inventario/productos.html');
        template()->addTemplateBit('footer', 'footer.html');
        page()->addEstigma('linea', array('SQL', $cache[0]));
        page()->addEstigma('marca', array('SQL', $cache[1]));
        page()->addEstigma('proveedor', array('SQL', $cache[2]));
        page()->addEstigma('color', array('SQL', $cache[3]));
        page()->addEstigma('genero', array('SQL', $cache[4]));
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function doc_mantenimiento_de_productos($cache, $doc, $usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->setTitle('Productos');
        template()->addTemplateBit('content', 'inventario/doc_productos.html');
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        //page()->addEstigma('linea', array('SQL', $cache[0]));
        //page()->addEstigma('marca', array('SQL', $cache[1]));
        //page()->addEstigma('proveedor', array('SQL', $cache[2]));
        page()->addEstigma('color', array('SQL', $cache[3]));
        //page()->addEstigma('genero', array('SQL', $cache[4]));
        page()->addEstigma('lineas_', array('SQL', $cache[5]));
        page()->addEstigma('marca_', array('SQL', $cache[6]));
        page()->addEstigma('proveedor_', array('SQL', $cache[7]));
        page()->addEstigma('genero_', array('SQL', $cache[9]));
        page()->addEstigma('tacon_', array('SQL', $cache[12]));
        page()->addEstigma('suela_', array('SQL', $cache[13]));
        page()->addEstigma('material_', array('SQL', $cache[14]));
        page()->addEstigma('concepto_', array('SQL', $cache[15]));
        page()->addEstigma('grupo_', array('SQL', $cache[16]));
        //page()->addEstigma('catalogo_', array('SQL', $cache[10]));
        page()->addEstigma('catalogo', array('SQL', $cache[11]));
        page()->addEstigma("documento", $doc);
        page()->addEstigma("fecha", date("Y-m-d"));
        page()->addEstigma("username", $usuario);
        page()->addEstigma("TITULO", 'Producto');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function nuevo_documento($usuario) {
        template()->buildFromTemplates('inventario/nuevo_documento.html');
        page()->addEstigma("usuario", $usuario);
        template()->parseOutput();
        print page()->getContent();
    }

    public function stock_documentos($user, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        page()->addEstigma("TITULO", 'Stock');
        page()->addEstigma("username", $user);
        page()->addEstigma("documentos", array('SQL', $cache[0]));
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/principal');
        template()->addTemplateBit('content', 'inventario/documentoStock.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cambiarPrecios($user, $cache) {
        template()->buildFromTemplates('template_table.html');
        page()->addEstigma("TITULO", 'Cambios');
        page()->setTitle('Cambios');
        page()->addEstigma("username", $user);
        page()->addEstigma("back_url", '/'.MODULE.'/inventario/nuevo_producto');
        //page()->addEstigma("l_proveedor", array('SQL', $cache[0]));
        page()->addEstigma("l_catalogos", array('SQL', $cache[0]));
        template()->addTemplateBit('content', 'inventario/cambiarPrecios.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

}

?>