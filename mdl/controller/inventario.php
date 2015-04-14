<?php

import('mdl.view.inventario');
import('mdl.model.inventario');

class inventarioController extends controller {
###### INDEX ######

    public function principal() {
        $this->validar();
        $this->view->principal();
    }

    private function validar() {
        if (!Session::ValidateSession())
            HttpHandler::redirect(DEFAULT_DIR);
        //if (!isset($_SESSION['inventario']))
        //    HttpHandler::redirect('/'.MODULE.'/modulo/listar');
    }

###### (FIN INDEX) ######
###### VISTAS MANTENIMIENTOS ######

    public function bodegas() {
        $this->validar();
        
        $usuario = Session::getUser();
        
        $cache = array(
            $this->model->get_child('empleado')->get_list()
        );

        $this->view->mantenimiento_de_bodegas($usuario, $cache);
    }
    
    public function hoja_retaceo(){
        
        $this->view->hoja_retaceo();   
    }
    
    public function editar_hoja_retaceo(){
        if(isset($_GET['cod'])){
            $hoja_retaceo = $this->model->get_child('hoja_retaceo');
            if($hoja_retaceo->exists($_GET['cod'])){
                $id_hoja = $_GET['cod'];
                $hoja_retaceo->get($id_hoja);
                if(!$hoja_retaceo->confirmada){
                    $detalle = $this->model->get_child('detalle_retaceo')->filter('id_hoja_retaceo', $id_hoja);
                    $this->view->editar_hoja_retaceo($id_hoja, $hoja_retaceo->total_gastos, $detalle);
                }else{
                    HttpHandler::redirect('/inventario/inventario/ver_hoja_retaceo?cod='.$id_hoja);    
                }
            }else{
                HttpHandler::redirect('/inventario/inventario/hoja_retaceo');    
            }
        }else{
            HttpHandler::redirect('/inventario/inventario/hoja_retaceo');   
        }
    }
    
    public function ver_hoja_retaceo(){
        if(isset($_GET['cod'])){
            $hoja_retaceo = $this->model->get_child('hoja_retaceo');
            if($hoja_retaceo->exists($_GET['cod'])){
                $id_hoja = $_GET['cod'];
                $hoja_retaceo->get($id_hoja);
                if($hoja_retaceo->confirmada){
                    $detalle = $this->model->get_child('detalle_retaceo')->filter('id_hoja_retaceo', $id_hoja);
                    $this->view->ver_hoja_retaceo($id_hoja, $hoja_retaceo->total_gastos, $detalle);
                }else{
                    HttpHandler::redirect('/inventario/inventario/editar_hoja_retaceo?cod='.$id_hoja);    
                }
            }else{
                HttpHandler::redirect('/inventario/inventario/hoja_retaceo');    
            }
        }else{
            HttpHandler::redirect('/inventario/inventario/hoja_retaceo');   
        }
    }
    
    public function guardar_detalle_retaceo(){
        if(isset($_POST)){
            $id_hoja = $_POST['id_hoja_retaceo'];
            $hoja_retaceo = $this->model->get_child('hoja_retaceo');
            if($hoja_retaceo->exists($id_hoja)){
                $data = $_POST;
                if($data['gasto']<0) $data['gasto'] *= -1 ; 
                $detalle_retaceo = $this->model->get_child('detalle_retaceo');
                $detalle_retaceo->get(0);
                $detalle_retaceo->change_status($data);
                $detalle_retaceo->save();
                $hoja_retaceo->get($id_hoja);
                $hoja_retaceo->total_gastos += $data['gasto'];
                $hoja_retaceo->save();
                HttpHandler::redirect('/inventario/inventario/editar_hoja_retaceo?cod='.$id_hoja);    
            }else{
                HttpHandler::redirect('/inventario/inventario/hoja_retaceo');    
            }
        }else{
            HttpHandler::redirect('/inventario/inventario/hoja_retaceo');
        }
    }
    
    public function confirmar_hoja_retaceo(){
        if(isset($_GET)){
            $id_hoja = $_GET['cod'];
            $hoja_retaceo = $this->model->get_child('hoja_retaceo');
            if($hoja_retaceo->exists($id_hoja)){
                $hoja_retaceo->get($id_hoja);
                $hoja_retaceo->confirmada = 1;
                $hoja_retaceo->save();
                HttpHandler::redirect('/inventario/inventario/hoja_retaceo?confirmacion=ok');    
            }else{
                HttpHandler::redirect('/inventario/inventario/hoja_retaceo');    
            }
        }else{
            HttpHandler::redirect('/inventario/inventario/hoja_retaceo');
        }
    }
    
    public function guardar_hoja_retaceo(){
        if(isset($_POST)){
            $hoja_retaceo = $this->model->get_child('hoja_retaceo');
            $hoja_retaceo->get($_POST['cod']);
            $hoja_retaceo->change_status($_POST);
            $hoja_retaceo->save();
            
            HttpHandler::redirect('/inventario/inventario/hoja_retaceo?estado=ok');
        }
    }

    public function kits(){

        $this->view->kits();
    }
    
    public function validar_kit(){
        if(isset($_POST) && !empty($_POST['estilo'])){
            $estilo  = "KT".$_POST['estilo'];
            $product = $this->model->get_child('producto');
            $product->setVirtualId('estilo');
            echo json_encode(array("existe"=>$product->exists($estilo)));
        }
    }
    
    public function guardar_kit(){
        $estilo      = $_POST['estilo'];
        $linea       = "0";
        $descripcion = $_POST['descripcion'];
        $precio      = $_POST['precio'];
        
        $producto = $this->model->get_child('producto');
        
        $producto->get(array("estilo"=>0, "linea"=>0));
        $producto->estilo = $estilo;
        $producto->linea = $linea;
        $producto->descripcion = $descripcion;
        $producto->fecha_ingreso = date("Y-m-d");
        $producto->force_save();
        
        $color_producto = $this->model->get_child("color_producto");
        $color_producto->get(0);
        $color_producto->color_estilo_producto = $estilo;
        $color_producto->linea = $linea;
        $color_producto->color = 1;
        $color_producto->force_save();
        
        $talla_producto = $this->model->get_child("talla_producto");
        $talla_producto->get(0);
        $talla_producto->talla_estilo_producto = $estilo;
        $talla_producto->linea = $linea;
        $talla_producto->color = 1;
        $talla_producto->talla = 1;
        $talla_producto->force_save();
        
        $control_precio = $this->model->get_child("control_precio");
        $control_precio->get(0);
        $control_precio->control_estilo = $estilo;
        $control_precio->linea  = $linea;
        $control_precio->talla  = 1;
        $control_precio->color  = 1;
        $control_precio->precio = $precio;
        $control_precio->costo  = 0;
        $control_precio->force_save();
        
        $estado_bodega = $this->model->get_child('estado_bodega');
        $estado_bodega->get(0);
        $estado_bodega->estilo  = $estilo;
        $estado_bodega->linea = $linea;
        $estado_bodega->talla = 1;
        $estado_bodega->color = 1;
        $estado_bodega->stock = 0;
        $estado_bodega->bodega = 1;
        $estado_bodega->force_save();
        
        echo json_encode(array("success"=>true));
    }
    
    public function guardar_elemento_kit(){
        
        $this->model->guardar_elemento_kit();
        
        echo json_encode(array("msg"=>""));
    }
    
    public function eliminar_item_kit(){
        if(isset($_POST)){
            $elemento_kit = $this->model->get_child('elemento_kit');
            $elemento_kit->delete($_POST['id']);
        }
        
        echo json_encode(array("msg"=>""));
    }

    public function json_producto(){
        $fields          = (isset($_POST['fields']) && !empty($_POST['fields'])) ? $_POST['fields']: null;
        $conditions      = (isset($_POST['conditions']) && !empty($_POST['conditions']))? $_POST['conditions'] : null;
        $group_by_fields = (isset($_POST['groub_by_fields']) && !empty($_POST['groub_by_fields'])) ? $_POST['groub_by_fields']: null;
        $order_by_field  = (isset($_POST['order_by_field']) && !empty($_POST['order_by_field'])) ? $_POST['order_by_field']: null;
        $order_type      = (isset($_POST['order_type']) && !empty($_POST['order_type'])) ? $_POST['order_type']: "";
        $limitInf        = (isset($_POST['limitInf']) && !empty($_POST['limitInf'])) ? $_POST['limitInf']: null;
        $tamPag          = (isset($_POST['tamPag']) && !empty($_POST['tamPag'])) ? $_POST['tamPag']: null;

        $datos = $this->model->DATOS_PRODUCTO(
                $fields, 
                $conditions,
                $group_by_fields,
                $order_by_field,
                $order_type,
                $limitInf,
                $tamPag,
                "data"
            );

        echo json_encode($datos);
    }

    public function json_productos_sugeridos(){
        
        $linea  = (isset($_POST['linea']) && !empty($_POST['linea'])) ? $_POST['linea']: null;
        $estilo = (isset($_POST['estilo']) && !empty($_POST['estilo'])) ? $_POST['estilo']: null;
        $datos = $this->model->get_child('productos_sugeridos')->obtener_productos_sugeridos($linea, $estilo, "data");

        echo json_encode($datos);
    }

    public function agregarProductoSugerido(){
        $linea  = (isset($_POST['linea']) && !empty($_POST['linea'])) ? $_POST['linea']: null;
        $estilo = (isset($_POST['estilo']) && !empty($_POST['estilo'])) ? $_POST['estilo']: null;
        $estilo_sugerencia = (isset($_POST['estilo_sugerencia']) && !empty($_POST['estilo_sugerencia'])) ? $_POST['estilo_sugerencia']: null;
        $prod   = $this->model->get_child('productos_sugeridos'); 
        $datos  = $prod->obtener_productos_sugeridos($linea, $estilo, "data");

        if(count($datos)==0){
            $prod->get(0);
            $prod->linea  = $linea;
            $prod->estilo = $estilo;
            $prod->estilo_sugerencia = $estilo_sugerencia;
            $prod->save();
            $prod->get(0);
            $prod->linea  = $linea;
            $prod->estilo = $estilo_sugerencia;
            $prod->estilo_sugerencia = $estilo;
            $prod->save();
            echo json_encode(array("msg"=>"Peticion procesada con exito!"));
        }else{
            echo json_encode(array("msg"=>"Los datos ya han sido asignados"));
        }           
    }

    public function segmentacion(){
        
        $this->view->segmentacion();
    }

    public function segmentacionLinea(){

        echo json_encode(array('html'=>$this->view->segmentacionLinea()));
    }

    public function segmentacionGrupo(){

        echo json_encode(array('html'=>$this->view->segmentacionGrupo()));
    }

    public function segmentacionTacon(){

        echo json_encode(array('html'=>$this->view->segmentacionTacon()));
    }

    public function segmentacionConcepto(){

        echo json_encode(array('html'=>$this->view->segmentacionConcepto()));
    }

    public function segmentacionSuela(){

        echo json_encode(array('html'=>$this->view->segmentacionSuela()));
    }

    public function segmentacionMaterial(){

        echo json_encode(array('html'=>$this->view->segmentacionMaterial()));
    }


    public function segmentacionColor(){

        echo json_encode(array('html'=>$this->view->segmentacionColor()));
    }

    public function segmentacionGenero(){

        echo json_encode(array('html'=>$this->view->segmentacionGenero()));
    }

    public function segmentacionMarca(){

        echo json_encode(array('html'=>$this->view->segmentacionMarca()));
    }

    public function detalle_de_producto(){
        if(isset($_GET['estilo']) && !empty($_GET['estilo']) ){
            $general = $this->model->general_producto($_GET['estilo']);
            $n = $this->model->c_general_producto($_GET['estilo']);
            $lineas = $this->model->l_general_producto($_GET['estilo']);
            $cache_stock     = array();
            $cache_transito  = array();
            $cache_sugeridos = array();
            
            foreach ($lineas as $linea) {
                $cache_stock[$linea] = $this->model->detalle_producto_stock($linea, $_GET['estilo']);
            }

            foreach ($lineas as $linea) {
                $cache_transito[$linea] = $this->model->detalle_producto_transito($linea, $_GET['estilo']);
            }

            foreach ($lineas as $linea) {
                $cache_sugeridos[$linea] = $this->model->get_child('productos_sugeridos')->obtener_productos_sugeridos($linea, $_GET['estilo']);
            }

            $this->view->detalle_de_producto($_GET['estilo'], $general, $n, $lineas, $cache_stock, $cache_transito, $cache_sugeridos);
        }
    }

    public function alertasDeStock(){
        $cache = $this->model->obtenerLineasYGrupos();
        $this->view->alertasDeStock($cache);
    }

    public function actualizarMinimo(){
        $linea = $_POST['linea'];
        $grupo = $_POST['grupo'];
        $minimo_stock = $_POST['minimo_stock'];
        $this->model->actualizarMinimo($linea, $grupo, $minimo_stock);
    }

    public function actualizarFoto(){
        $lineas = $this->model->get_child('linea')->get_list('', '', array('nombre'));
        $this->view->actualizarFoto($lineas);
    }

    public function productosSugeridos(){
        $lineas = $this->model->get_child('linea')->get_list('', '', array('nombre'));
        $this->view->productosSugeridos($lineas);   
    }

    public function obtenerEstilos(){
        $this->model->obtenerEstilos($_POST['linea']);
    }

    public function actualizarFotoProducto(){
        upload_image(APP_PATH.'static/img/productos', 'archivo', $_POST['linea'].'_'.$_POST['estilo'].".jpg");
        httpHandler::redirect('/'.MODULE.'/inventario/actualizarFoto?linea='.$_POST['linea'].'&estilo='.$_POST['estilo']);
    }

    public function promociones() {
        $this->validar();
        $usuario = Session::getUser();
        $cache = array(
            $this->model->get_child('linea')->get_list('', '', array('nombre'))
        );

        $this->view->promociones($usuario, $cache);
    }

    public function colores() {
        $this->validar();
        $usuario = Session::getUser();
        $this->view->mantenimiento_de_colores($usuario);
    }

