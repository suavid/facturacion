<?php

function bloqueo_pantalla() {
    $usuario = Session::singleton()->getUser();
    $query = "SELECT activo FROM empleado WHERE usuario = '{$usuario}'";
    data_model()->executeQuery($query);
    $ret = data_model()->getResult()->fetch_assoc();
    echo json_encode($ret);
    /*
      if($ret['activo'] == 0 && !empty($usuario)){
      echo "
      <div style=\"position:fixed;width:100%;height:100%;background:#000;top:0px;text-align:center;opacity:0.6\" class=\"metro\">
      <br/>
      <br/>
      <br/>
      <br/>
      <h1 style=\"color:#fff;\">Bloqueado</h1>
      <br/>
      <br/>
      <img src=\"../static/img/candado.png\" />
      <br/>
      <br/>
      <div class=\"input-control text\" style=\"background:#000;\">
      <input style=\"width:300px;border:#D3D3D3;padding:10px;\" type=\"password\" placeholder=\" Password\" />
      <button type=\"button\" class=\"large primary\">Desbloquear</button>
      </div>
      </div>
      ";
      }
     */
}

function CANCELAR_OFERTAS(){
   $ofertas = array();
   $query = "SELECT * FROM oferta_producto INNER JOIN oferta ON oferta_producto.id_oferta = oferta.id INNER JOIN estado_bodega ON (estado_bodega.linea = oferta_producto.linea AND estado_bodega.estilo = oferta_producto.estilo AND estado_bodega.talla = oferta_producto.talla AND estado_bodega.color = oferta_producto.color) WHERE fin = DATE_ADD(CURRENT_DATE(), INTERVAL -1 DAY) AND bodega = 3 AND vencida = 0 GROUP BY estado_bodega.linea, estado_bodega.estilo, estado_bodega.color, estado_bodega.talla";

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
      if(!in_array($item['id_oferta'], $ofertas)) $ofertas[] = $item['id_oferta'];
      $query  = "UPDATE estado_bodega SET bodega = 1 WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla";
      data_model()->executeQuery($query);
    }

    foreach ($ofertas as $oferta) {
      $query = "UPDATE oferta SET vencida = 1 WHERE id = $oferta";
      data_model()->executeQuery($query);
    }


}

function ANULAR_PEDIDOS_PENDIENTES(){
    import('scripts.alias');
    import('objects.bodega');
    
    $cls = "bodegaModel";
    $bodega = new $cls();
    
    // Obtener los pedidos de ayer que están pendientes y no han sido reservados 
    $query = "SELECT id_factura FROM factura WHERE fecha < CURRENT_DATE() AND facturado = 0 AND estado !='RESERVADO' AND (monto > 0 OR iva > 0 OR subtotal > 0 OR total > 0)";
    data_model()->executeQuery($query);
    $pedidosAnulables = array();
    
    while($row = data_model()->getResult()->fetch_assoc()){
        $pedidosAnulables[] = $row;
    }
    
    foreach($pedidosAnulables as $pedido){
        $id_factura = $pedido['id_factura'];
        $qdetalle = "SELECT id, linea, estilo, color, talla, bodega, cantidad FROM detalle_factura WHERE id_factura = $id_factura";
        
        data_model()->executeQuery($qdetalle);
        
        $detalle = array();
        while($row = data_model()->getResult()->fetch_assoc()){        
            $detalle = $row;
        }    
        
        foreach($detalle as $item){
            $bodega->aumentar_stock($item['linea'], $item['estilo'], $item['color'], $item['talla'], $item['bodega'], $item['cantidad']);
            $qdel = "DELETE FROM detalle_factura WHERE id = ".$item['id'];
            data_model()->executeQuery($qdel);
        }
        
        $qupdate = "UPDATE factura SET monto = 0, iva = 0, subtotal = 0, total = 0, descuento = 0 WHERE id_factura = $id_factura";
        data_model()->executeQuery($qupdate);
    }
    
}

