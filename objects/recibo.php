<?php

class reciboModel extends object {

    public function actualizar_saldos($id_recibo, $cliente) {
        $query = "SELECT abono,pedido,interes FROM detalle_recibo WHERE id_recibo=$id_recibo";
        data_model()->executeQuery($query);
        $res = array();
        while ($data = data_model()->getResult()->fetch_assoc()) {
            $res[] = $data;
        }

        $oCliente = $this->get_sibling('cliente');
        $oCliente->get($cliente);
        $ofactura = $this->get_sibling('factura');
        $ointeres = $this->get_child('interes');

        foreach ($res as $d) {
            if (!$d['interes']) {
                $ofactura->get($d['pedido']);
                $ofactura->set_attr('credito_pagada', true);
                $ofactura->save();
            } else {
                $ointeres->get($d['pedido']);
                $ointeres->set_attr('credito_pagada', true);
                $ointeres->save();
            }

            $oCliente->set_attr('credito_usado', $oCliente->get_attr('credito_usado') - $d['abono']);
            $oCliente->save();
        }
    }

}

?>