    public function lineas() {
        $this->validar();
        $usuario = Session::getUser();
        $this->view->mantenimiento_de_lineas($usuario);
    }

    public function generos() {
        $this->validar();
        $usuario = Session::getUser();
        $this->view->mantenimiento_de_generos($usuario);
    }

    public function marcas() {
        $this->validar();
        $usuario = Session::getUser();
        $this->view->mantenimiento_de_marcas($usuario);
    }

    public function destroyProv() {
        if (isset($_SESSION['p'])):
            unset($_SESSION['p']);
        endif;
        HttpHandler::redirect('/'.MODULE.'/modulo/listar');
    }

    public function proveedores() {
        $this->validar();
        $usuario = Session::getUser();
        $paises = $this->model->get_child('paises')->get_list();
        $this->view->mantenimiento_de_proveedores($usuario, $paises);
       
    }

    public function obtenerBodegas(){

        $bodegas = array();
        $bds     = $this->model->get_child('bodega');
        $bodegas = $bds->get_list_array();
        echo json_encode($bodegas);
    }

    public function movKardex(){
        if(!isInstalled("kardex")){
            $this->view->noKardex();
        }else{
            $this->view->movKardex();
        }
    }

    public function requestColors(){
        $linea  = $_POST['linea'];
        $estilo = $_POST['estilo'];

        $this->model->requestColors($linea, $estilo);
    }

    public function requestRun(){
        $linea  = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color  = $_POST['color'];

        $this->model->requestRun($linea, $estilo, $color);
    }

    // acceso solo admin
    public function cambio_de_linea() {
        $this->validar();
        $usuario = Session::singleton()->getUser();
        $acceso = Session::singleton()->getLevel();
        $data = array(
            $this->model->get_child('linea')->get_list('', '', array('nombre'))
            , $this->model->get_child('linea')->get_list('', '', array('nombre'))
        );
        if ($acceso) {
            $this->view->cambio_de_linea($usuario, $data);
        } else {
            HttpHandler::redirect('/'.MODULE.'/inventario/principal');
        }
    }

    // acceso solo admin
    public function cambio_de_grupo() {
        $this->validar();
        $usuario = Session::singleton()->getUser();
        $acceso  = Session::singleton()->getLevel();
        $data    = array(
            $this->model->get_child('grupo')->get_list('', '', array('nombre'))
            , $this->model->get_child('grupo')->get_list('', '', array('nombre'))
        );
        if ($acceso) {
            $this->view->cambio_de_grupo($usuario, $data);
        } else {
            HttpHandler::redirect('/'.MODULE.'/inventario/principal');
        }
    }

    public function s_cambio_linea() {
        $this->model->s_cambio_linea($_POST, $json_response = true);
    }

    public function s_cambio_grupo() {
        $this->model->s_cambio_grupo($_POST, $json_response = true);
    }

    ###### (FIN VISTAS MANTENIMIENTOS) ######
    ###### ACCIONES MANTENIMIENTOS ##########

