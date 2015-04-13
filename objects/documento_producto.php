<?php

class documento_productoModel extends object {
    public function getDocument($estilo){
    	$query = "SELECT numero_documento FROM documento_producto WHERE estilo=$estilo";

    	data_model()->executeQuery($query);

    	$res = data_model()->getResult()->fetch_assoc();

    	return $res['numero_documento']; 
    }

    public function get($terms) {
        list($tblname, $fields, $id, $is_auto) = ORMHelper::analize($this);
        if (!$this->modelTablePk):
            return false;
        else:
            if ($terms !== null):
				$linea  = $terms['linea'];
				$estilo = $terms['estilo'];
                $select = "SELECT * FROM $tblname WHERE linea = $linea AND estilo='{$estilo}'";
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