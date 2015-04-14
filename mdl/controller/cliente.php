<?php

import('mdl.model.cliente');
import('mdl.view.cliente');

class clienteController extends controller {

    private $MODULES = array(
        'inventario' => array('acces' => false, 'subCategory' => array('colores' => false, 'lineas' => false, 'marcas' => false, 'generos' => false, 'productos' => false, 'proveedores' => false, 'bodegas' => false, 'stock' => false)),
        'factura' => array('acces' => false, 'subCategory' => array()),
        'credito' => array('acces' => false, 'subCategory' => array()),
        'cliente' => array('acces' => false, 'subCategory' => array('fichaCliente' => true, 'listadoCliente' => false, 'listadoEmpleado' => true))
    );

    public function updateModules($id = '') {
        if ($id == '')
            $str = $this->model->updateModules(Session::singleton()->getUser());
        else
            $str = $this->model->updateModules($id, true);
        if ($str != "") {
            $this->MODULES = unserialize(base64_decode($str));
        }
    }

    public function resetAcces($cliente) {
        $str = base64_encode($this->serializeModules());
        $this->model->unlockAcces($cliente, $str);
        echo json_encode(array('status' => 'OK'));
    }

    public function unlockAcces($modulo, $cliente) {
        $this->updateModules($cliente);
        $this->MODULES[$modulo]['acces'] = true;
        $str = base64_encode($this->serializeModules());
        $this->model->unlockAcces($cliente, $str);
        echo json_encode(array('status' => 'OK'));
    }

    public function unlockSubAcces($modulo, $sub, $cliente) {
        $this->updateModules($cliente);
        $this->MODULES[$modulo]['subCategory'][$sub] = true;
        $str = base64_encode($this->serializeModules());
        $this->model->unlockAcces($cliente, $str);
        echo json_encode(array('status' => 'OK'));
    }

    public function lockAcces($modulo, $cliente) {
        $this->updateModules($cliente);
        $this->MODULES[$modulo]['acces'] = false;
        $str = base64_encode($this->serializeModules());
        $this->model->unlockAcces($cliente, $str);
        echo json_encode(array('status' => 'OK'));
    }

    public function lockSubAcces($modulo, $sub, $cliente) {
        $this->updateModules($cliente);
        $this->MODULES[$modulo]['subCategory'][$sub] = false;
        $str = base64_encode($this->serializeModules());
        $this->model->unlockAcces($cliente, $str);
        echo json_encode(array('status' => 'OK'));
    }

    public function CheckIfModuleEnabledStr($moduleName) {
        $admin = (Session::singleton()->getLevel() == 1) ? true : false;
        if (isset($_POST['id'])) {
            $this->updateModules($_POST['id']);
        } else {
            $this->updateModules();
        }
        $ret['Module'] = $moduleName;
        if (isset($this->MODULES[$moduleName])) {
            $ret['Result'] = ( $this->MODULES[$moduleName]['acces'] || $admin );
        } else {
            $ret['Result'] = ( false || $admin );
        }
        echo json_encode($ret);
    }

    public function CheckIfSubEnabledStr($moduleName, $sub) {
        if (isset($_POST['id'])) {
            $this->updateModules($_POST['id']);
        } else {
            $this->updateModules();
        }
        $ret['Module'] = $sub;
        if (isset($this->MODULES[$moduleName]['subCategory'][$sub])) {
            $ret['Result'] = $this->MODULES[$moduleName]['subCategory'][$sub];
        } else {
            $ret['Result'] = false;
        }
        echo json_encode($ret);
    }

    public function serializeModules() {
        return serialize($this->MODULES);
    }

    private function validar() {
        if (!Session::ValidateSession())
            HttpHandler::redirect(DEFAULT_DIR);

        //if (!isset($_SESSION['cliente']))
            //HttpHandler::redirect('/nymsa/modulo/listar');
    }

    public function principal() {
        HttpHandler::redirect('/nymsa/modulo/listar');
        //$this->validar();
        //$user = Session::getUser();
        //$this->view->mostrar_opciones($user);
    }

    /*
     *
     * Descripcion: Borra un empleado del sistema
     *
     */

    public function borrar_empleado() {
        $ret = array();
        $ret['admin'] = false;
        $this->validar();
        $id = $_POST['id'];
        if (!$this->model->es_admin($id))
            $this->model->delete_emp($id);
        else
            $ret['admin'] = true;
        echo json_encode($ret);
    }

