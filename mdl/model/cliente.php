<?php

class clienteModel extends object {
    /*
     * 
     * Implementacion de funciones de abstraccion
     * 
     */

    public function tiene_credito($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "estado credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);               # obtiene el id del cliente actual
            if ($id > 0) {                                    # verifica si es un id valido
                $res['success'] = true;                 # si todo ha ido bien indica un estado de exito en la peticion
                $tcredito = $this->get_attr('tcredito');    # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($tcredito) || ($tcredito == 0))     # valida tipo de respuesta para diferentes versiones de Mysql 
                    return false;
                else
                if ($tcredito == '1' || $tcredito == 1)
                    return true;
                else
                    return $this->get_attr('tcredito');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function obtener_credito($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "obtener monto credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);               # obtiene el id del cliente actual
            if ($id > 0) {                                    # verifica si es un id valido
                $res['success'] = true;                 # si todo ha ido bien indica un estado de exito en la peticion
                $credito = $this->get_attr('credito');      # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($credito) || ($credito == 0))       # valida tipo de respuesta para diferentes versiones de Mysql 
                    return 0.0;
                else
                    return $this->get_attr('credito');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function tiene_financiamiento($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "estado financiamiento"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $tfina = $this->get_attr('extra_credito');      # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($tfina) || ($tfina == 0))               # valida tipo de respuesta para diferentes versiones de Mysql 
                    return false;
                else
                if ($tfina == '1' || $tfina == 1)
                    return true;
                else
                    return $this->get_attr('extra_credito');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function obtener_financiamiento($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "obtener monto extra financiamiento"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $ecredito = $this->get_attr('monto_extra');     # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($ecredito) || ($ecredito == 0))         # valida tipo de respuesta para diferentes versiones de Mysql 
                    return 0.0;
                else
                    return $this->get_attr('monto_extra');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function desactivar_credito($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "desactivacion de credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('tcredito', '0');               # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function activar_credito($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "activacion de credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('tcredito', true);              # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function aperturar_credito($monto, $json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "apertura de credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        if ($monto < 0)
            $monto *= -1;# evitar negativos

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('tcredito', true);              # modifica el estado
                $this->set_attr('credito', $monto);             # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function anular_credito($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "anulacion de credito"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('tcredito', '0');               # modifica el estado
                $this->set_attr('credito', 0.0);                # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function bloquear($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "bloquear cliente"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('bloqueado', true);             # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function desbloquear($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "desbloquear cliente"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $this->set_attr('bloqueado', '0');              # modifica el estado
                $this->save();                                  # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function esta_bloqueado($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "estado de bloqueo"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $bloqu = $this->get_attr('bloqueado');          # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($bloqu) || ($bloqu == 0))               # valida tipo de respuesta para diferentes versiones de Mysql 
                    return false;
                else
                if ($bloqu == '1' || $bloqu == 1)
                    return true;
                else
                    return $this->get_attr('bloqueado');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function abonar($monto, $json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "abono a cuenta"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        if ($monto < 0)
            $monto *= -1;# evitar negativos

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                       # obtiene el id del cliente actual
            if ($id > 0) {                                            # verifica si es un id valido
                $res['success'] = true;                         # si todo ha ido bien indica un estado de exito en la peticion
                $saldo = $this->get_attr('credito_usado');
                $this->set_attr('credito_usado', $saldo - $monto);  # modifica el estado
                $this->save();                                      # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function cargar($monto, $json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "cargo a cuenta"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        if ($monto < 0)
            $monto *= -1;# evitar negativos

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                       # obtiene el id del cliente actual
            if ($id > 0) {                                            # verifica si es un id valido
                $res['success'] = true;                         # si todo ha ido bien indica un estado de exito en la peticion
                $saldo = $this->get_attr('credito_usado');
                $this->set_attr('credito_usado', $saldo + $monto);  # modifica el estado
                $this->save();                                      # guarda los cambios
            } else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    public function saldo($json_response = false) {
        // estandar para toda comunicacion asincrona dentro el sistema
        // cada transaccion debe poseer al menos los siguientes elementos
        $res = array();
        $res['transaction'] = "obtener saldo"; // operacion que se esta realizando
        $res['message'] = "";               // mensaje de la operacion (puede ser mensaje de error u otro)
        $res['success'] = false;            // estado de la operacion (true si tuvo exito o false si ocurre algun error)

        /* bloque try que valida si cliente ha sido inicializado, sino lanza una excepcion */
        try {
            $id = $this->get_attr($this->id);                   # obtiene el id del cliente actual
            if ($id > 0) {                                        # verifica si es un id valido
                $res['success'] = true;                     # si todo ha ido bien indica un estado de exito en la peticion
                $saldo = $this->get_attr('credito_usado');      # verifica el estado del credito del cliente 
                if ($json_response)
                    echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
                if (empty($saldo) || ($saldo == 0))               # valida tipo de respuesta para diferentes versiones de Mysql 
                    return 0.0;
                else
                    return $this->get_attr('credito_usado');# retorna estado del credito
            }
            else {
                $res['success'] = false;                    # error, el id del cliente no era valido
                $res['message'] = "Can not find customer";  # se informa del mensaje al navegador
            }
        } catch (Exception $e) {
            $res['success'] = false;                # error, no se habia inicializado el objeto
            $res['message'] = $e->getMessage();     # obtiene el mensaje de la excepcion levantada
        }

        if ($json_response)
            echo json_encode($res);# si las respuestas asincronas estan activas envia el mensaje al cliente en JSON format
    }

    // fin

    public function get_clients() {
        $sql = "  SELECT * FROM cliente 
              WHERE codigo_afiliado NOT IN(
                  SELECT id_datos FROM empleado 
              );";

        return data_model()->cacheQuery($sql);
    }

    public function updateModules($user, $flg = false) {
        if ($flg) {
            $Query = "SELECT acceso FROM empleado WHERE id_datos='{$user}'";
        } else {
            $Query = "SELECT acceso FROM empleado WHERE usuario='{$user}'";
        }
        data_model()->executeQuery($Query);
        if (data_model()->getNumRows() > 0) {
            while ($data = data_model()->getResult()->fetch_assoc()) {
                return $data['acceso'];
            }
        } else {
            return "";
        }
    }

    public function unlockAcces($cliente, $str) {
        $Query = "UPDATE empleado SET acceso='{$str}' WHERE id_datos = $cliente";

        data_model()->executeQuery($Query);
    }

    public function listaEmpleados($limitInf = '', $limitSup = '') {
        $limit_str = "";
        if ($limitInf !== '')
            $limit_str .= "LIMIT $limitInf";
        if ($limitSup !== '')
            $limit_str .= ", $limitSup";
        $Query = "SELECT * FROM cliente WHERE codigo_afiliado IN 
              (SELECT id_datos FROM empleado) {$limit_str};";
        return data_model()->cacheQuery($Query);
    }

    public function delete_emp($emp) {
        $sql = " DELETE FROM empleado WHERE id_datos=$emp;";
        return data_model()->executeQuery($sql);
    }

    public function es_admin($emp) {
        $sql = " SELECT id_datos FROM empleado WHERE id_datos=$emp AND permiso=1;";
        data_model()->executeQuery($sql);
        if (data_model()->getNumRows() > 0)
            return true;
        else
            return false;
    }

    public function get_employees() {
        $sql = " SELECT * FROM empleado ";
        return data_model()->cacheQuery($sql);
    }

    public function RegistroBloqueado($usuario) {
        $Query = "SELECT id_datos FROM empleado WHERE usuario='{$usuario}'";
        data_model()->executeQuery($Query);
        while ($data = data_model()->getResult()->fetch_assoc()) {
            return $data['id_datos'];
        }
    }

    public function credito_cliente($codigo_afiliado) {
        $query = "SELECT credito, credito_usado FROM cliente WHERE codigo_afiliado=$codigo_afiliado";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();

        return array($res['credito'], $res['credito_usado'], $res['credito'] - $res['credito_usado']);
    }

    public function es_valido($id_ref, $total) {
        $query = "SELECT cliente FROM traslado WHERE id=$id_ref";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        $cliente = $res['cliente'];
        $query = "SELECT credito, credito_usado FROM cliente WHERE codigo_afiliado=$cliente";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        if (($res['credito_usado'] + $total) > $res['credito']) {
            return false;
        } else {
            return true;
        }
    }

    public function actualizar_saldo($id_ref, $total) {
        $query = "SELECT cliente FROM traslado WHERE id=$id_ref";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        $cliente = $res['cliente'];
        $query = "UPDATE cliente SET credito_usado = (credito_usado + $total) WHERE codigo_afiliado=$cliente";
        data_model()->executeQuery($query);
    }

    public function referencias_cliente($id_cliente) {
        $query = "SELECT * FROM referencias WHERE cliente=$id_cliente";
        return data_model()->cacheQuery($query);
    }

}

?>