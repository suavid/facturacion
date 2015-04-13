<?php

class productoModel extends object {

    public function existe_producto($linea, $estilo) {
        $query = "SELECT * FROM producto WHERE estilo=$estilo AND linea=$linea";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;

        return false;
    }

    public function existe_producto_g($grupo, $estilo) {
        $query = "SELECT * FROM producto WHERE estilo=$estilo AND grupo=$grupo";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0)
            return true;

        return false;
    }

    public function cambio_linea($linea_actual, $nueva_linea, $estilo) {
        $query = "START TRANSACTION";
        data_model()->executeQuery($query);
        $query = "UPDATE control_precio SET linea = $nueva_linea WHERE control_estilo=$estilo AND linea=$linea_actual";
        data_model()->executeQuery($query);
        $query = "UPDATE estado_bodega SET linea = $nueva_linea WHERE estilo=$estilo AND linea=$linea_actual";
        data_model()->executeQuery($query);
        $query = "UPDATE oferta_producto SET linea = $nueva_linea WHERE estilo=$estilo AND linea=$linea_actual";
        data_model()->executeQuery($query);
        $query = "UPDATE producto SET linea=$nueva_linea WHERE estilo=$estilo AND linea=$linea_actual";
        data_model()->executeQuery($query);
        $query = "COMMIT";
        data_model()->executeQuery($query);
    }

    public function cambio_grupo($grupo_actual, $nuevo_grupo, $estilo) {
        $query = "START TRANSACTION";
        data_model()->executeQuery($query);
        /*$query = "UPDATE control_precio SET grupo = $nuevo_grupo WHERE control_estilo=$estilo AND grupo=$grupo_actual";
        data_model()->executeQuery($query);
        $query = "UPDATE estado_bodega SET grupo = $nuevo_grupo WHERE estilo=$estilo AND grupo=$grupo_actual";
        data_model()->executeQuery($query);
        $query = "UPDATE oferta_producto SET grupo = $nuevo_grupo WHERE estilo=$estilo AND grupo=$grupo_actual";
        data_model()->executeQuery($query);*/
        $query = "UPDATE producto SET grupo=$nuevo_grupo WHERE estilo=$estilo AND grupo=$grupo_actual";
        data_model()->executeQuery($query);
        $query = "COMMIT";
        data_model()->executeQuery($query);
    }
	
	public function get($terms) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        if (!$this->modelTablePk):
            return false;
        else:
            if ($terms !== null):
				$linea  = $terms['linea'];
				$estilo = $terms['estilo'];
                $select = "SELECT * FROM $tblname WHERE linea = $linea AND estilo='sasdasdllcL\''";
                if(data_model()->getNumRows()>0):
                    data_model()->executeQuery($select);
                    $data = data_model()->getResult()->fetch_assoc();
                    if(!is_null($data)):
                        foreach ($data as $key => $value):
                            $this->set_attr($key, $value);
                            if (empty($value)):
                                $this->set_attr($key, '');
                            endif;
                        endforeach;
                    else:
                        foreach ($fields as $field):
                            $this->set_attr($field, '');
                        endforeach;
                    endif;    
                else:
                    foreach ($fields as $field):
                        $this->set_attr($field, '');
                    endforeach;
                endif;
            else:
                foreach ($fields as $field):
                    $this->set_attr($field, '');
                endforeach;
            endif;
        endif;
    }

}

?>