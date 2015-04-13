<?php

class cuentaModel extends object {
    
    public function aumentar($monto){
        // 0 == pasivo y 2 == capital
        if($this->tipo==0 || $this->tipo==2){
            $id_cuenta = $this->id;
            $detalle = $this->get_child('detalle_cuenta');
            $detalle->get(0);
            $detalle->set_attr('id_cuenta', $id_cuenta);
            $detalle->set_attr('debe', '0.0');
            $detalle->set_attr('haber', $monto);
            $detalle->save();
        }else{
            $id_cuenta = $this->id;
            $detalle = $this->get_child('detalle_cuenta');
            $detalle->get(0);
            $detalle->set_attr('id_cuenta', $id_cuenta);
            $detalle->set_attr('haber', '0.0');
            $detalle->set_attr('debe', $monto);
            $detalle->save();
        }
    }
    
    public function disminuir($monto){
        // 0 == pasivo y 2 == capital
        if($this->tipo==1){
            $id_cuenta = $this->id;
            $detalle = $this->get_child('detalle_cuenta');
            $detalle->get(0);
            $detalle->set_attr('id_cuenta', $id_cuenta);
            $detalle->set_attr('debe', '0.0');
            $detalle->set_attr('haber', $monto);
            $detalle->save();
        }else{
            $id_cuenta = $this->id;
            $detalle = $this->get_child('detalle_cuenta');
            $detalle->get(0);
            $detalle->set_attr('id_cuenta', $id_cuenta);
            $detalle->set_attr('haber', '0.0');
            $detalle->set_attr('debe', $monto);
            $detalle->save();
        }
    }
    
}

?>