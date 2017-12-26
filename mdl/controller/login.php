<?php

import('mdl.view.login');
import('mdl.model.login');

class LoginController extends controller
{
    public function form()
    {
        if (!Session::singleton()->ValidateSession()){ $this->view->show_form(); } else
        {
            HttpHandler::redirect('/facturacion/factura/principal');
        }
    }

    public function login()
    {
        if (empty($_POST)){ HttpHandler::redirect('/facturacion/login/form'); } else
        {
            $client  = new SoapClient(SERVICE_URL, array("trace" => 1, "exception" => true, "soap_version"=>SOAP_1_1));

            $usuario = (isset($_POST['usuario']))? $_POST['usuario']:'';
            $clave   = (isset($_POST['clave']))? cifrar_RIJNDAEL_256($_POST['clave']):'';

            $params = array('Usuario' => $usuario,'Clave'   => $clave);
            
            $result = $client->Autenticar($params);
      
            if( $result->{"AutenticarResult"} == 0 )
            {
                 Session::singleton()->NewSession($usuario);
                 HttpHandler::redirect('/facturacion/login/form');
            }
            else{ HttpHandler::redirect('/facturacion/login/form?error_id=2'); }
        }
    }
}

?>