    public function verFicha() {
        $this->validar();
        if (isset($_GET['cliente'])):
            $cliente = $_GET['cliente'];
            if ($this->model->exists($cliente)):
                $cache = array();
                $cache[0] = $this->model->search('codigo_afiliado', $cliente);
                $this->view->fichaCliente($cliente, $cache);
            else:
                HttpHandler::redirect('/nymsa/cliente/listado');
            endif;
        else:
            HttpHandler::redirect('/nymsa/cliente/listado');
        endif;
    }

    public function borrarEmpleado() {
        $this->validar();
        $id = $_GET['id'];
        $this->model->delete_emp($id);
        HttpHandler::redirect('/nymsa/cliente/empleados');
    }

    public function ficha() {
        $this->validar();
        $user  = Session::getUser();
        $cache = array();
        $cache['departamentos'] = $this->model->get_child('departamento')->get_list();
        $this->view->ficha($user, $cache);
    }

    public function gestion_empleados() {
        $this->validar();
        $user  = Session::getUser();
        $cache = array();
        $cache['departamentos'] = $this->model->get_child('departamento')->get_list();
        $this->view->gestion_empleados($user, $cache);
    }

    public function cargar_municipios(){
        $departamento = $_POST['departamento'];
        $this->model->get_child('municipio')->cargar_municipios($departamento);
    }

    /*
     *
     * Descripcion: Funcion que muestra el listado de clientes
     *              para edicion.
     *
     */

    public function mantenimiento() {
        $this->validar();
        $user = Session::getUser();
        $this->view->mantenimiento($user);
    }

    /*
     *
     * Descripcion: Funcion que muestra clientes que no son empleados
     *              con la posibilidad de convertirlos en empleados
     *
     */

    public function empleados() {
        $this->validar();
        $user = Session::getUser();
        import('scripts.paginacion');
        $cache = array();
        if (isset($_GET['filtro']) && !empty($_GET['filtro'])):
            $filtro = data_model()->sanitizeData($_GET['filtro']);
            $tipo_filtro = (isset($_GET['tipo_filtro']) && !empty($_GET['tipo_filtro'])) ? data_model()->sanitizeData($_GET['tipo_filtro']) : 'nombre';
            $numeroRegistros = $this->model->quantify($tipo_filtro, $filtro);
            $url_filtro = "/nymsa/cliente/listado?filtro=" . $filtro . "&";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache[0] = $this->model->get_child('empleado')->filter($tipo_filtro, $filtro, $limitInf, $tamPag);
        else:
            $numeroRegistros = $this->model->get_child('empleado')->quantify();
            $url_filtro = "/nymsa/cliente/listado?";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache[0] = $this->model->listaEmpleados($limitInf, $tamPag);
        endif;
        $fil = (isset($_GET['tipo_filtro'])) ? $_GET['tipo_filtro'] : 'nombre';
        $cache[1] = $this->model->RegistroBloqueado(Session::singleton()->getUser());
        $this->view->empleados($cache, $user, $paginacion_str, $fil);
    }

    /*
     *
     * Descripcion: Selección de nombre de usuario para empleado
     *
     */

    public function nuevo_empleado() {
        $this->validar();
        $cliente = $_GET['codigo'];
        $user = Session::getUser();
        if ($this->model->exists($cliente)):
            $this->view->nuevo_empleado($cliente, $user);
        else:
            echo "RECURSO NO ENCONTRADO";
        endif;
    }

    /*
     *
     * Descripcion: Crea un nuevo empleado a partir de un cliente
     *
     */

    public function salvar_empleado() {
        $this->validar();
        if (isset($_POST) && !empty($_POST)):
            $data = $_POST;
            $data['clave'] = cifrar_RIJNDAEL_256($data['usuario']);
            $empObj = $this->model->get_child('empleado');
            copy(APP_PATH . 'static/img/users/thumbnail_' . $data['id_datos'], APP_PATH . 'static/img/users/' . $data['usuario'] . '.jpg');
            $empObj->get(0);
            $data['acceso'] = base64_encode($this->serializeModules());
            $empObj->change_status($data);
            $empObj->save();
            HttpHandler::redirect('/nymsa/cliente/ficha');
        else:
            HttpHandler::redirect('/nymsa/cliente/principal');
        endif;
    }

    /*
     *
     * Descripcion: Funcion que almacena un nuevo cliente en
     *              la base de datos.
     *
     */