    public function guardar_color() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $color_model = $this->model->get_child('color');
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $color_model->ultimo_en_blanco();
            else
                $id = 0;
            $color_model->not_null($not_null);
            $color_model->get($id);
            $color_model->change_status($_POST);
            $color_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=color');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=color');
        endif;
    }

    public function verificarExistencia() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $retArray = array();
        $retArray['existe'] = false;
        $query = "SELECT * FROM estado_bodega WHERE linea = $linea AND estilo=$estilo";
        data_model()->executeQuery($query);
        $rows = data_model()->getNumRows();
        if ($rows > 0)
            $retArray['existe'] = true;
        echo json_encode($retArray);
    }

    public function obtenerBodega() {
        $id_bodega = addslashes($_POST['idBodega']);
        $bodega = $this->model->get_child('bodega');
        $bodega->get($id_bodega);
        $ret_array = array();
        $fields = $bodega->get_fields();

        foreach ($fields as $field) {
            $ret_array[$field] = $bodega->get_attr($field);
        }

        echo json_encode($ret_array);
    }

    public function comparativo_fisico_teorico() {
        $this->validar();
        $cache    = array();
        $cache[0] = $this->model->get_child('bodega')->get_list();
        $activo   = $this->model->comparativo_activo();

        if (!$activo) {
            if (isset($_GET['new'])) {
                $action = true;
                $this->model->crear_comparativo();
            }
        }

        if ($activo){
            $this->model->crear_comparativo();
            $this->view->comparativo_fisico_teorico(Session::singleton()->getUser(), $cache);
        }
        else{
            $this->view->creacion_comparativo(Session::singleton()->getUser());
        }
    }

    public function actualizar_fisico() {
        $linea = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color = $_POST['color'];
        $talla = $_POST['talla'];
        $cantidad = $_POST['cantidad'];

        $this->model->actualizar_fisico($linea, $estilo, $color, $talla, $cantidad);
    }

    public function hacer_comparativo() {
        $this->validar();
        $tipoQuery  = $_POST['tipoQuery'];
        $bodega     = $_POST['bodega'];
        $Li         = $_POST["lineaInf"];
        $Ls         = $_POST["lineaSup"];
        $Pi         = $_POST["provInf"];
        $Ps         = $_POST["provSup"];

        $cache = array();
        $cache[0] = $this->model->consultar_inventario($tipoQuery, $bodega, $Li, $Ls, $Pi, $Ps);

        $this->view->cargar_tabla($tipoQuery, $cache);
    }

    public function orden_compra() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('proveedor')->get_list('','',array('nombre'));
        $cache[1] = $this->model->get_child('color')->get_list('','',array('nombre'));
        $this->view->orden_compra(Session::singleton()->getUser(), $cache);
    }

    public function crear_orden() {
        $ret = array();
        $oc = $this->model->get_child('orden_compra');
        $oc->get(0);
        $oc->change_status($_POST);
        $oc->save();
        $query = "SELECT MAX(id) AS id FROM orden_compra";
        data_model()->executeQuery($query);
        $ret = data_model()->getResult()->fetch_assoc();
        echo json_encode($ret);
    }

    public function guardar_producto() {
        if (isset($_POST) && !empty($_POST)):

            // variables que no pueden ser nulas
            $not_null = array(
                'estilo'
                , 'linea'
                , 'codigo_origen'
                , 'descripcion'
                , 'proveedor'
                , 'catalogo'
                , 'n_pagina'
                , 'genero'
                , 'marca'
                , 'propiedad'
                , 'fecha_ingreso'
                , 'numero_documento'
            );


            $dataTCosto = array();
            $dataDocPro = $_POST;
            $TCostoMod = $this->model->get_child('tarjeta_costo');
            $docProMod = $this->model->get_child('documento_producto');

            /**
             * Creacion de la tarjeta de costos
             */
            $TCostoMod->get(0);
            $TCostoMod->CESTILO = $_POST['estilo'];
            $TCostoMod->LINEA = $_POST['linea'];
            $TCostoMod->CODORIGEN = $_POST['codigo_origen'];
            $TCostoMod->CCOLOR = $_POST[''];
            $TCostoMod->DESCRIP = $_POST['descripcion'];
            $TCostoMod->PROVEEDOR = $_POST['proveedor'];
            $TCostoMod->CATALOGO = $_POST['catalogo'];
            $TCostoMod->PAGINA = $_POST['n_pagina'];
            $TCostoMod->GENERO = $_POST['genero'];
            $TCostoMod->MARCA = $_POST['marca'];
            $TCostoMod->PROPIEDAD = $_POST['propiedad'];
            $TCostoMod->OBSERVACIO = $_POST['observacion'];
            $TCostoMod->FEINGRESA = $_POST['fecha_ingreso'];
            $TCostoMod->NODOC = $_POST['numero_documento'];
            $TCostoMod->RUN1 = 0;
            $TCostoMod->RUN2 = 0;
            $TCostoMod->save();

        //$prod = $this->model->get_child('documento_producto');
        //$prod->not_null($not_null);
        //$prod->get(0);
        //$prod->change_status($data);
        //$prod->save();
        //HttpHandler::redirect('/'.MODULE.'/inventario/doc_productos?documento='.$_POST['numero_documento']);
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/doc_productos?documento=' . $_POST['numero_documento']);
        endif;
    }

    public function guardar_proveedor() {
        if (isset($_POST) && !empty($_POST)):
            $model = $this->model->get_child('proveedor'); 
            $model->get(0);
            $model->change_status($_POST);
            $model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/proveedores');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/proveedores');
        endif;
    }

    public function guardar_linea() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $linea_model = $this->model->get_child('linea');
            $linea_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $linea_model->ultimo_en_blanco();
            else
                $id = 0;
            $linea_model->get($id);
            $linea_model->change_status($_POST);
            $linea_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=linea');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=linea');
        endif;
    }

    public function guardar_material() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $material_model = $this->model->get_child('material');
            $material_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $material_model->ultimo_en_blanco();
            else
                $id = 0;
            $material_model->get($id);
            $material_model->change_status($_POST);
            $material_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=material');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=material');
        endif;
    }

    public function guardar_grupo() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $grupo_model = $this->model->get_child('grupo');
            $grupo_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $grupo_model->ultimo_en_blanco();
            else
                $id = 0;
            $grupo_model->get($id);
            $grupo_model->change_status($_POST);
            $grupo_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=grupo');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=grupo');
        endif;
    }

    public function guardar_concepto() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $concepto_model = $this->model->get_child('concepto');
            $concepto_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $concepto_model->ultimo_en_blanco();
            else
                $id = 0;
            $concepto_model->get($id);
            $concepto_model->change_status($_POST);
            $concepto_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=concepto');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=concepto');
        endif;
    }

    public function guardar_suela() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $suela_model = $this->model->get_child('suela');
            $suela_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $suela_model->ultimo_en_blanco();
            else
                $id = 0;
            $suela_model->get($id);
            $suela_model->change_status($_POST);
            $suela_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=suela');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=suela');
        endif;
    }

    public function guardar_tacon() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $tacon_model = $this->model->get_child('tacon');
            $tacon_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $tacon_model->ultimo_en_blanco();
            else
                $id = 0;
            $tacon_model->get($id);
            $tacon_model->change_status($_POST);
            $tacon_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=tacon');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=tacon');
        endif;
    }

    public function performPSearch() {
        $ret = array();
        if (isset($_POST) && !empty($_POST)):
            $ret['status'] = 200;
            $ret['estilo'] = $_POST['_term'];
            $term = $_POST['_term'];
            $ret['data'] = $this->model->performPSearch($term);
        else:
            $ret['status'] = 403;
        endif;
        echo json_encode($ret);
    }

    public function performCSearch() {
        $ret = array();
        if (isset($_POST) && !empty($_POST)):
            $ret['status'] = 200;
            $term = $_POST['_term'];
            $ret['data'] = $this->model->performCSearch($term);
        else:
            $ret['status'] = 403;
        endif;
        echo json_encode($ret);
    }

    public function guardar_marca() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $marca_model = $this->model->get_child('marca');
            $marca_model->not_null($not_null);
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;
            $data = $_POST;
            if ($rellenar)
                $id = $marca_model->ultimo_en_blanco();
            else
                $id = 0;
            $marca_model->get($id);
            $marca_model->change_status($_POST);
            $marca_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=marca');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=marca');
        endif;
    }

    public function guardar_genero() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre');
            $color_model = $this->model->get_child('genero');
            $color_model->not_null($not_null);
            $color_model->get(0);
            $color_model->change_status($_POST);
            $color_model->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=genero');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/segmentacion?tab=genero');
        endif;
    }

    public function ofertas() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->ofertas();
        $cache[1] = $this->model->ofertas();
        $cache[2] = $this->model->get_child('genero')->get_list('','',array('nombre'));
        $cache[3] = $this->model->ofertas();
        $this->view->ofertas(Session::singleton()->getUser(), $cache);
    }

    public function oferta_x_genero() {
        $genero = $_POST['genero'];
        $oferta = $_POST['oferta'];
        $this->model->oferta_x_genero($genero, $oferta);
    }

    public function oferta_x_detalle() {
        $linea = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color = $_POST['color'];
        $talla = $_POST['talla'];
        $oferta = $_POST['oferta'];

        $this->model->oferta_x_detalle($linea, $estilo, $color, $talla, $oferta);
    }

    public function salvar_traslado() {
        $tc = $this->model->get_child('traslado');
        $id = (!isset($_POST['id']) || empty($_POST['id'])) ? 0 : $_POST['id'];
        $data = $_POST;
        unset($data['id']);
        if (isset($data['bodega_origen'])) {
            $id_bodega_ = $data['bodega_origen'];
        } else {
            $id_bodega_ = $data['bodega_origen_r'];
            $data['bodega_origen'] = $data['bodega_origen_r'];
        }

        if (isset($data['bodega_destino'])) {
            $id_bodega = $data['bodega_destino'];
        } else {
            $id_bodega = $data['bodega_destino_r'];
            $data['bodega_destino'] = $data['bodega_destino_r'];
        }
        
        if($id==0){
            $transaccion = $this->model->get_child('transacciones');
            $transaccion->setVirtualId('cod');
            $transaccion->get($data['transaccion']);
            $data['cod'] = $transaccion->get_attr('ultimo') + 1;
            $transaccion->set_attr('ultimo', $data['cod']);
            $transaccion->save();
        }else{
            $data['cod'] = $id;
        }
        
        if (!isset($data['proveedor_origen'])) {
            $data['proveedor_origen'] = $data['proveedor_origen_r'];
        }

        if (!isset($data['proveedor_nacional'])) {
            $data['proveedor_nacional'] = $data['proveedor_nacional_r'];
        }

        if (!isset($data['concepto'])) {
            $data['concepto'] = $data['concepto_r'];
        }

        if (!isset($data['transaccion'])) {
            $data['transaccion'] = $data['transaccion_r'];
        }
        
        if (!isset($data['referencia_retaceo'])) {
            $data['referencia_retaceo'] = $data['referencia_retaceo_r'];
        }

        if (!isset($data['fecha'])) {
            $data['fecha'] = $data['fecha_r'];
        }

        if (!isset($data['total_pares'])) {
            $data['total_pares'] = $data['total_pares_r'];
        }

        if (!isset($data['total_costo'])) {
            $data['total_costo'] = $data['total_costo_r'];
        }
        
        $hoja_retaceo = $this->model->get_child('hoja_retaceo');
        $hoja_retaceo->get($data['referencia_retaceo']);
        $hoja_retaceo->aplicada = 1;
        $hoja_retaceo->save();

        $nombre_bodega  = $this->model->obtenerBodega($id_bodega);
        $nombre_bodega_ = $this->model->obtenerBodega($id_bodega_);

        if ($id == 0) {
            $data['editable'] = 1;
        }

        $data['usuario'] = Session::singleton()->getUser();
        
        $id = $tc->obtenerId($data['cod'], $data['transaccion']);
        
        $tc->get($id);
        $tc->change_status($data);
        $tc->save();

        if ($id == 0) {
            $id = $tc->last_insert_id();
        }else{
            
        }

        HttpHandler::redirect('/'.MODULE.'/inventario/detalle_traslado?id=' . $id);
    }

    public function borrar_traslado_detalle() {
        $this->model->borrar_traslado_detalle($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla'], $_POST['id_ref']);
        $this->model->reducir_costos_y_pares($_POST['cantidad'], $_POST['total'], $_POST['id_ref']);
        if ($_POST['consigna'] == 1) {
            $this->model->get_sibling('cliente')->actualizar_saldo($_POST['id_ref'], $_POST['total'] * -1);
            $this->model->reducir_costos_y_pares2($_POST['cantidad'], $_POST['total'], $_POST['id_ref']);
        }

        echo json_encode(array("msg"=>""));
    }

    public function detalle_traslado() {
        
        $this->validar();
        $id = $_GET['id'];

        ### inicializacion ###
        # comunes 
        $cache = array();
        $id_bodega_ = 0; // origen
        $nombre_bodega_ = "";
        $id_bodega = 0; // destino
        $nombre_bodega = "";
        $oConsigna = $this->model->get_child('traslado');
        data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
        data_model()->setActiveConnection(1);
        $system = $this->model->get_child('system');
        $system->get(1);
        data_model()->setActiveConnection(0);

        if ($oConsigna->exists($id) && !empty($id)) {

            $oConsigna->get($id);

            # solo traslado
            $total_costo = 0.0;
            $total_pares = 0;

            #solo consigna
            $credito = 0.0;
            $usado = 0.0;
            $saldo = 0.0;
            $consigna = 0;
            $cliente = 0;

            ### asignaciones ###
            # comunes

            $id_bodega_ = $oConsigna->get_attr('bodega_origen');
            $id_bodega = $oConsigna->get_attr('bodega_destino');
            $nombre_bodega_ = $this->model->obtenerBodega($id_bodega_);
            $nombre_bodega = $this->model->obtenerBodega($id_bodega);
            $consigna = $oConsigna->get_attr('consigna');
            $transaccion = $oConsigna->get_attr('transaccion');

            if ($consigna != 1) {
                # solo traslado
                $consigna = 0;
                $proveedor   = $oConsigna->get_attr('proveedor_origen');
                $total_costo = $oConsigna->get_attr('total_costo');
                $total_pares = $oConsigna->get_attr('total_pares');
            } else {
                $cliente = $oConsigna->get_attr('cliente');
            }

            $cache[0] = $this->model->get_child('linea')->get_list();
            $this->view->detalle_traslado(Session::singleton()->getUser(), $id, $id_bodega, $nombre_bodega, $id_bodega_, $nombre_bodega_, $transaccion, $total_costo, $total_pares, $cache, $cliente, $consigna, $system->tolerancia, $proveedor);
        } else {
            HttpHandler::redirect('/'.MODULE.'/error/not_found');
        }
    }

    public function traslados() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('proveedor')->get_list('','',array('nombre'));
        $cache[1] = $this->model->get_child('proveedor')->get_list('','',array('nombre'));
        $cache[2] = $this->model->get_child('bodega')->get_list('','',array('nombre'));
        $cache[3] = $this->model->get_child('bodega')->get_list('','',array('nombre'));
        $cache[4] = $this->model->get_child('transacciones')->transaccionesTraslados();
        $cache[5] = $this->model->get_child('hoja_retaceo')->aplicables();
        $this->view->traslados(Session::singleton()->getUser(), $cache);
    }

    public function datos_oferta() {
        $id = (!isset($_POST['id']) || empty($_POST['id'])) ? 0 : $_POST['id'];
        if ($id != 0)
            echo json_encode($this->model->datos_oferta($id));
        else
            echo json_encode(array('status' => false));
    }

    public function guardarProductoOferta() {
        $ofp = $this->model->get_child('oferta_producto');
        $ofp->get(0);
        $ofp->change_status($_POST);
        $ofp->save();
        $this->model->cambiarBodega($_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla']);
        HttpHandler::redirect('/'.MODULE.'/inventario/ofertas');
    }

    public function salvar_oferta() {
        $id = (!isset($_POST['id']) || empty($_POST['id'])) ? 0 : $_POST['id'];
        $of = $this->model->get_child('oferta');
        $of->get($id);
        $data = $_POST;
        unset($data['id']);
        $data['inicio'] = $this->getDateFromString($data['inicio']);
        $data['fin'] = $this->getDateFromString($data['fin']);
        $data['descuento'] = $data['descuento'] / 100;
        $of->change_status($data);
        $of->save();
        HttpHandler::redirect('/'.MODULE.'/inventario/ofertas');
    }

    public function eliminar_bodega() {
        $id = (empty($_GET['id']) || !isset($_GET['id'])) ? 0 : $_GET['id'];
        $bodega = $this->model->get_child('bodega');
        if ($bodega->exists($id)) {
            $bodega->delete($id);
            HttpHandler::redirect('/'.MODULE.'/inventario/bodegas');
        } else {
            HttpHandler::redirect('/'.MODULE.'/inventario/bodegas?err_no=404');
        }
    }

    public function guardar_bodega() {
        if (isset($_POST) && !empty($_POST)):
            $not_null = array('nombre', 'encargado', 'descripcion');
            $bodega_model = $this->model->get_child('bodega');
            $bodega_model->not_null($not_null);

            $id = (empty($_POST['id'])) ? 0 : addslashes($_POST['id']);
            $bodega_consigna = (!isset($_POST['esConsigna'])) ? '0' : true;
            $rellenar = (!isset($_POST['rellenar'])) ? false : true;

            $ultimo_en_blanco = $bodega_model->ultimo_en_blanco();
            $data = $_POST;
            if (!$rellenar || ($id != 0 && $bodega_model->existe($id)) || ( $ultimo_en_blanco == -1))
                $bodega_model->get($id);
            else {
                $bodega_model->get($ultimo_en_blanco);
                unset($data['id']);
            }

            $bodega_model->bodega_consigna = $bodega_consigna;
            $bodega_model->change_status($data);
            $bodega_model->save();

            HttpHandler::redirect('/'.MODULE.'/inventario/bodegas');
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/bodegas');
        endif;
    }

    ###### (FIN ACCIONES MANTENIMIENTOS) ##########

    public function nuevo_producto() {
        $this->validar();
        $usuario = Session::getUser();
        $cache[0] = $this->model->get_documents($usuario);
        $this->view->nuevo_producto($usuario, $cache);
    }

    /* public function stock(){
      $this->validar();
      $usuario = Session::getUser();
      $this->view->actualizar_stock($usuario);
      } */

    public function oc_data() {
        $cache = array();
        $id = $_POST['oc_id'];
        $cache[0] = $this->model->info_oc($id);
        $this->view->oc_data($cache);
    }

    public function resumenGeneralStock() {
        $this->validar();
        $this->view->resumenGeneralStock(Session::singleton()->getUser());
    }

    public function resumenGeneralProducto() {
        $this->validar();
        $this->view->resumenGeneralProducto(Session::singleton()->getUser());
    }

    public function exportarEstadoBodegaImg() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('estado_bodega')->get_list();
        $this->view->exportarEstadoBodegaImg($cache, Session::singleton()->getUser());
    }

    public function exportarEstadoBodega() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('estado_bodega')->get_list();
        $this->view->exportarEstadoBodega($cache, Session::singleton()->getUser());
    }

    public function exportarResumenProducto() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('producto')->get_list();
        $this->view->exportarResumenProducto($cache, Session::singleton()->getUser());
    }

    public function exportarHistorial($modulo) {
        $cache = array();
        $cache[0] = $this->model->get_child('historial')->filter('modulo', $modulo);
        $this->view->exportarHistorial($cache, Session::singleton()->getUser());
    }

    public function efectuarCambioPrecios() {
        $ret = array();
        $ret['action'] = "";
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $color = $_POST['color'];
        $precio = $_POST['precio'];
        $talla1 = $_POST['talla1'];
        $talla2 = $_POST['talla2'];
        $talla = "";

        /* UNTA TALLA ESTA VACIA */
        if ((empty($talla1) || empty($talla2)) && !(empty($talla1) && empty($talla2))) {
            if (empty($talla1))
                $talla = $talla2;
            if (empty($talla2))
                $talla = $talla1;
            if (empty($color)) {
                $ret['action'] = "Cambio por estilo, linea, y una talla";
                $this->model->cambiosPorEstiloLineaTalla($estilo, $linea, $talla, $precio);
            } else {
                $ret['action'] = "Cambio por estilo, linea,color, y una talla";
                $this->model->cambiosPorEstiloLineaTallaColor($estilo, $linea, $talla, $color, $precio);
            }
        }

        /* AMBAS TALLAS EXISTEN */
        if (!(empty($talla1) && empty($talla2))) {
            if (empty($color)) {
                $ret['action'] = "Cambio por estilo, linea y corrida";
                $this->model->cambiosPorEstiloLineaCorrida($estilo, $linea, $talla1, $talla2, $precio);
            } else {
                $ret['action'] = "Cambio por estilo, linea,color y corrida";
                $this->model->cambiosPorEstiloLineaCorridaColor($estilo, $linea, $talla1, $talla2, $color, $precio);
            }
        }

        /* AMBAS TALLAS ESTAN VACIAS */
        if (empty($talla1) && empty($talla2)) {
            if (empty($color)) {
                $ret['action'] = "cambio por estilo y linea";
                $this->model->cambiosPorEstiloLinea($estilo, $linea, $precio);
            } else {
                $ret['action'] = "cambio por estilo, linea y color";
                $this->model->cambiosPorEstiloLineaColor($estilo, $linea, $color, $precio);
            }
        }
        echo json_encode($ret);
    }

    public function efectuarCambioCostos() {
        $ret = array();
        $ret['action'] = "";
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $color = $_POST['color'];
        $costo = $_POST['costo'];
        $talla1 = $_POST['talla1'];
        $talla2 = $_POST['talla2'];
        $talla = "";

        /* UNTA TALLA ESTA VACIA */
        if ((empty($talla1) || empty($talla2)) && !(empty($talla1) && empty($talla2))) {
            if (empty($talla1))
                $talla = $talla2;
            if (empty($talla2))
                $talla = $talla1;
            if (empty($color)) {
                $ret['action'] = "Cambio por estilo, linea, y una talla";
                $this->model->cambiosPorEstiloLineaTallaC($estilo, $linea, $talla, $costo);
            } else {
                $ret['action'] = "Cambio por estilo, linea,color, y una talla";
                $this->model->cambiosPorEstiloLineaTallaColorC($estilo, $linea, $talla, $color, $costo);
            }
        }

        /* AMBAS TALLAS EXISTEN */
        if (!(empty($talla1) && empty($talla2))) {
            if (empty($color)) {
                $ret['action'] = "Cambio por estilo, linea y corrida";
                $this->model->cambiosPorEstiloLineaCorridaC($estilo, $linea, $talla1, $talla2, $costo);
            } else {
                $ret['action'] = "Cambio por estilo, linea,color y corrida";
                $this->model->cambiosPorEstiloLineaCorridaColorC($estilo, $linea, $talla1, $talla2, $color, $costo);
            }
        }

        /* AMBAS TALLAS ESTAN VACIAS */
        if (empty($talla1) && empty($talla2)) {
            if (empty($color)) {
                $ret['action'] = "cambio por estilo y linea";
                $this->model->cambiosPorEstiloLineaC($estilo, $linea, $costo);
            } else {
                $ret['action'] = "cambio por estilo, linea y color";
                $this->model->cambiosPorEstiloLineaColorC($estilo, $linea, $color, $costo);
            }
        }
        echo json_encode($ret);
    }

    public function inventarioHistorial() {
        $this->validar();
        if (Session::singleton()->getLevel() != 1):
            HttpHandler::redirect('/'.MODULE.'/error/e403');
        else:
            import('scripts.paginacion');
            $numeroRegistros = $this->model->get_child('documento')->quantify();
            $url_filtro = "/'.MODULE.'/inventario/inventarioHistorial?";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache = array();
            $cache[0] = $this->model->get_child('documento')->documentos($limitInf, $tamPag);
            $this->view->inventarioHistorial(Session::singleton()->getUser(), $cache, $paginacion_str);
        endif;
    }

    public function reporteDocumentoPr(){
        $docDetail = $this->model->detalleDocumento($_GET['id']);
        data_model()->newConnection(HOST, USER, PASSWORD, "db_system");
        data_model()->setActiveConnection(1);
        $system = $this->model->get_child('system');
        $system->get(1);
        data_model()->setActiveConnection(0);
        $this->view->reporteDocumentoPr($docDetail, $_GET['id'], $system);
    }

    public function p_transito() {
        $ret = array();
        $cache = array();
        import('scripts.paginacion');
        $numeroRegistros = $this->model->cantidadOc($_POST);
        $url_filtro = "/'.MODULE.'/inventario/orden_compra?";
       
        
        if($_POST['pendiente'] == 'true'){ $url_filtro .= "p=true&"; }
        if($_POST['rechazado'] == 'true'){ $url_filtro .= "r=true&"; }
        if($_POST['entregado'] == 'true'){ $url_filtro .= "e=true&"; }
        

        list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
        $cache[0] = $this->model->transito($_POST, $limitInf, $tamPag);
        $ret['html'] = $this->view->p_transito($cache, $paginacion_str);
        echo json_encode($ret);
    }

    public function ver_producto_en_transito(){
        import('scripts.paginacion');

        $estilo = (isset($_GET['estilo']) && !empty($_GET['estilo']) ) ? $_GET['estilo']:"";

        $numeroRegistros = $this->model->cantidadTransito($estilo);
        if($estilo!=""){
            $url_filtro = "/'.MODULE.'/inventario/ver_producto_en_transito?estilo=$estilo&";
        }else{
            $url_filtro = "/'.MODULE.'/inventario/ver_producto_en_transito?";
        }
        list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);

        $cache = $this->model->ver_producto_en_transito($estilo, $limitInf, $tamPag);

        $this->view->ver_producto_en_transito($cache, $paginacion_str);
    }

    public function del_detalle() {
        $linea = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color = $_POST['color'];
        $talla = $_POST['talla'];
        $id = $_POST['id_orden'];
        $this->model->del_detalle($linea, $estilo, $color, $talla, $id);
    }

    public function f_transito() {
        $ret = array();
        $cache = array();
        $id = $_POST['id'];
        $cache[0] = $this->model->seleccion_oc($id);
        $ret['html'] = $this->view->p_transito($cache);
        echo json_encode($ret);
    }

    public function edicion_oc() {
        $id = $_GET['id'];
        $ret = array();
        $cache = array();
        $cache[0] = $this->model->get_oc($id);
        $cache[1] = $this->model->get_oc_detalle($id);
        $cache[2] = $this->model->get_child('linea')->get_list('','',array('nombre'));
        $ret['html'] = $this->view->edicion_oc($cache);
        echo json_encode($ret);
    }

    public function catalogos() {
        $this->validar();
        $this->view->catalogos(Session::singleton()->getUser());
    }

    public function stockHistorial() {
        $this->validar();
        if (Session::singleton()->getLevel() != 1):
            HttpHandler::redirect('/'.MODULE.'/error/e403');
        else:
            import('scripts.paginacion');
            $numeroRegistros = $this->model->get_child('historial')->quantify();
            $url_filtro = "/'.MODULE.'/inventario/stockHistorial?";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache = array();
            $cache[0] = $this->model->get_child('historial')->filter('modulo', 'stock', $limitInf, $tamPag);
            $this->view->stockHistorial(Session::singleton()->getUser(), $cache, $paginacion_str);
        endif;
    }

    public function verificarProducto() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $color = $_POST['color'];
        $talla = $_POST['talla'];
        echo $this->model->verificarProducto($linea, $estilo, $color, $talla);
    }

    public function agregar_detalle_oc() {
        $dt_oc = $this->model->get_child('detalle_orden_compra');
        $dt_oc->get(0);
        $dt_oc->change_status($_POST);
        $dt_oc->save();
    }

    public function agregar_detalle_oc_a() {
        $dt_oc = $this->model->get_child('detalle_orden_compra');
        $dt_oc->get(0);
        $dt_oc->change_status($_POST);
        $dt_oc->es_anexo = "1";
        $dt_oc->save();
    }

    public function actualizar_detalle_oc() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $color = $_POST['color'];
        $talla = $_POST['talla'];
        $orden = $_POST['id_orden'];
        $cantidad = $_POST['cantidad'];
        $query = "SELECT id FROM detalle_orden_compra WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND id_orden=$orden";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        $id = $data['id'];
        $query = "UPDATE detalle_orden_compra SET cantidad = cantidad + $cantidad WHERE id=$id";
        data_model()->executeQuery($query);
    }

    public function actualizar_oc() {
        $pares = $_POST['total'];
        $costo = $_POST['total_costo'];
        $id = $_POST['id'];
        $query = "UPDATE orden_compra SET total = $pares, total_costo=$costo WHERE id=$id";
        data_model()->executeQuery($query);
    }

    public function estado_oc() {
        $estado = $_GET['estado'];
        $id     = $_GET['id'];
        if($estado=="RECHAZADO"){
            $this->model->estado_oc($estado, $id);
            HttpHandler::redirect('/'.MODULE.'/inventario/orden_compra?conf=ok');
        }else if($estado=="ENTREGADO"){
            $detalle = $this->model->inicializarRecepcion($id);
            $anexos = $this->model->obtenerAnexos($id);
            $orden   = $this->model->get_child('orden_compra');
            $orden->get($id);
            $proveedor = $this->model->get_child('proveedor');
            $proveedor->get($orden->proveedor);
           
            $this->view->recibir_oc($detalle, $id, $orden, $proveedor, $anexos);
        }
    }

    public function recibir_item(){
        $rep = $this->model->get_child('recepcion_orden_compra');
        $rep->get(0);
        $rep->id_detalle  = $_POST['id_detalle'];
        $rep->id_orden    = $_POST['id_orden'];
        $rep->descripcion = $_POST['descripcion'];
        $rep->fecha = date("Y-m-d");
        $rep->save();
        $detalle = $this->model->get_child("detalle_orden_compra");
        $detalle->get($_POST['id_detalle']);
        $detalle->recibidos = $_POST['cantidad'];
        $detalle->save();
    }

    public function cargar_operaciones_oc(){
        $operaciones = $this->model->operaciones_oc($_POST['id_orden']);
        $resp = array();
        $resp['html'] = $this->view->operaciones_oc($operaciones);
        echo json_encode($resp);
    }

    public function dar_baja_item_oc(){
        $rep = $this->model->get_child('recepcion_orden_compra');
        $rep->get(0);
        $rep->id_detalle  = $_POST['id_detalle'];
        $rep->id_orden    = $_POST['id_orden'];
        $rep->fecha = date("Y-m-d");
        $detalle = $this->model->get_child("detalle_orden_compra");
        $detalle->get($_POST['id_detalle']);
        $detalle->cancelados =  $detalle->cantidad -  $detalle->recibidos;
        $detalle->save();   
        $rep->descripcion = "De baja ".$detalle->cancelados." unidades del producto ".$detalle->linea."-".$detalle->estilo."-".$detalle->color."-".$detalle->talla." [Orden No".$_POST['id_orden']."]";
        $rep->save();
        echo json_encode(array("cancelados"=>$detalle->cancelados, "id"=>$_POST['id_detalle']));
    }

    public function deshacer_operacion_oc(){
        $id_detalle = $_POST['id_detalle'];
        $this->model->deshacer_operacion_oc($id_detalle);
        $detalle = $this->model->get_child("detalle_orden_compra");
        $detalle->get($id_detalle);
        $detalle->cancelados =  0;
        $detalle->recibidos  =  0;
        $detalle->save();
        echo json_encode(array("id"=>$id_detalle));   
    }

    public function procesarOrdenCompra(){
        $id = $_GET['id'];
        $pendientes = $this->model->itemsPendientesOC($id);
        if($pendientes == 0){
            $orden_compra = $this->model->get_child('orden_compra');
            $orden_compra->get($id);
            if($orden_compra->estado=="PENDIENTE"){
                $traslado      = $this->model->get_child('traslado');
                $transacciones = $this->model->get_child('transacciones');
                $transacciones->get('1B');
                $ultima_transaccion = $transacciones->ultimo;

                $traslado->get(0);
                $traslado->fecha = date("Y-m-d");
                $traslado->proveedor_origen = $orden_compra->proveedor;
                $traslado->proveedor_nacional = $orden_compra->proveedor;
                $traslado->bodega_origen  = 1;
                $traslado->bodega_destino = 1;
                $traslado->concepto    = $orden_compra->concepto;
                $traslado->transaccion = "1B";
                $traslado->total_pares = $orden_compra->total;
                $traslado->total_costo = $orden_compra->total_costo;
                $traslado->total_costo_p = $orden_compra->total_costo;
                $traslado->total_pares_p = $orden_compra->total;
                $traslado->editable = "0";
                $traslado->consigna = "0";
                $traslado->usuario  = Session::singleton()->getUser();
                $traslado->concepto_alternativo = "";
                $traslado->cliente  = 0;
                $traslado->cod = $ultima_transaccion + 1;


                $transacciones->ultimo = $ultima_transaccion + 1;
                $transacciones->save(); 
                $traslado->save();
                $id_traslado = $traslado->last_insert_id();

                $query = "SELECT * FROM detalle_orden_compra WHERE id_orden = $id";
                data_model()->executeQuery($query);
                $detalle_orden_compra = array();
                while($item = data_model()->getResult()->fetch_assoc()){
                    $detalle_orden_compra[] = $item;
                }

                foreach ($detalle_orden_compra as $item) {
                    
                    $detalle_traslado = $this->model->get_child('detalle_traslado');
                    $detalle_traslado->get(0);
                    $detalle_traslado->id_ref = $id_traslado;
                    $detalle_traslado->linea = $item['linea'];
                    $detalle_traslado->estilo = $item['estilo'];
                    $detalle_traslado->color = $item['color'];
                    $detalle_traslado->talla = $item['talla'];
                    $detalle_traslado->costo = $item['costo'];
                    $detalle_traslado->cantidad = $item['recibidos'];
                    $detalle_traslado->total = $item['costo'] * $item['recibidos'];
                    $detalle_traslado->bodega = 1;
                    $detalle_traslado->save();
                }

                $this->model->ingresoCompra($id_traslado, 1);
                $orden_compra->estado = "ENTREGADO";
                $orden_compra->fecha_entrega = date("Y-m-d");
                $orden_compra->save();
            }
        }

        HttpHandler::redirect("/'.MODULE.'/inventario/imprimirReportesOC?id_orden=".$id."&traslado=".$id_traslado);
    }

    public function imprimirReportesOC(){

        $this->view->imprimirReportesOC($_GET['id_orden'], $_GET['traslado']);
    }

    public function datos_producto(){
        $producto = $this->model->get_child('producto');
        $producto->get(array("linea"=>$_POST['linea'], "estilo"=>$_POST['estilo']));

        $resp = array("descripcion"=>$producto->descripcion, "catalogo"=>$producto->catalogo, "pagina"=>$producto->n_pagina);

        echo json_encode($resp);
    }

    public function bloquear_orden() {
        $id = $_GET['id'];
        $query = "UPDATE orden_compra SET editable = 0 WHERE id=$id";
        data_model()->executeQuery($query);
        HttpHandler::redirect('/'.MODULE.'/inventario/orden_compra');
    }

    public function cancelar_oc() {
        $id = $_GET['id'];
        $query = "SELECT editable FROM orden_compra WHERE id=$id";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        $editable = $data['editable'];
        if ($editable == 1) {
            $query = "DELETE FROM detalle_orden_compra WHERE id_orden = $id";
            data_model()->executeQuery($query);
            $query = "DELETE FROM orden_compra WHERE id = $id";
            data_model()->executeQuery($query);
            HttpHandler::redirect('/'.MODULE.'/inventario/orden_compra?success=DEL');
        } else {
            HttpHandler::redirect('/'.MODULE.'/inventario/orden_compra?err=NODEL');
        }
    }

    public function stock() {
        if (isset($_GET['doc']) && !empty($_GET['doc'])):
            $this->validar();
            $doc = $_GET['doc'];
            $this->model->actualizarControlPrecio($doc);
            if ($this->model->get_child('documento')->exists($doc)):
                $usuario = Session::getUser();
                $this->view->actualizarStock($usuario, $doc);
            else:
                HttpHandler::redirect('/'.MODULE.'/inventario/stock_documentos');
            endif;
        else:
            echo 'Llama incorrecta a procedimiento';
        endif;
    }

    public function DatosCatalogo() {
        $id = $_POST['id'];
        echo json_encode($this->model->CatalogoPorId($id));
    }

    public function crear_documento() {
        $this->validar();
        $user = Session::getUser();
        $this->view->nuevo_documento($user);
    }

    public function stock_documentos() {
        $this->validar();
        $user = Session::singleton()->getUser();
        $cache = array();
        $cache[0] = $this->model->documentoStock($user);
        $this->view->stock_documentos($user, $cache);
    }

    /* Muestra un lista de las tallas disponibles para un estilo y color determinado */

    public function preliminar_tallas() {
        $estilo = $_POST['estilo'];
        $color = $_POST['color'];
        $buffer_str = "";
        $sql = "SELECT * FROM documento_talla_producto WHERE talla_estilo_producto=$estilo AND color=$color";
        $sql2 = "SELECT * FROM talla_producto WHERE talla_estilo_producto=$estilo AND color=$color";
        data_model()->executeQuery($sql);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $talla = $data['talla'];
            $buffer_str.="
        <tr>
          <td>$talla</td>
        </tr>
    ";
        endwhile;
        data_model()->executeQuery($sql2);
        while ($data = data_model()->getResult()->fetch_assoc()):
            $talla = $data['talla'];
            $buffer_str.="
        <tr>
          <td>$talla</td>
        </tr>
    ";
        endwhile;
        echo $buffer_str;
    }

    /* Crea un nuevo documento */

    public function salvar_documento() {
        $data = array();
        $data['propietario'] = Session::singleton()->getUser();
        $data['fecha_creacion'] = date("y-m-d");
        $data['modulo'] = 'inventario';
        $objDocument = $this->model->get_child('documento');
        $objDocument->get(0);
        $objDocument->change_status($data);
        $objDocument->save();
        HttpHandler::redirect('/'.MODULE.'/inventario/nuevo_producto');
    }

    public function salvarCatalogo() {
        $data = $_POST;
        $catalogo = $this->model->get_child('catalogo');
        $catalogo->get(0);
        $data['inicio'] = $this->getDateFromString($data['inicio']);
        $data['final'] = $this->getDateFromString($data['final']);
        $catalogo->change_status($data);
        $catalogo->save();
        upload_image(APP_PATH . 'static/img/catalogo', 'archivo', $catalogo->last_insert_id().".jpg");
        HttpHandler::redirect('/'.MODULE.'/inventario/catalogos');
    }

    public function getDateFromString($String) {
        $DateArray = explode(".", $String);
        return $DateArray[2] . "-" . $DateArray[1] . "-" . $DateArray[0];
    }

    public function salvarDocumentoStock() {
        $data = array();
        $data['propietario'] = Session::singleton()->getUser();
        $data['fecha_creacion'] = date("y-m-d");
        $data['modulo'] = 'stock';
        $objDocument = $this->model->get_child('documento');
        $objDocument->get(0);
        $objDocument->change_status($data);
        $objDocument->save();
        HttpHandler::redirect('/'.MODULE.'/inventario/stock_documentos');
    }

    /* Abre un nuevo documento */

    public function seleccion_documento() {
        $this->validar();
        $usuario = Session::getUser();
        $doc = (isset($_GET) && !empty($_GET)) ? $_GET['documento'] : 0;
        if ($this->model->document_acces($usuario, $doc)):
            $this->view->vista_documento($usuario, $doc);
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/principal');
        endif;
    }

    public function cambiarDatoGeneral() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $data = $_POST;
        unset($data['estilo']);
        if (isset($data['precio']))
            unset($data['precio']);
        if (isset($data['costo']))
            unset($data['costo']);
        unset($data['linea']);
        if (isset($data['pagina'])) {
            $data['n_pagina'] = $data['pagina'];
            unset($data['pagina']);
        }
        $this->model->cambiarDatosGenerales($linea, $estilo, $data);
    }

    public function cambiarPrecios() {
        $this->validar();
        $cache = array();
        //$cache[0] = $this->model->get_child('proveedor')->get_list();
        $cache[0] = $this->model->get_child('catalogo')->get_list();
        $this->view->cambiarPrecios(Session::singleton()->getUser(), $cache);
    }

    public function DatosGeneralesProducto() {
        $estilo = $_POST['estilo'];
        $linea  = $_POST['linea'];
        echo json_encode($this->model->DatosGeneralesProducto($linea, $estilo));
    }

    public function doc_productos() {
        $this->validar();
        $usuario = Session::getUser();
        $doc = (isset($_GET) && !empty($_GET)) ? $_GET['documento'] : 0;
        if ($this->model->document_acces($usuario, $doc)):
            $cache     = array();
            $cache[0]  = $this->model->get_child('linea')->get_list();
            $cache[1]  = $this->model->get_child('marca')->get_list();
            $cache[2]  = $this->model->get_child('proveedor')->get_list('', '', array('nombre'));;
            $cache[3]  = $this->model->get_child('color')->get_list('', '', array('nombre'));
            $cache[4]  = $this->model->get_child('genero')->get_list();
            $cache[5]  = $this->model->get_child('linea')->get_list();
            $cache[6]  = $this->model->get_child('marca')->get_list();
            $cache[7]  = $this->model->get_child('proveedor')->get_list('', '', array('nombre'));
            $cache[8]  = $this->model->get_child('color')->get_list('', '', array('nombre'));
            $cache[9]  = $this->model->get_child('genero')->get_list();
            $cache[10] = $this->model->get_child('catalogo')->get_list();
            $cache[11] = $this->model->get_child('catalogo')->get_list();
            $cache[12]  = $this->model->get_child('tacon')->get_list('', '', array('nombre'));
            $cache[13]  = $this->model->get_child('suela')->get_list('', '', array('nombre'));
            $cache[14]  = $this->model->get_child('material')->get_list('', '', array('nombre'));
            $cache[15]  = $this->model->get_child('concepto')->get_list('', '', array('nombre'));
            $cache[16]  = $this->model->get_child('grupo')->get_list('', '', array('nombre'));
            $this->view->doc_mantenimiento_de_productos($cache, $doc, $usuario);
        else:
            HttpHandler::redirect('/'.MODULE.'/inventario/principal');
        endif;
    }

    public function borrar_colores() {
        if (!empty($_POST['string_colores'])):
            $estilo = $_POST['estilo'];
            $color = $_POST['string_colores'];
            $cd = $this->model->get_child('documento_talla_producto');
            if ($cd->editable($estilo, $color)) {
                $this->model->borrar_color($estilo, $color);
            } else {
                echo "No se puede eliminar, otros datos pueden depender de este registro";
            }
        endif;
    }

    public function verificacionProveedor() {
        $this->validar();
        $this->view->passProveedor();
    }

    public function vericarPermisoPrecio() {
        $this->validar();
        $this->view->passPrecio();
    }

    /* Lista de documentos */

    public function abrir_documento() {
        $this->validar();
        $usuario = Session::getUser();
        $cache = array();
        $cache[0] = $this->model->get_documents($usuario);
        $this->view->abrir_documento($usuario, $cache);
    }

    public function colores_producto() {
        echo $this->model->colores_actuales($_POST['estilo']);
    }

    public function obtener_colores(){

        echo $this->model->coloresXestilo($_GET['estilo']);
    }

    public function salvar_color() {
        if (isset($_POST) && !empty($_POST)):
            $model = $this->model->get_child('documento_color_producto');
            $model->get(0);
            $model->change_status($_POST);
            $model->force_save();
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function concederAcceso() {
        $system = $this->model->get_child('system');
        $system->get(1);
        $clave = $system->get_attr('clave');
        if (isset($_POST) && !empty($_POST)):
            if (md5($_POST['clave']) == $clave):
                $_SESSION['p'] = true;
                HttpHandler::redirect('/'.MODULE.'/inventario/proveedores');
            else:
                HttpHandler::redirect('/'.MODULE.'/inventario/verificacionProveedor?error=1530');
            endif;
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function concederAccesoPrecio() {
        $system = $this->model->get_child('system');
        $system->get(1);
        $clave = $system->get_attr('clave');
        if (isset($_POST) && !empty($_POST)):
            if (md5($_POST['clave']) == $clave):
                $_SESSION['p'] = true;
                HttpHandler::redirect('/'.MODULE.'/inventario/cambiarPrecios');
            else:
                HttpHandler::redirect('/'.MODULE.'/inventario/vericarPermisoPrecio?error=1530');
            endif;
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function salvar_stock() {
        if (isset($_POST) && !empty($_POST)):
            $data = $_POST;
            echo var_dump($data);
            $doc = $_POST['documento'];
            $model_stock = $this->model->get_child('estado_bodega_documento');
            $data['bodega'] = 1; // referencia a bodega principal
            $sql = $this->model->sql_existencia($data);
            data_model()->executeQuery($sql);
            if (data_model()->getNumRows() > 0):
                $res = data_model()->getResult();
                while ($datos = $res->fetch_assoc()):
                    $id = $datos['id'];
                    $model_stock->get($id);
                    $data['stock'] += $datos['stock'];
                    $model_stock->change_status($data);
                    $model_stock->save();
                endwhile;
            else:
                $model_stock->get(0);
                $model_stock->change_status($data);
                $model_stock->save();
            endif;
            $h_data = array();
            $h_data['usuario'] = Session::getUser();
            $h_data['descripcion'] = "se agregaron " . $data['stock'] . " unidades al producto " . $data['estilo'];
            $h_data['fecha_hora'] = date("y-m-d h:m:s");
            $h_data['modulo'] = "stock";
            $historial = $this->model->get_child('historial');
            $historial->get(0);
            $historial->change_status($h_data);
            $historial->save();
            HttpHandler::redirect('/'.MODULE.'/inventario/stock?doc=' . $doc);
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function historial() {
        $this->validar();
        $cache[0] = $this->model->get_child('historial')->get_list();
        $this->view->historial($cache);
    }

    public function agregar_corrida() {
        if (isset($_POST) && !empty($_POST)):
            $inf_corrida = $_POST['inf_corrida'];
            $sup_corrida = $_POST['sup_corrida'];
            $estilo = $_POST['estilo'];
            $color = $_POST['color'];
            $sql = "";
            $medios = (isset($_POST['medios']) && $_POST['medios'] == "on" ) ? true : false;
            $tallas = array();
            for ($i = $inf_corrida; $i <= $sup_corrida; $i++):
                $tallas[] = $i;
                if ($medios):
                    $tallas[] = $i + 0.5;
                endif;
            endfor;
            foreach ($tallas as $talla):
                $sql = "SELECT * FROM talla_producto WHERE talla_estilo_producto=$estilo AND talla_color=$color AND talla=$talla";
                data_model()->executeQuery($sql);
                if (data_model()->getNumRows() == 0):
                    $data = array();
                    $data['talla_estilo_producto'] = $estilo;
                    $data['talla_color'] = $color;
                    $data['talla'] = $talla;
                    $talla_obj = $this->model->get_child('talla_producto');
                    $talla_obj->get(0);
                    $talla_obj->change_status($data);
                    $talla_obj->save();
                endif;
            endforeach;
            HttpHandler::redirect('/'.MODULE.'/inventario/productos');
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function nuevoPrecioProducto($precio, $estilo, $color, $talla, $linea) {
        $this->model->nuevoPrecioProducto($precio, $estilo, $color, $talla, $linea);
        echo json_encode(array('status' => 200));
    }

    public function agregar_corrida_doc() {
        if (isset($_POST) && !empty($_POST)):
            $inf_corrida = $_POST['inf_corrida'];
            $sup_corrida = $_POST['sup_corrida'];
            $estilo = $_POST['estilo'];
            $color = $_POST['color'];
            $linea = $_POST['linea'];
            $doc = $_GET['doc'];

            $prodMdl = $this->model->get_child('producto');
            $prodTmp = $this->model->get_child('documento_producto');
            $prodMdl->setVirtualId('estilo');
            $prodTmp->setVirtualId('estilo');
            $prodTmp->get(array("estilo"=>0, "linea"=>0));
            $fields = $prodMdl->get_fields();
            if(!$prodTmp->exists($estilo)){
                $prodMdl->get(array("estilo"=>$estilo, "linea"=>$linea));
                foreach ($fields as $field) {
                    $prodTmp->set_attr($field, $prodMdl->get_attr($field));
                }
                $prodTmp->set_attr('visible', '0');
                $prodTmp->set_attr('numero_documento', $doc);
                $prodTmp->save();
            }  

            $sql = "";
            $medios = (isset($_POST['medios']) && $_POST['medios'] == "on" ) ? true : false;
            $tallas = array();
            for ($i = $inf_corrida; $i <= $sup_corrida; $i++):
                $tallas[] = $i;
                if ($medios):
                    $tallas[] = $i + 0.2;
                    $tallas[] = $i + 0.4;
                    $tallas[] = $i + 0.6;
                    $tallas[] = $i + 0.8;
                endif;
            endfor;
            foreach ($tallas as $talla):
                $sql = "SELECT * FROM documento_talla_producto WHERE talla_estilo_producto='{$estilo}' AND color=$color AND talla=$talla";
                data_model()->executeQuery($sql);
                if (data_model()->getNumRows() == 0):
                    $data = array();
                    $data['talla_estilo_producto'] = $estilo;
                    $data['color'] = $color;
                    $data['talla'] = $talla;
                    $data['linea'] = $linea;
                    $talla_obj = $this->model->get_child('documento_talla_producto');
                    $talla_obj->get(0);
                    $talla_obj->change_status($data);
                    $talla_obj->save();
                endif;
            endforeach;
            HttpHandler::redirect('/'.MODULE.'/inventario/doc_productos?documento=' . $doc);
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function administrar() {
        $this->validar();
        $doc = $_GET['documento'];
        $usuario = Session::getUser();
        $this->view->administrar($doc, $usuario);
    }

    public function actualizar_productos() {
        $doc = (isset($_GET['documento'])) ? $_GET['documento'] : 0;
        $this->model->salvarCambiosDocumento($doc);
        // redireccionamos
        HttpHandler::redirect('/'.MODULE.'/inventario/nuevo_producto');
    }

    public function subir_foto() {
        if (isset($_POST) && !empty($_POST)):
            upload_image(APP_PATH . 'static/img/productos/', 'foto', $_POST['estilo']);
            HttpHandler::redirect('/'.MODULE.'/inventario/doc_productos?documento=' . $_POST['documento']);
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function productos() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('linea')->get_list();
        $cache[1] = $this->model->get_child('marca')->get_list();
        $cache[2] = $this->model->get_child('proveedor')->get_list();
        $cache[3] = $this->model->get_child('color')->get_list();
        $cache[4] = $this->model->get_child('genero')->get_list();
        $this->view->mantenimiento_de_productos($cache);
    }

    public function guardar_color_producto() {
        header('Content-type:text/javascript;charset=UTF-8');
        $producto = $_POST['producto'];
        $producto = addslashes($producto);
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        if ($json->{'action'} == 'save'):
            $sql = "";
            $params = array();
            $errors = "";

            //deal with those deleted
            $deletedRecords = $json->{'deletedRecords'};
            foreach ($deletedRecords as $value):
                $sql = "delete from color_producto where color_estilo_producto = $producto and color = $value->color ";
                data_model()->executeQuery($sql);
            endforeach;

            //deal with those inserted
            $insertedRecords = $json->{'insertedRecords'};
            foreach ($insertedRecords as $value):
                $sql = "insert into color_producto(color_estilo_producto,color) values ($producto,$value->color)";
                data_model()->executeQuery($sql);
            endforeach;

            $ret = "{success : true,exception:''}";
            echo $ret;
        endif;
    }
    
    public function guardarNuevoProducto(){
        header('Content-type:text/javascript;charset=UTF-8');
        $json        = $_POST['productInfo'];
        $productInfo = json_decode(stripslashes($json));
        $estilo      = $productInfo->{"estilo"};
        $linea       = $productInfo->{"linea"};
        $response    = array();
        $modelo      = $this->model->get_child('tarjeta_costo');
        $prodTmp     = $this->model->get_child('documento_producto');
        $prodSys     = $this->model->get_child('producto');
        $colorml     = $this->model->get_child('documento_color_producto');
        $colortl     = $this->model->get_child('color_producto');
        $colorCount  = 0;
        $param       = array();


        $prodTmp->setVirtualId('estilo');
        $prodSys->setVirtualId('estilo');
        
        /**
         * COSAS QUE SE DAN POR HECHO: 
         * 1. Si un producto existe en la tabla documento_producto esto quiere decir
         *    que existe una entrada en la tabla tarjeta_costo para ese producto la cual
         *    todavia es posible editarla.
         * 2. No se busca producto en tarjeta costo porque es puramente historial.
         *    si un producto existe en tarjeta costo y existe en documento_producto entonces
         *    se trata de un nuevo producto que todavia esta en edicion y no existe realmente en el
         *    catalogo. Si el producto se encuentra en tarjeta_costo y en la tabla producto se trata de
         *    un producto que ya esta vigente en el catalogo.
         * 3. No es posible que exista un producto en tarjeta costo y que no este ni en la tabla de productos
         *    ni en los documentos del producto.
         * 4. Por regla general tampoco es posible que exista un producto en la tabla de productos o los
         *    documentos del producto si no existe una entrada para el mismo en las tarjetas de costos
         * 
         * Algunas aclaraciones para recordar:
         * 1. Cuando se crea un nuevo producto primero se verifica si no existe en algun documento no aprobado
         *    o en los catalogos de la empresa. Si el producto no existe se procede a crear primeramente su 
         *    entrada en la tarjeta de costos. Despues de crear la entrada se ingresa a las tablas temporales
         *    las cuales contienen los datos mas detallados. Algo importante a resaltar es que solo se ingresan
         *    los datos generales del producto y los colores. Por lo tanto en la tarjeta de costos la corrida inicial
         *    hasta la final se establecen como 0 ("cero") y se guardan los datos recibidos. Para poder modificar 
         *    estas corridas pueden darse varios casos. 
         * 
         *    Si el producto acaba de ser ingresado en una tarjeta de costos (el registro se encuentra en 
         *    la tabla documento_producto). Entonces es necesario encontrar la tarjeta de costos para el producto
         *    , estilo y color en cuestion y modificar las corriadas limite adecuadamente. (En esta funcion NO
         *    se modifican corridas, eso se hace en otra seccion) 
         * 
         *    Si el producto es uno que ya esta en catalogo entonces se debera crear una entrada nueva en la tarjeta
         *    de costo para los colores nuevos que no se hayan agregado.
         *     
         */
        
        
        $tmp = $prodTmp->exists($estilo);
        $sys = $prodSys->exists($estilo);
        
        $response['found'] = ($tmp || $sys);
        
        if(!$response['found']){
            // NO SE ENCONTRO EL PRODUCTO EN NINGUNA TABLA
            # inserta
            # 1. creacion tarjeta_costo
            foreach($productInfo->{"colores"} as $color){
                $colorml->get(0);
                $param['estilo'] = $estilo;
                $param['color']  = $color;
                if(!$colorml->exists($param)){
                    $modelo->setVirtualId('id');
                    $modelo->get(0);      
                    $modelo->CESTILO    = $estilo;
                    $modelo->LINEA      = $linea;
                    $modelo->CODORIGEN  = $productInfo->{"codigo_origen"};
                    $modelo->CCOLOR     = $color;
                    $modelo->DESCRIP    = $productInfo->{"descripcion"};
                    $modelo->PROVEEDOR  = $productInfo->{"proveedor"};
                    $modelo->CATALOGO   = $productInfo->{"catalogo"};
                    $modelo->PAGINA     = $productInfo->{"n_pagina"};
                    $modelo->GENERO     = $productInfo->{"genero"};
                    $modelo->MARCA      = $productInfo->{"marca"};
                    $modelo->PROPIEDAD  = $productInfo->{"propiedad"};
                    $modelo->OBSERVACIO = $productInfo->{"observacion"};
                    $modelo->FEINGRESA  = $productInfo->{"fecha_ingreso"};
                    $modelo->NODOC      = $productInfo->{"documento"};
                    $modelo->TACON      = 0;
                    $modelo->SUELA      = 0;
                    $modelo->RUN1       = 0;
                    $modelo->RUN2       = 0;
                    $modelo->save();
                    $colorCount++;
                }    
            }

            # 2. Crear producto temporal
            
            if($colorCount>0){
                $target = $prodTmp;
                $target->get(array("estilo"=>"0", "linea"=>"0"));
                $target->set_attr("estilo", $estilo);
                $target->set_attr("linea", $linea);
                $target->set_attr("codigo_origen", $productInfo->{"codigo_origen"});
                $target->set_attr("descripcion", $productInfo->{"descripcion"});
                $target->set_attr("proveedor", $productInfo->{"proveedor"});
                $target->set_attr("catalogo", $productInfo->{"catalogo"});
                $target->set_attr("n_pagina", $productInfo->{"n_pagina"});
                $target->set_attr("genero", $productInfo->{"genero"});
                $target->set_attr("marca", $productInfo->{"marca"});
                $target->set_attr("suela", $productInfo->{"suela"});
                $target->set_attr("tacon", $productInfo->{"tacon"});
                $target->set_attr("material", $productInfo->{"material"});
                $target->set_attr("grupo", $productInfo->{"grupo"});
                $target->set_attr("concepto", $productInfo->{"concepto"});
                $target->set_attr("propiedad", $productInfo->{"propiedad"});
                $target->set_attr("observacion", $productInfo->{"observacion"});
                $target->set_attr("fecha_ingreso", $productInfo->{"fecha_ingreso"});
                $target->set_attr("nota", $productInfo->{"nota"});
                $target->set_attr("visible", '0');
                $target->set_attr("garantia", $productInfo->{"dias_garantia"});
                $target->set_attr('numero_documento', $productInfo->{"documento"});
                $target->save();    
            }
            
            # 3. Guardar colores
            foreach($productInfo->{"colores"} as $color){
                $estilo  = $estilo;
                $linea   = $linea;
                $colorml->get(0);
                $param['estilo'] = $estilo;
                $param['color']  = $color;
                if(!$colorml->exists($param)){
                    $colorml->set_attr('color_estilo_producto', $estilo);
                    $colorml->set_attr('linea', $linea);
                    $colorml->set_attr('color', $color);
                    $colorml->force_save();
                }
            }
            
        }else{
            // actualiza
            // Si es temporal se actualizan los datos y se insertan los nuevos colores
            if($tmp){
                # 1. creacion tarjeta_costo
                foreach($productInfo->{"colores"} as $color){
                    $colorml->get(0);
                    $id = 0;
                    $param['estilo'] = $estilo;
                    $param['color']  = $color;
                    if($colorml->exists($param)){
                        $id = $modelo->get_id($productInfo->{"documento"}, $estilo, $color);
                    }else{
                        $colorml->set_attr('color_estilo_producto', $estilo);
                        $colorml->set_attr('linea', $linea);
                        $colorml->set_attr('color', $color);
                        $colorml->force_save();
                    }

                    $modelo->setVirtualId('id');  // se pierde id en alguna operacion       
                    $modelo->get($id);      
                    $modelo->CESTILO    = $estilo;
                    $modelo->LINEA      = $linea;
                    $modelo->CODORIGEN  = $productInfo->{"codigo_origen"};
                    $modelo->DESCRIP    = $productInfo->{"descripcion"};
                    $modelo->PROVEEDOR  = $productInfo->{"proveedor"};
                    $modelo->CATALOGO   = $productInfo->{"catalogo"};
                    $modelo->PAGINA     = $productInfo->{"n_pagina"};
                    $modelo->GENERO     = $productInfo->{"genero"};
                    $modelo->MARCA      = $productInfo->{"marca"};
                    $modelo->PROPIEDAD  = $productInfo->{"propiedad"};
                    $modelo->OBSERVACIO = $productInfo->{"observacion"};
                    $modelo->FEINGRESA  = $productInfo->{"fecha_ingreso"};
                    $modelo->NODOC      = $productInfo->{"documento"};
                    $modelo->save();

                    $target = $prodTmp;
                    $target->setVirtualId('estilo');
                    $target->get($estilo);
                    $target->set_attr("estilo", $estilo);
                    $target->set_attr("linea", $linea);
                    $target->set_attr("codigo_origen", $productInfo->{"codigo_origen"});
                    $target->set_attr("descripcion", $productInfo->{"descripcion"});
                    $target->set_attr("proveedor", $productInfo->{"proveedor"});
                    $target->set_attr("catalogo", $productInfo->{"catalogo"});
                    $target->set_attr("n_pagina", $productInfo->{"n_pagina"});
                    $target->set_attr("genero", $productInfo->{"genero"});
                    $target->set_attr("marca", $productInfo->{"marca"});
                    $target->set_attr("propiedad", $productInfo->{"propiedad"});
                    $target->set_attr("observacion", $productInfo->{"observacion"});
                    $target->set_attr("fecha_ingreso", $productInfo->{"fecha_ingreso"});
                    $target->set_attr("nota", $productInfo->{"nota"});
                    $target->set_attr("visible", '0');
                    $target->set_attr("garantia", $productInfo->{"dias_garantia"});
                    $target->set_attr('numero_documento', $productInfo->{"documento"});
                    $target->save();    
                }
            }else{

                $prodSys->setVirtualId('estilo');
                $prodTmp->setVirtualId('estilo');
                    
                $prodSys->get($estilo);
                    
                $fields = $prodSys->get_fields();
                    
                $prodTmp->get(0);

                foreach ($fields as $field) {
                    $prodTmp->set_attr($field, $prodSys->get_attr($field));
                }

                $prodTmp->set_attr("visible", '0');
                $prodTmp->set_attr('numero_documento', $productInfo->{"documento"});

                $prodTmp->save();

                foreach($productInfo->{"colores"} as $color){
                    $param['estilo'] = $estilo;
                    $param['color']  = $color;
                    if((!$colortl->exists($param)) && (!$colorml->exists($param))){
                        $colorml->set_attr('color_estilo_producto', $estilo);
                        $colorml->set_attr('linea', $linea);
                        $colorml->set_attr('color', $color);
                        $colorml->force_save();
                    } 
                }
            }
        }
        
        echo json_encode($response);
    }

    public function cargar_color_producto() {
        header('Content-type:text/javascript;charset=UTF-8');
        if (isset($_POST['producto']) && !empty($_POST['producto'])):
            $producto = $_POST['producto'];
            $producto = addslashes($producto);
            $json = json_decode(stripslashes($_POST["_gt_json"]));
            $pageNo = $json->{'pageInfo'}->{'pageNum'};
            $pageSize = 10; //10 rows per page
            //to get how many records totally.
            $sql = "select count(*) as cnt from color_producto where color_estilo_producto=$producto";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $row = mysqli_fetch_object($handle);
            $totalRec = $row->cnt;

            //make sure pageNo is inbound
            if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
                $pageNo = 1;
            endif;

            if ($json->{'action'} == 'load'):
                $sql = "select * from color_producto where color_estilo_producto = $producto limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function cargarNoOfer() {
        header('Content-type:text/javascript;charset=UTF-8');

        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "SELECT count(*) as cnt FROM estado_bodega WHERE bodega = 1 OR bodega = 2";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "SELECT * FROM estado_bodega WHERE bodega = 1 OR bodega = 2 limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    /**
     * Esta funcion es llamada para comprobar si un estilo ya existe. Esta comprobacion se hace antes
     * de permitir al usuario crear un nuevo estilo ya que no puede ingresarse un estilo ya existente.
     * Si el estilo ya existe se cargan los datos para que el usuario los pueda obervar, sin embargo, 
     * la edicion de los mismo no es posible ya que existe un modulo especial para dicha tarea. 
     */
    public function comprobar_estilo() {
        $resp   = array();            #arreglo de respuesta al cliente
        $estilo = $_POST['estilo'];   #estilo enviado como parametro
        $doc    = $_POST['doc'];      #numero de documento actual
        $fecha  = date("yyyy-mm-dd"); #fecha actual
        
        // se cargan dos modelos. El temporal (para documentos) y el operativo a modo
        // de verificar la existencia del producto y evitar duplicados con otros documentos
        $prod   = $this->model->get_child('producto');
        $temp   = $this->model->get_child('documento_producto');
        
        // se fuerza al estilo como identificador
        $temp->setVirtualId('estilo');
        $prod->setVirtualId('estilo');
        
        // se verifica si existe en alguna tabla
        $flg1 = $temp->exists($estilo);
        $flg2 = $prod->exists($estilo);

        $resp['data']  = null;              # preparacion del arreglo con datos
        $resp['found'] = ($flg1 || $flg2);  # existe en al menos una tabla (lo ideal es que sea exactamente en una)
        $resp['temp']  = ($flg1 || $flg2) && !$flg2;
        $resp['auth']  = true;              # autorizado a cambios en documento
        $resp['ref_doc'] = -1;

        
        // si se encuentra se recogen los datos
        if ($resp['found']) {
            // target es la tabla donde se ha encontrado el producto
            $target = ($flg1) ? $temp : $prod;
            $fields = $target->get_fields(); # se obtiene los atributos del modelo
            $target->get(array("estilo"=>$estilo, "linea"=>$_POST['linea']));           # se carga el modelo con los datos del producto
            // se prepata la respuesta para el cliente
            foreach ($fields as $key) {
                $resp['data'][$key] = $target->$key;
            }
            
            
            // en esta seccion se obtiene los colores asociados al producto, tanto nombre como identificador
            $colorMod = null;
            
            

            $colorMod = $this->model->get_child('documento_color_producto'); 
            $colorsT  = $colorMod->get_colors($estilo);  
            $colorMod = $this->model->get_child('color_producto');
            $colorsS  = $colorMod->get_colors($estilo);
            $colorsF = array();

            foreach ($colorsT as $color) {
                if(!in_array($color, $colorsF) ){
                    $colorsF[] = $color;
                }
            }

            foreach ($colorsS as $color) {
                if(!in_array($color, $colorsF) ){
                    $colorsF[] = $color;
                }
            }

            $resp['data']['colors'] =  $colorsF;

            if($flg1){

                $aux = $target->getDocument($estilo);
                $resp['auth']  = ($doc ==  $aux);
                $resp['ref_doc'] = $aux;
            }
            
        }

        echo json_encode($resp);
    }

    public function replicar() {
        $estilo = $_POST['estilo'];
        $doc = $_POST['doc'];
        $model = $this->model->get_child('producto');
        $copy = $this->model->get_child('documento_producto');
        $model->get($estilo);
        $copy->get(0);
        $data = array();
        $fields = $model->get_fields();
        foreach ($fields as $field) {
            $data[$field] = $model->get_attr($field);
        }
        $data['visible'] = 1;
        $data['numero_documento'] = $doc;
        $copy->change_status($data);
        $copy->save();
    }

    public function verificarTotales() {

        $total_cliente = $_POST['total_cliente'];
        $pares_cliente = $_POST['pares_cliente'];
        $referencia = $_POST['referencia'];

        $ret = $this->model->verificarTotales($referencia, $total_cliente, $pares_cliente);

        echo json_encode($ret);
    }

    public function consigna() {
        $this->validar();
        $cache = array();
        $cache[0] = $this->model->get_child('bodega')->get_list();
        $cache[1] = $this->model->get_child('proveedor')->get_list();
        $cache[2] = $this->model->get_child('proveedor')->get_list();
        $this->view->consigna(Session::singleton()->getUser(), $cache);
    }

    public function salvar_consigna() {
        # 1C por defecto
        $tc = $this->model->get_child('traslado');
        $data = $_GET;

        $fecha = explode("/", $data['fecha']);

        if (!checkdate($fecha[1], $fecha[0], $fecha[2]) || count($fecha) != 3)
            $data['fecha'] = date("Y-m-d");
        else {

            $tmp = $fecha[0];
            $fecha[0] = $fecha[2];
            $fecha[2] = $tmp;

            $data['fecha'] = implode("-", $fecha);
        }

        $id_bodega_ = $data['bodega_origen'];
        $id_bodega = $data['bodega_destino'];
        $nombre_bodega = $this->model->obtenerBodega($id_bodega);
        $nombre_bodega_ = $this->model->obtenerBodega($id_bodega_);

        $data['usuario'] = Session::singleton()->getUser();
        $data['transaccion'] = '1C';
        $data['consigna'] = 1;
        $data['editable'] = 1;

        $tc->get(0);
        $tc->change_status($data);
        $tc->save();

        if (true) {
            $id = $tc->last_insert_id();
        }

        $oCliente = $this->model->get_sibling('cliente');
        list($credito, $usado, $saldo) = $oCliente->credito_cliente($data['cliente']);

        /* import('scripts.Request');
          $request = new Request();
          $request->addParam('id',$id);
          $request->addParam('id_bodega',$id_bodega);
          $request->addParam('nombre_bodega',$nombre_bodega);
          $request->addParam('id_bodega_',$id_bodega_);
          $request->addParam('nombre_bodega_',$nombre_bodega_);
          $request->addParam('transaccion',$data['transaccion']);
          $request->addParam('credito',$credito);
          $request->addParam('usado',$usado);
          $request->addParam('saldo',$saldo);
          $request->addParam('consigna','true');
         */
        HttpHandler::redirect('/'.MODULE.'/inventario/detalle_traslado?id=' . $id);
    }

    public function bodegaCliente() {
        $id = $_POST['cliente'];
        $this->model->bodegaCliente($id);
    }

    public function trasladoEditable() {
        echo $this->model->trasladoEditable($_POST['ref']);
    }

    public function establecer_datos_traslado() {
        $ref = $_POST['ref'];

        echo json_encode($this->model->establecer_datos_traslado($ref));
    }

    public function establecer_datos_consigna() {
        $ref = $_POST['cliente'];

        echo json_encode($this->model->establecer_datos_consigna($ref));
    }

    public function carga_traslado() {
        $id             = $_POST['cod'];
        $transaccion    = $_POST['transaccion'];
        $query = "SELECT * FROM traslado WHERE cod = $id AND transaccion='{$transaccion}' ";
        data_model()->executeQuery($query);
        $data = data_model()->getResult()->fetch_assoc();
        if (data_model()->getNumRows() > 0) {
            $data['status'] = "FOUND";
        } else {
            $data['status'] = "NOTFOUND";
        }
        echo json_encode($data);
    }

    public function salvar_traslado_detalle() {
        $json = json_decode(stripslashes($_POST["productos"]));
        $ret = array();
        $ret["error"] = false;

        foreach ($json as $item) {
            $estilo = $item->{"estilo"};
            $linea = $item->{"linea"};
            $color = $item->{"color"};
            $talla = $item->{"talla"};
            $cantidad = $item->{"cantidad"};
            $consigna = $item->{"consigna"};
            $costo = $item->{"costo"};
            $val = false;

            /* CAMBIO DE COSTO */
            // YA NO SE PERMITE CAMBIO DE COSTO, ES UNICAMENTE MOMENTANEO
            //$this->model->get_child('control_precio')->cambiar_costo($linea, $estilo, $color, $talla, $costo);

            /* COSTO */

            $data = array();

            $data['estilo'] = $item->{"estilo"};
            $data['linea'] = $item->{"linea"};
            $data['color'] = $item->{"color"};
            $data['talla'] = $item->{"talla"};
            $data['cantidad'] = $item->{"cantidad"};
            $data['costo'] = $item->{"costo"};
            $data['total'] = $item->{"total"};
            $data['id_ref'] = $item->{"id_ref"};
            $dt = $this->model->get_child('detalle_traslado');

            if ($consigna == 0) {
                //$this->model->suplir_orden( $estilo, $linea, $color, $talla, $cantidad );
                $val = $this->model->get_child('traslado')->es_valido($data['cantidad'], $data['total'], $data['id_ref']);
            } else {
                $val = $this->model->get_sibling('cliente')->es_valido($data['id_ref'], $data['total']);
            }

            if ($val) {

                $existe = $this->model->get_child('detalle_traslado')->existe($estilo, $linea, $color, $talla, $data['id_ref']);
                if (!$existe) {
                    $dt->get(0);
                    $dt->change_status($data);
                    $dt->save();
                } else {
                    $this->model->get_child('detalle_traslado')->actualizar($estilo, $linea, $color, $talla, $cantidad, $data['total'], $data['id_ref']);
                }
                $this->model->get_child('traslado')->actualizar($data['cantidad'], $data['total'], $data['id_ref']);
                if ($consigna == 1) {
                    $this->model->get_sibling('cliente')->actualizar_saldo($data['id_ref'], $data['total']);
                    $this->model->get_child('traslado')->actualizar2($data['cantidad'], $data['total'], $data['id_ref']);
                }
            } else {
                $ret['error'] = true;
            }
        }
        echo json_encode($ret);
    }

    public function listadoTraslado() {
        $this->validar();
        $this->view->listadoTraslado(Session::singleton()->getUser());
    }

    public function listadoConsigna() {
        $this->validar();
        $this->view->listadoConsigna(Session::singleton()->getUser());
    }

    public function transaccionCompra() {
        $ret            = array();
        $id_ref         = $_POST['id_ref'];
        $transaccion    = $_POST['transaccion'];
        $bodega_destino = $_POST['bodega_destino'];

        if ($transaccion == '1B') {
            $ret['tran'] = "success";
            $ret['of']   = $this->model->ingresoCompra($id_ref, $bodega_destino);
        } else {
            $ret['of']   = false;
            $ret['tran'] = "error";
        }
        echo json_encode($ret);
    }

    public function ingresos() {
        $ret = array();
        $id_ref = $_POST['id_ref'];
        $transaccion = $_POST['transaccion'];
        $bodega_origen = $_POST['bodega_origen'];
        $bodega_destino = $_POST['bodega_destino'];

        if ($transaccion == '1D' || $transaccion == '1E') {
            $ret['tran'] = "success";
            $ret['out'] = $this->model->ingresos($id_ref, $bodega_origen, $bodega_destino);
        } else {
            $ret['out'] = false;
            $ret['tran'] = "error";
        }

        echo json_encode($ret);
    }

    public function salidas() {
        $ret = array();
        $id_ref         = $_POST['id_ref'];
        $transaccion    = $_POST['transaccion'];
        $bodega_origen  = $_POST['bodega_origen'];
        $bodega_destino = $_POST['bodega_destino'];

        if ($transaccion == '2B' || $transaccion == '2E') {
            $ret['tran'] = "success";
            $ret['out'] = $this->model->salidas($id_ref, $bodega_origen, $bodega_destino);
        } else {
            $ret['out'] = false;
            $ret['tran'] = "error";
        }

        echo json_encode($ret);
    }

    public function imprimirTraslado($id) {
        $cache = array();
        $this->validar();
        $cache[0] = $this->model->reporteTraslado($id);
        $ta = $this->model->get_child('traslado');
        $ta->get($id);
        $fecha = $ta->get_attr('fecha');
        $transaccion = $ta->get_attr('transaccion');
        $concepto = $ta->get_attr('concepto');
        $total_pares = $ta->get_attr('total_pares');
        $total_costo = $ta->get_attr('total_costo');
        $ntdoc = $ta->get_attr('cod');
        $nt = $this->model->nombre_transaccion($transaccion);
        $this->view->imprimirTraslado($id, Session::singleton()->getUser(), $cache, $transaccion, $nt, $fecha, $concepto, $total_pares, $total_costo, $ntdoc);
    }

    public function imprimirConsigna($id, $tipo) {
        $this->validar();
        if ($tipo == 1) {
            $cache = array();
            $cache[0] = $this->model->reporteTraslado($id);
            $ta = $this->model->get_child('traslado');
            $ta->get($id);
            $fecha = $ta->get_attr('fecha');
            $transaccion = $ta->get_attr('transaccion');
            $concepto = $ta->get_attr('concepto');
            $total_pares = $ta->get_attr('total_pares');
            $total_costo = $ta->get_attr('total_costo');
            $nt = $this->model->nombre_transaccion($transaccion);
            $this->view->imprimirTraslado($id, Session::singleton()->getUser(), $cache, $transaccion, $nt, $fecha, $concepto, $total_pares, $total_costo);
        } else {
            $cache = array();
            $cache[0] = $this->model->reporteTraslado($id);
            $ta = $this->model->get_child('traslado');
            $ta->get($id);
            $fecha = $ta->get_attr('fecha');
            $transaccion = '2B';
            $concepto = $ta->get_attr('concepto_alternativo');
            $total_pares = $ta->get_attr('total_pares');
            $total_costo = $ta->get_attr('total_costo');
            $nt = $this->model->nombre_transaccion($transaccion);
            $this->view->imprimirTraslado($id, Session::singleton()->getUser(), $cache, $transaccion, $nt, $fecha, $concepto, $total_pares, $total_costo);
        }
    }

    public function transaccionLibre() {
        $ret            = array();
        $id_ref         = $_POST['id_ref'];
        $transaccion    = $_POST['transaccion'];
        $bodega_origen  = $_POST['bodega_origen'];
        $bodega_destino = $_POST['bodega_destino'];

        if ($transaccion == '1C' || $transaccion == '2C') {
            $ret['tran'] = "success";
            $ret['out']  = $this->model->transaccionLibre($id_ref, $bodega_origen, $bodega_destino, $transaccion);
        } else {
            $ret['out'] = false;
            $ret['tran'] = "error";
        }

        echo json_encode($ret);
    }

    public function borrarOferta() {
        $linea  = $_POST['linea'];
        $estilo = $_POST['estilo'];
        $color  = $_POST['color'];
        $talla  = $_POST['talla'];

        $this->model->borrarOferta($linea, $estilo, $color, $talla);
    }

    public function eliminar_oferta($id_oferta) {
        $this->model->get_child('oferta')->delete($id_oferta);
        $query = "SELECT * FROM oferta_producto WHERE id_oferta = $id_oferta";
        data_model()->executeQuery($query);
        $items = array();
        while ($row = data_model()->getResult()->fetch_assoc()) {
            $items[] = $row;
        }

        foreach ($items as $item) {
            $estilo = $item['estilo'];
            $linea  = $item['linea'];
            $color  = $item['color'];
            $talla  = $item['talla'];
            $query  = "UPDATE estado_bodega SET bodega = 1 WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla";
            data_model()->executeQuery($query);
        }

        $query = "DELETE FROM oferta_producto WHERE id_oferta = $id_oferta";
        data_model()->executeQuery($query);
        HttpHandler::redirect('/'.MODULE.'/inventario/ofertas');
    }

    public function get_detalle_data_traslado() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $query = "SELECT proveedor AS id_proveedor, proveedor.nombre AS nombre_proveedor,catalogo AS id_catalogo,catalogo.nombre AS nombre_catalogo,n_pagina,margen_usual FROM producto join proveedor ON proveedor=proveedor.id join catalogo WHERE estilo='{$estilo}' AND linea=$linea";
        data_model()->executeQuery($query);
        $dat = data_model()->getResult()->fetch_assoc();
        echo json_encode($dat);
    }

    public function cargar_consulta_producto() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page

        $filtros   = $_POST['filtros'];
       
        $temp = explode(',', $filtros);
        $filtros = array();
        foreach ($temp as $parts) {
            $tt = explode(':', $parts);
            $filtros[$tt[0]] = $tt[1];
        }

        /* CADENA DE CONDICION */
        //*/
        $fin = " WHERE precio > 0 AND ";
        $keys = array_keys($filtros);
        $values = array_values($filtros);
        $str_ct = implode(' = \'$\' ? ', $keys);
        $str_ct.= ' = \'$\' ';
        $art = explode('?', $str_ct);
        for ($i = 0; $i < count($art); $i++):
            $art[$i] = str_replace('$', $values[$i], $art[$i]);
        endfor;
        $fin .= implode(' AND ', $art);

        //*/  
        //to get how many records totally.
        $sql = "select count(*) as cnt from control_precio left join estado_bodega on control_estilo=estilo AND estado_bodega.linea = control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla join producto on control_precio.linea = producto.linea AND control_precio.control_estilo = producto.estilo  " . $fin;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select control_estilo as estilo,control_precio.linea as linea,control_precio.color as color,control_precio.talla as talla,precio,costo,stock,bodega from control_precio left join estado_bodega on control_estilo=estilo AND estado_bodega.linea = control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla join producto on control_precio.linea = producto.linea AND control_precio.control_estilo = producto.estilo " . $fin . " limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            
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

    public function cargar_detalle_traslado() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page

        $filtros   = $_POST['filtros'];
        $proveedor = $_POST['proveedor'];
        $temp = explode(',', $filtros);
        $filtros = array();
        foreach ($temp as $parts) {
            $tt = explode(':', $parts);
            $filtros[$tt[0]] = $tt[1];
        }

        /* CADENA DE CONDICION */
        //*/
        $fin = " WHERE precio > 0 AND producto.proveedor = $proveedor AND ";
        $keys = array_keys($filtros);
        $values = array_values($filtros);
        $str_ct = implode(' = \'$\' ? ', $keys);
        $str_ct.= ' = \'$\' ';
        $art = explode('?', $str_ct);
        for ($i = 0; $i < count($art); $i++):
            $art[$i] = str_replace('$', $values[$i], $art[$i]);
        endfor;
        $fin .= implode(' AND ', $art);

        //*/  
        //to get how many records totally.
        $sql = "select count(*) as cnt from control_precio left join estado_bodega on control_estilo=estilo AND estado_bodega.linea = control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla join producto on control_precio.linea = producto.linea AND control_precio.control_estilo = producto.estilo  " . $fin;
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select control_estilo as estilo,control_precio.linea as linea,control_precio.color as color,control_precio.talla as talla,precio,costo,stock,bodega from control_precio left join estado_bodega on control_estilo=estilo AND estado_bodega.linea = control_precio.linea AND estado_bodega.color = control_precio.color AND estado_bodega.talla = control_precio.talla join producto on control_precio.linea = producto.linea AND control_precio.control_estilo = producto.estilo " . $fin . " limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
            
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

    public function cargar_color_producto_doc() {
        header('Content-type:text/javascript;charset=UTF-8');
        if (isset($_POST['producto']) && !empty($_POST['producto'])):
            $producto = $_POST['producto'];
            $producto = addslashes($producto);
            $json = json_decode(stripslashes($_POST["_gt_json"]));
            $pageNo = $json->{'pageInfo'}->{'pageNum'};
            $pageSize = 10; //10 rows per page
            //to get how many records totally.
            $sql = "select count(*) as cnt from documento_color_producto where color_estilo_producto=$producto";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $row = mysqli_fetch_object($handle);
            $totalRec = $row->cnt;

            //make sure pageNo is inbound
            if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
                $pageNo = 1;
            endif;

            if ($json->{'action'} == 'load'):
                $sql = "select * from documento_color_producto where color_estilo_producto = $producto limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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
        else:
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
    }

    public function guardar_color_producto_doc() {
        header('Content-type:text/javascript;charset=UTF-8');
        $producto = $_POST['producto'];
        $producto = addslashes($producto);
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        if ($json->{'action'} == 'save'):
            $sql = "";
            $params = array();
            $errors = "";

            //deal with those deleted
            $deletedRecords = $json->{'deletedRecords'};
            foreach ($deletedRecords as $value):
                $sql = "delete from documento_color_producto where color_estilo_producto = $producto and color = $value->color ";
                data_model()->executeQuery($sql);
            endforeach;

            //deal with those inserted
            $insertedRecords = $json->{'insertedRecords'};
            foreach ($insertedRecords as $value):
                $sql = "insert into documento_color_producto(color_estilo_producto,color) values ($producto,$value->color)";
                data_model()->executeQuery($sql);
            endforeach;

            $ret = "{success : true,exception:''}";
            echo $ret;
        endif;
    }

    public function cargar() {
        $tblname = $_POST['tblname'];
        _loaddata($tblname);
    }

    public function cargar_con_filtro() {
        $tblname = $_POST['tblname'];
        $filtros = $_POST['filtros'];
        $temp = explode(',', $filtros);
        $filtros = array();
        foreach ($temp as $parts) {
            $tt = explode(':', $parts);
            $filtros[$tt[0]] = $tt[1];
        }
        _loaddata_filter($tblname, $filtros);
    }

    public function salvar_precio() {
        $estilo = $_POST['estilo'];
        $linea = $_POST['linea'];
        $talla = $_POST['talla'];
        $color = $_POST['color'];
        $precio = $_POST['precio'];
        $documento = $_POST['documento'];
        $sql = "INSERT INTO control_precio_documento(control_estilo,linea,talla,color,precio,documento) VALUES($estilo,$linea,$talla,$color,$precio,$documento)";
        data_model()->executeQuery($sql);
        $h_data = array();
        $h_data['usuario'] = Session::getUser();
        $h_data['descripcion'] = "se modifico el precio del producto " . $estilo;
        $h_data['fecha_hora'] = date("y-m-d h:m:s");
        $h_data['modulo'] = "stock";
        $historial = $this->model->get_child('historial');
        $historial->get(0);
        $historial->change_status($h_data);
        $historial->save();
        HttpHandler::redirect('/'.MODULE.'/inventario/stock?doc=' . $documento);
    }

    public function facBodega() {
        if (isset($_POST) && !empty($_POST)):
            echo json_encode($this->model->existsBodega($_POST['bodega']));
        else:
            echo "ERROR! WRONG CALL";
        endif;
    }

    public function facLinea() {
        if (isset($_POST) && !empty($_POST)):
            echo json_encode($this->model->existsLinea($_POST['bodega'], $_POST['linea']));
        else:
            echo "ERROR! WRONG CALL";
        endif;
    }

    public function facEstilo() {
        if (isset($_POST) && !empty($_POST)):
            echo json_encode($this->model->existsEstilo($_POST['bodega'], $_POST['linea'], $_POST['estilo']));
        else:
            echo "ERROR! WRONG CALL";
        endif;
    }

    public function facColor() {
        if (isset($_POST) && !empty($_POST)):
            echo json_encode($this->model->existsColor($_POST['bodega'], $_POST['linea'], $_POST['estilo'], $_POST['color']));
        else:
            echo "ERROR! WRONG CALL";
        endif;
    }

    public function facTalla() {
        if (isset($_POST) && !empty($_POST)):
            echo json_encode($this->model->existsTalla($_POST['bodega'], $_POST['linea'], $_POST['estilo'], $_POST['color'], $_POST['talla']));
        else:
            echo "ERROR! WRONG CALL";
        endif;
    }

    public function salvarCambiosStock() {
        $doc = (isset($_GET['doc']) && !empty($_GET['doc'])) ? $_GET['doc'] : 0;
        $sql = "DELETE FROM control_precio_documento 
            WHERE CONCAT(control_estilo,linea,talla,color) IN 
            (SELECT CONCAT(control_estilo,linea,talla,color) as cod 
              FROM control_precio) AND documento=$doc;";

        data_model()->executeQuery($sql);
        $this->model->ClonarControlPrecio($doc);
        $this->model->ClonarEstadoBodega($doc);
        $close = "UPDATE documento SET estado=1 WHERE id_documento=$doc";
        data_model()->executeQuery($close);
        $deletePrecio = "DELETE FROM control_precio_documento WHERE documento=$doc;";
        $deleteEstado = "DELETE FROM estado_bodega_documento WHERE documento=$doc;";
        data_model()->executeQuery($deletePrecio);
        data_model()->executeQuery($deleteEstado);
        HttpHandler::redirect('/'.MODULE.'/inventario/stock_documentos?save=ok');
    }

    public function cargar_ce($estilo) {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from producto where estilo=$estilo";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;

        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from producto where estilo=$estilo limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargar_cf() {
        $tblname = $_POST['tblname'];
        $value   = $_POST['value'];
        $field   = $_POST['field'];
        _loaddata_cf($tblname, $field, $value);
    }

    public function cargar_cfd() {
        $tblname = $_POST['tblname'];
        $value = $_POST['value'];
        $field = $_POST['field'];
        _loaddata_cfd($tblname, $field, $value);
    }

    public function actualizar() {
        $tblname = $_POST['tblname'];
        _updatedata($this, $tblname);
    }

    public function actualizar_cd() {
        $tblname = $_POST['tblname'];
        $value = $_POST['value'];
        $field = $_POST['field'];
        _updatedata_cd($this, $tblname, $field, $value);
    }

    public function cargar_stock() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo join control_precio_documento on (estilo=control_estilo AND talla_producto.talla = control_precio_documento.talla AND talla_producto.color = control_precio_documento.color) WHERE CONCAT(estilo,producto.linea,talla_producto.talla,talla_producto.color) IN (SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio_documento ) OR CONCAT(estilo,producto.linea,talla_producto.talla,talla_producto.color) IN (SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio) group by talla_estilo_producto,talla_producto.talla;";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        //$row = mysqli_fetch_object($handle);
        $totalRec = mysqli_num_rows($handle);
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select estilo,talla_estilo_producto,precio,talla_producto.color,talla_producto.talla,proveedor,producto.linea,fecha_ingreso from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo join control_precio_documento on (estilo=control_estilo AND talla_producto.talla = control_precio_documento.talla AND talla_producto.color = control_precio_documento.color) WHERE CONCAT(estilo,producto.linea,talla_producto.talla,talla_producto.color) IN(SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio_documento ) OR CONCAT(estilo,producto.linea,talla_producto.talla,talla_producto.color) IN (SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio) group by talla_estilo_producto,talla_producto.talla limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargar_stock2() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from control_precio";
        //$handle = mysqli_query($sql);
        data_model()->executeQuery($sql);
        //$row = mysqli_fetch_object($handle);
        $sd = data_model()->getResult()->fetch_assoc();
        $totalRec = $sd['cnt'];
        //echo $totalRec."<br/><br/><br/>";
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from control_precio limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargar_stock2_con_filtro() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page

        $filtros = $_POST['filtros'];
        $temp = explode(',', $filtros);
        $filtros = array();
        foreach ($temp as $parts) {
            $tt = explode(':', $parts);
            $filtros[$tt[0]] = $tt[1];
        }

        $fin = " WHERE ";
        $talla = $_POST['talla'];
        $talla2 = $_POST['talla2'];
        $keys = array_keys($filtros);
        $values = array_values($filtros);
        $str_ct = implode(' = \'$\' ? ', $keys);
        $str_ct.= ' = \'$\' ';
        $art = explode('?', $str_ct);
        for ($i = 0; $i < count($art); $i++):
            $art[$i] = str_replace('$', $values[$i], $art[$i]);
        endfor;
        $fin .= implode(' AND ', $art);
        if (!empty($_POST['talla']) && !empty($_POST['talla2']))
            $fin .= " AND talla>=" . $talla . " AND talla<=" . $talla2 . " ";
        else if (!empty($_POST['talla']) && empty($_POST['talla2']))
            $fin .= " AND talla>=" . $talla . " ";
        else if (!empty($_POST['talla2']) && empty($_POST['talla']))
            $fin .= " AND talla<=" . $talla2 . " ";

        //to get how many records totally.
        $sql = "select count(*) as cnt from control_precio" . $fin;
        //$handle = mysqli_query($sql);
        data_model()->executeQuery($sql);
        //$row = mysqli_fetch_object($handle);
        $sd = data_model()->getResult()->fetch_assoc();
        $totalRec = $sd['cnt'];
        //echo $totalRec."<br/><br/><br/>";
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select * from control_precio" . $fin . " limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargar_precio() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo WHERE CONCAT(estilo,linea,talla,talla_producto.color) NOT IN(SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio_documento ) AND CONCAT(estilo,linea,talla,talla_producto.color) NOT IN(SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio) group by talla_estilo_producto,talla;";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        //$row = mysqli_fetch_object($handle);
        $totalRec = mysqli_num_rows($handle);
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select estilo,talla_estilo_producto,talla_producto.color,talla,proveedor,linea,fecha_ingreso from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo WHERE CONCAT(estilo,linea,talla,talla_producto.color) NOT IN(SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio_documento ) AND CONCAT(estilo,linea,talla,talla_producto.color) NOT IN(SELECT CONCAT(control_estilo,linea,talla,color) as cod FROM control_precio) group by talla_estilo_producto,talla limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function cargar_stock_ce($estilo = '') {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo where estilo=$estilo group by talla_estilo_producto,talla;";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        //$row = mysqli_fetch_object($handle);
        $totalRec = mysqli_num_rows($handle);
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;

        if ($json->{'action'} == 'load'):
            $sql = "select estilo,talla_estilo_producto,talla_producto.color,talla,proveedor,producto.linea,fecha_ingreso from talla_producto join color_producto on talla_estilo_producto=color_estilo_producto join producto on talla_estilo_producto=estilo where estilo=$estilo group by talla_estilo_producto,talla limit  " . ($pageNo - 1) * $pageSize . ", " . $pageSize;
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

    public function proveedorList() {
        //This is a php file for data feeding
        import('scripts.proveedorDAO');
        //To create grid exporting instant.
        $gridHandler = new GridServerHandler();
        $type = getParameter('exportType');
        if ($type == 'pdf') {
            // to use html2pdf to export pdf
            // param1 : Orientation. 'P' for Portrait , 'L' for Landscape
            // param2 : Paper size. Could be A3, A4, A5, LETTER, LEGAL
            // param3 : Relative picture path to this php file
            $header = "<h1>Negocios y Mas s.a de c.v</h1>";
            $header .= "<p><br/></p> <h3>Reporte de proveedores</h3>";
            $header .= "<hr/><p><br/></p>";
            $gridHandler->exportPDF('P', 'A4', '', $header);
        } else {
            //to get the data from data base. // 
            $data1 = getProveedorData();

            if ($type == 'xml') {
                //exporting to xml
                $gridHandler->exportXML($data1);
            } else if ($type == 'xls') {
                //exporting to xls
                $gridHandler->exportXLS($data1);
            } else if ($type == 'csv') {
                //exporting to csv
                $gridHandler->exportCSV($data1);
            } else {
                $data1 = getProveedorData();
                //for grid presentation
                $gridHandler->setData($data1);
                $gridHandler->setTotalRowNum(count($data1));
                $gridHandler->printLoadResponseText();
            }
        }
    }

}

?>