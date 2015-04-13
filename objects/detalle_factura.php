<?php

class detalle_facturaModel extends object {

    public function anular_pedido_total($id) {
        /* consulta para obtener la cantidad de producto entrante */
        $query = "SELECT cantidad,bodega,linea,estilo,color,talla,importe,descuento  FROM detalle_factura WHERE id_factura=$id";
        data_model()->executeQuery($query);
        $buffer_mem = array();

        // almacenar el producto entrante en un array
        while ($temp = data_model()->getResult()->fetch_assoc()) {
            $buffer_mem[] = $temp;
        }

        // obtener el cliente de la factura
        $fac = $this->get_sibling('factura');
        $fac->get($id);
        $id_cliente = $fac->get_attr('id_cliente');

        // obtener objeto del cliente para manipulacion de cuenta
        $cliente = $this->get_sibling('cliente');
        $cliente->get($id_cliente);

        // por cada elemento almacenado en el array (memoria temporal)
        foreach ($buffer_mem as $item) {
            $linea = $item['linea'];
            $estilo = $item['estilo'];
            $color = $item['color'];
            $talla = $item['talla'];
            $bodega = $item['bodega'];
            $cantidad = $item['cantidad'];

            // abonamos el total que le fue cargado en la cuenta al usuario
            $cliente->abonar($item['importe'] + $item['descuento']);
            $cliente->save();

            // aumentamos el stock del producto respectivo

            $inv = $this->get_child('bodega');
            $inv->aumentar_stock($linea, $estilo, $color, $talla, $bodega, $cantidad);
        }
    }

    public function anular_pedido_parcial($id) {
        /* consulta para obtener la cantidad de producto entrante */
        $query = "SELECT entran,bodega,linea,estilo,color,talla,importe,descuento,precio,cantidad FROM detalle_factura WHERE id_factura=$id";
        data_model()->executeQuery($query);
        $buffer_mem = array();

        // almacenar el producto entrante en un array
        while ($temp = data_model()->getResult()->fetch_assoc()) {
            $buffer_mem[] = $temp;
        }

        // obtener el cliente de la factura
        $fac = $this->get_sibling('factura');
        $fac->get($id);
        $id_cliente = $fac->get_attr('id_cliente');

        // obtener objeto del cliente para manipulacion de cuenta
        $cliente = $this->get_sibling('cliente');
        $cliente->get($id_cliente);

        // por cada elemento almacenado en el array (memoria temporal)
        foreach ($buffer_mem as $item) {
            $linea = $item['linea'];
            $estilo = $item['estilo'];
            $color = $item['color'];
            $talla = $item['talla'];
            $bodega = $item['bodega'];
            $cantidad = $item['entran'];

            $abono = $item['precio'] * $cantidad;
            $descuento = ($item['descuento'] / $item['cantidad']) * $cantidad;

            // abonamos el total que le fue cargado en la cuenta al usuario
            $cliente->abonar($abono + $descuento);
            $cliente->save();

            // aumentamos el stock del producto respectivo
            $inv = $this->get_child('bodega');
            $inv->aumentar_stock($linea, $estilo, $color, $talla, $bodega, $cantidad);
        }
    }

    public function facturar_diferencia($pedido, $cliente, $caja, $periodo_actual) {
        $fecha = date("Y-m-d");
        $f_data = array();
        $f_data['id_cliente'] = $cliente;
        $f_data['fecha'] = $fecha;
        $f_data['caja'] = $caja;
        $f_data['estado'] = "PENDIENTE";

        $oCliente = $this->get_sibling('cliente');
        $oCliente->get($cliente);

        $f_data['periodo_actual'] = $periodo_actual;

        $oPedido = $this->get_sibling('factura');

        $oPedido->get(0);
        $oPedido->change_status($f_data);
        $oPedido->save(); // se ha creado el pedido

        $npedido = $oPedido->last_insert_id(); // se recupera el pedido



        /* estas acciones son seguras porque solo un usuario puede usar una caja al mismo tiempo  */
        $p = array();
        $p['caja'] = $caja;
        $p['pedido'] = $oPedido->crear_pedido($caja); // se reserva el numero de pedido para la caja
        $p['referencia'] = $npedido;
        $pd = $p['pedido'];

        $oj = $this->get_child('caja_pedido_referencia');
        $oj->get(0);
        $oj->change_status($p);
        $oj->save();  // se crea el pedido por caja

        /* proceso de agregar detalles  */

        $query = "SELECT * FROM detalle_factura WHERE id_factura=$pedido";
        data_model()->executeQuery($query);
        $buffer_mem = array();
        while ($temp = data_model()->getResult()->fetch_assoc()) {
            $buffer_mem[] = $temp;
        }

        foreach ($buffer_mem as $item) {
            $cantidad = $item['cantidad'];
            $item['cantidad'] = $item['cantidad'] - $item['entran'];
            $item['entran'] = 0;
            $item['precio'] = -1 * $this->get_child('control_precio')->consultar_precio($item['linea'], $item['estilo'], $item['color'], $item['talla']);
            $item['importe'] = $item['cantidad'] * $item['precio'];
            $item['descuento'] = $item['importe'] * ($item['porcentaje'] / 100) * -1;
            $item['id_factura'] = $npedido;
            $item['id'] = 0; // reiniciar id para evitar sobreescritura

            if ($item['cantidad'] > 0):
                $this->get(0);
                $this->change_status($item);
                $this->save();

                // abono a la cuenta del cliente
                $abono = $item['cantidad'] * $item['precio'];
                $descuento = ($item['descuento'] / $cantidad) * $item['cantidad'];
                $oCliente->abonar($abono + $descuento);

                /* actualizacion de datos de la factura  */
                $oPedido->get($npedido);
                $oPedido->set_attr('subtotal', $oPedido->get_attr('subtotal') + $item['importe']);
                $oPedido->set_attr('descuento', $oPedido->get_attr('descuento') + $item['descuento']);
                $oPedido->set_attr('total', $oPedido->get_attr('subtotal') + $oPedido->get_attr('descuento'));
                $oPedido->save();
                $oCliente->save();
            endif;
        }

        return $pd;
    }

    public function total_facturable($pedido) {
        /* selecciona extraidos y entrantes por cada entrada del detalle */
        $query = "SELECT cantidad,entran FROM detalle_factura WHERE id_factura=$pedido";
        data_model()->executeQuery($query);

        $buffer_mem = array();
        $totl = 0;

        // almacenamiento en memoria de los registros
        while ($temp = data_model()->getResult()->fetch_assoc()) {
            $buffer_mem[] = $temp;
        }

        // calculo de totales
        foreach ($buffer_mem as $item) {
            $totl += ($item['cantidad'] - $item['entran']);
        }

        return $totl;
    }

}

?>