    public function salvar_nuevo() {
        $this->validar();
        if (isset($_POST) && !empty($_POST)):
            # lista de campos requeridos 
            $id = $_POST['codigo_afiliado'];
            $this->model->get($id); # crea un nuevo objeto
            //$this->model->not_null($not_null); # establecer campos requeridos
            $this->model->change_status($_POST); # actualiza al objeto
            if ($this->model->save()):
                HttpHandler::redirect('/nymsa/cliente/principal?success_code=200');
            else:
                HttpHandleRodrigor::redirect('/nymsa/cliente/principal?error_id=100');
            endif;
        else:
            HttpHandler::redirect('/nymsa/cliente/principal');
        endif;
    }

    public function referencias() {
        $this->validar();
        $cliente = $_GET['id_cliente'];
        if ($this->model->exists($cliente) && isset($_GET['id_cliente']) && !empty($_GET['id_cliente'])) {
            $cache = array();
            $cache[0] = $this->model->referencias_cliente($cliente);
            $this->view->referencias(Session::singleton()->getUser(), $cliente, $cache);
        } else {
            HttpHandler::redirect('/nymsa/cliente/ficha');
        }
    }

    public function dato_laboral() {
        $this->validar();
        $cliente = $_GET['id_cliente'];
        if ($this->model->exists($cliente) && isset($_GET['id_cliente']) && !empty($_GET['id_cliente'])) {
            $this->view->dato_laboral(Session::singleton()->getUser(), $cliente);
        } else {
            HttpHandler::redirect('/nymsa/cliente/ficha');
        }
    }

    function tiene_credito_fiscal() {
        $response = array();
        $response['estado'] = false;
        $cliente = $_POST['cliente'];
        $this->model->get($cliente);
        if (trim($this->model->get_attr('credito_fiscal')) != "") {
            $response['estado'] = true;
        }

        echo json_encode($response);
    }

    public function dato_credito() {
        $this->validar();
        $cliente = $_GET['id_cliente'];
        $documentos = $this->model->get_child('documentos_cliente')->get_list();
        if ($this->model->exists($cliente) && isset($_GET['id_cliente']) && !empty($_GET['id_cliente'])) {
            $this->view->dato_credito(Session::singleton()->getUser(), $cliente, $documentos);
        } else {
            HttpHandler::redirect('/nymsa/cliente/ficha');
        }
    }

    public function salvar_dato_laboral() {
        $cliente = $_POST['codigo_afiliado'];
        $data = $_POST;
        if (isset($_POST['posee_carro']))
            $data['posee_carro'] = 1;
        $this->model->get($cliente);
        $this->model->change_status($data);
        $this->model->save();
        HttpHandler::redirect('/nymsa/cliente/dato_laboral?id_cliente=' . $_POST['codigo_afiliado']);
    }

    public function cargar_dato_laboral() {
        $cliente = $_POST['codigo_afiliado'];
        $this->model->get($cliente);
        $response = array();

        $fields = array(
            'nombre_empresa',
            'direccion_empresa',
            'telefono_empresa',
            'salario_mensual',
            'otros_ingresos',
            'especificacion',
            'puesto',
            'fecha_ingreso',
            'posee_carro',
            'marca',
            'modelo',
            'tipo',
            'vivienda',
            'tiempo',
            'pago_mensual',
            'gasto_mensual'
        );

        foreach ($fields as $field) {
            $response[$field] = $this->model->get_attr($field);
        }

        echo json_encode($response);
    }

    public function datos_referencia() {
        $id = $_POST['id'];
        $ref = $this->model->get_child('referencias');
        $ref->get($id);
        $fields = $ref->get_fields();
        $response = array();
        foreach ($fields as $field) {
            $response[$field] = $ref->get_attr($field);
        }
        echo json_encode($response);
    }

    public function salvar_referencia() {
        $id = $_POST['id'];
        $ref = $this->model->get_child('referencias');
        $ref->get($id);
        $ref->change_status($_POST);
        $ref->save();
        HttpHandler::redirect('/nymsa/cliente/referencias?id_cliente=' . $_POST['cliente']);
    }

    public function datosCliente() {
        if (isset($_POST) && !empty($_POST)):
            $cliente = $_POST['cliente'];
            $cacheQuery = $this->model->search('codigo_afiliado', $cliente);
            $data = data_model()->resultsFromCache($cacheQuery);
            if (!empty($data)):
                $data['STATUS'] = "OK";
            else:
                $data['STATUS'] = "NOTFOUND";
            endif;
            echo json_encode($data);
        else:
            echo "WRONG CALL! ACCESS DENIED";
        endif;
    }

