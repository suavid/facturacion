<?php

class nota_creditoModel extends object {

    public function actualizar_saldos($id_nota_credito, $cliente) {
        $query = "SELECT abono,pedido,interes FROM detalle_nota_credito WHERE id_nota_credito=$id_nota_credito";
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
                if (!($ofactura->get_attr('saldo') > 0.0)) {
                    $ofactura->set_attr('credito_pagada', true);
                }
                $ofactura->save();
            } else {
                $ointeres->get($d['pedido']);
                if (!($ointeres->get_attr('saldo') > 0.0)) {
                    $ointeres->set_attr('credito_pagada', true);
                }
                $ointeres->save();
            }

            $oCliente->set_attr('credito_usado', $oCliente->get_attr('credito_usado') - $d['abono']);
            $oCliente->save();
        }
    }

}

?>