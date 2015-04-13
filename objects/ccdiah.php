<?php

class ccdiahModel extends object {
    
	public function pagosPendientes($idCliente){
		$query = "SELECT * FROM ccdiah WHERE codcli = $idCliente AND saldo > 0";

		data_model()->executeQuery($query);

		$rows = data_model()->getNumRows();
		$resp = array();

		if($rows > 0){
			while($ret = data_model()->getResult()->fetch_assoc()){
				$resp[] = $ret;
			}
			return $resp;
		}else{
			return null;
		}
	}

}

?>