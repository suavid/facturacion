<?php

class detalle_orden_compraModel extends object {
    public function existe($estilo, $linea, $color, $talla, $id_orden){
		$query = "SELECT id FROM detalle_orden_compra WHERE id_orden = $id_orden AND linea = $linea AND estilo='{$estilo}' AND color=$color AND $talla = talla";
		
		data_model()->executeQuery($query);

		if(data_model()->getNumRows()>0)
			return true;
		else
			return false;
	}
}

?>