    public function listado() {
        $this->validar();
        $user = Session::getUser();
        import('scripts.paginacion');
        $cache = array();
        if (isset($_GET['filtro']) && !empty($_GET['filtro'])):
            $filtro = data_model()->sanitizeData($_GET['filtro']);
            $tipo_filtro = array('primer_nombre' => $filtro, 'segundo_nombre' => $filtro, 'primer_apellido' => $filtro, 'segundo_apellido' => $filtro);
            $numeroRegistros = $this->model->MultyQuantify($tipo_filtro);
            $url_filtro = "/nymsa/cliente/listado?filtro=" . $filtro . "&";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache[0] = $this->model->Multyfilter($tipo_filtro, $limitInf, $tamPag);
        else:
            $numeroRegistros = $this->model->quantify();
            $url_filtro = "/nymsa/cliente/listado?";
            list($paginacion_str, $limitInf, $tamPag) = paginar($numeroRegistros, $url_filtro);
            $cache[0] = $this->model->get_list($limitInf, $tamPag);
        endif;
        $fil = (isset($_GET['tipo_filtro'])) ? $_GET['tipo_filtro'] : 'primer_nombre';
        $cache[1] = $this->model->get_child('empleado')->get_list();
        $cache[2] = $this->model->RegistroBloqueado(Session::singleton()->getUser());
        $this->view->listado($cache, $paginacion_str, $fil, $user);
    }

    public function foto() {
        upload_image(APP_PATH . 'static/img/users', 'archivo', $_GET['cliente']);
        echo $_GET['cliente'];
    }

    public function subir_documentos() 
    {
        if($_SERVER['REQUEST_METHOD'] == "POST" && (count($_POST) > 0))
        {

            $directorio = APP_PATH . 'static/pdf/cliente_'.$_POST['codigo_afiliado'];
            
            $nombre  = $_POST['tipo'];

            $nombre .= ".pdf";

            $tipo = "";

            switch ($_POST['tipo']) {
                case 'facturaE':
                    $tipo = "Factura de energía eléctrica";
                    break;

                case 'facturaA':
                    $tipo = "Factura de agua potable";
                    break;
                    
                case 'dui':
                    $tipo = "Documento único de identidad";
                    break;        
                
                case 'partnac':
                    $tipo = "Partida de nacimiento";
                    break;    

                default:
                    # code...
                    break;
            }

            if(!file_exists($directorio))
            {
                mkdir($directorio, 0777, true);
            }

            $docMdl = $this->model->get_child('documentos_cliente');

            $docMdl->get(0);

            $docMdl->id_cliente = $_POST['codigo_afiliado'];
            $docMdl->tipo = $tipo;
            $docMdl->nombre = $nombre;

            $docMdl->save();

            upload_pdf($directorio, 'documentos', $nombre);
            
            HttpHandler::redirect('/nymsa/cliente/dato_credito?id_cliente=' . $_POST['codigo_afiliado']);
        }
    }

    public function listar_documentos()
    {
        $cliente = 1;
        
    }

    public function cargar_datos_cliente() {
        $response = array();
        $this->model->get($_POST['codigo_afiliado']);
        $fields = $this->model->get_fields();
        foreach ($fields as $attr) {
            $response[$attr] = $this->model->get_attr($attr);
        }

        $emp = $this->model->get_child('empleado');
        $emp->set_attr('id', 'id_datos');
        if ($emp->es_empleado($_POST['codigo_afiliado'])) {
            $response['es_empleado'] = true;
        } else {
            $response['es_empleado'] = false;
        }
        echo json_encode($response);
    }

    public function datos_credito() {
        $codigo_afiliado = $_POST['cliente'];
        $this->model->get($codigo_afiliado);
        $response = array();

        $response['telefono_celular'] = $this->model->get_attr('telefono_celular');
        $response['telefono_oficina'] = $this->model->get_attr('telefono_oficina');
        $response['telefono_casa'] = $this->model->get_attr('telefono_casa');
        $response['transitorio'] = $this->model->get_attr('transitorio');
        $response['tcredito'] = $this->model->get_attr('tcredito');
        $response['bloqueado'] = $this->model->get_attr('bloqueado');
        $response['empleado'] = $this->model->get_attr('empleado');
        $response['credito'] = $this->model->get_attr('credito');
        $response['credito_usado'] = $this->model->get_attr('credito_usado');
        $response['extra_credito'] = $this->model->get_attr('extra_credito');
        $response['monto_extra'] = $this->model->get_attr('monto_extra');
        $response['nombre'] = $this->model->get_attr('primer_nombre') . ' ' . $this->model->get_attr('primer_apellido');
        echo json_encode($response);
    }

}

?>