function ANULAR_RESERVAS_VENCIDAS(){
    import('scripts.alias');
    import('objects.bodega');
    
    $cls = "bodegaModel";
    $bodega = new $cls();
    
    // Obtener los pedidos de ayer que están pendientes y no han sido reservados 
    $query = "SELECT id_factura FROM factura WHERE fecha <= DATE_ADD(CURRENT_DATE(), INTERVAL -3 DAY) AND facturado = 0 AND estado ='RESERVADO' AND (monto > 0 OR iva > 0 OR subtotal > 0 OR total > 0)";
    data_model()->executeQuery($query);
    $pedidosAnulables = array();
    
    while($row = data_model()->getResult()->fetch_assoc()){
        $pedidosAnulables[] = $row;
    }
    
    foreach($pedidosAnulables as $pedido){
        $id_factura = $pedido['id_factura'];
        $qdetalle = "SELECT id, linea, estilo, color, talla, bodega, cantidad FROM detalle_factura WHERE id_factura = $id_factura";
        
        data_model()->executeQuery($qdetalle);
        
        $detalle = array();
        while($row = data_model()->getResult()->fetch_assoc()){        
            $detalle = $row;
        }    
        
        foreach($detalle as $item){
            $bodega->aumentar_stock($item['linea'], $item['estilo'], $item['color'], $item['talla'], $item['bodega'], $item['cantidad']);
            $qdel = "DELETE FROM detalle_factura WHERE id = ".$item['id'];
            data_model()->executeQuery($qdel);
        }
        
        $qupdate = "UPDATE factura SET monto = 0, iva = 0, subtotal = 0, total = 0,descuento = 0, estado='PENDIENTE' WHERE id_factura = $id_factura";
        data_model()->executeQuery($qupdate);
    }
}

function GENERAR_INTERESES() {
    import('scripts.alias');

    /* consulta factura pendientes de pagar */
    $query = " select * from factura WHERE estado='FACTURADO' AND tipo='CREDITO' AND saldo>0.0 AND credito_pagada=0";
    data_model()->executeQuery($query);
    $facturas = array();
    while ($res = data_model()->getResult()->fetch_assoc()) {
        $facturas[] = $res;
    }

    /* cargar los recursos necesarios para manejar intereses */
    import("mdl.model.factura");
    $cls = "facturaModel";
    $oFactura = new $cls();

    import("mdl.model.cliente");
    $cls = "clienteModel";
    $oCliente = new $cls();

    import("objects.interes");
    $cls = "interesModel";
    $oInteres = new $cls();

    foreach ($facturas as $factura) {
        $oFactura->get($factura['id_factura']);
        $oCliente->get($factura['id_cliente']);

        $fecha_factura = $oFactura->get_attr('fecha');    # obtener fecha de factura 
        $ic = $oFactura->get_attr('intereses_cargados') + 1;  # cargar el interes
        $dias_credito = $oCliente->get_attr('dias_credito');   # obtener los dias de credito
        $fecha_vence = sumar_dias_habiles($fecha_factura, $ic * $dias_credito); # nueva fecha de vencimiento (fecha actual + interes_cargado * dias_credito)
        $query = "SELECT CURDATE() > '$fecha_vence' as vencido";  # verificar si la nueva fecha de vencimiento ha pasado
        data_model()->executeQuery($query);
        $fs = data_model()->getResult()->fetch_assoc();

        /* si ya se ha vencido */
        if ($fs['vencido'] == 1) {
            $factura['referencia'] = $factura['id_factura'];  # factura a la que apunta el interes
            $factura['id_factura'] = 0;        # 0 = nuevo interes
            $factura['fecha'] = date("Y-m-d");    # fecha actual (cuando se carga el interes)
            $factura['fecha_vence'] = sumar_dias_habiles($factura['fecha'], $oCliente->get_attr('dias_credito'));  # se establece la fecha de vencimiento del interes
            $factura['subtotal'] = $factura['total'] = (($oFactura->get_attr('saldo')) * 0.05); # el total es igual al subtotal y esto es igual al 5% del saldo pendiente de la factura
            $factura['descuento'] = 0.0;       # un interes no tiene descuento
            $factura['tipo'] = 'INTERES';     # tipo de documento
            $factura['estado'] = 'PENDIENTE';     # el estado inicial es pendiente de pagar
            $factura['flete'] = 0;       # intereses no tienen flete
            $factura['saldo'] = $factura['total'];   # el saldo inicial es el total que se debe
            $factura['cobro'] = 0.0;       # inicialmente no se ha cobrado nada
            $oInteres->get(0);          # se ha creado el interes
            $oCliente->set_attr('credito_usado', $oCliente->get_attr('credito_usado') + $factura['total']); # cargamos los intereses a la cuenta del usuario
            $oCliente->save();
            $oInteres->change_status($factura);
            $oInteres->save();
            $oFactura->set_attr('intereses_cargados', $ic);   # actualizamos los interes cargados a la factura
            $oFactura->save();
        }
    }
}

?>