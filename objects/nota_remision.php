<?php

class nota_remisionModel extends object {
    
	public function obtenerId($pedido, $caja){
		
		$query = "SELECT id FROM nota_remision WHERE noped=$pedido AND caja=$caja";
		
		data_model()->executeQuery($query);
		
		if(data_model()->getNumRows()>0){
			$row = data_model()->getResult()->fetch_assoc();
			
			return $row['id'];
		}else{
			return -1;
		}		
	}
}

?>