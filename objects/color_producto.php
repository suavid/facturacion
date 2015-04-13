<?php

class color_productoModel extends object {
    public function get_colors($estilo){
        $query = "SELECT * FROM color_producto WHERE color_estilo_producto=$estilo";
        data_model()->executeQuery($query);
        $resp = array();
        while($res = data_model()->getResult()->fetch_assoc()){
            $resp[] = $res;
        } 
        
        return $resp;
    }

    public function exists($data) {
        $estilo = $data['estilo'];
        $color  = $data['color'];
        $query = "SELECT * FROM color_producto WHERE color_estilo_producto=$estilo AND color=$color";
        data_model()->executeQuery($query);
        if(data_model()->getNumRows() > 0) return true;
        
        return false;
    }
}

?>