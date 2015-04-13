<?php

class caja_pedido_referenciaModel extends object {
    public function obtener_referencia($caja, $pedido){
    	$query = "SELECT referencia FROM caja_pedido_referencia WHERE caja=$caja AND pedido=$pedido";
    	data_model()->executeQuery($query);
    	$res = data_model()->getResult()->fetch_assoc();

    	return $res['referencia'];
    }
	
	public function obtener_pedido($caja, $referencia){
    	$query = "SELECT pedido FROM caja_pedido_referencia WHERE caja=$caja AND referencia=$referencia";
    	data_model()->executeQuery($query);
    	$res = data_model()->getResult()->fetch_assoc();

    	return $res['pedido'];
    }
}

?>