<?php

class LoginView {

    public function show_form() {
        BM::singleton()->getObject('temp')->buildFromTemplates('login.html');
        page()->setTitle('Acceso');
        BM::singleton()->getObject('temp')->parseExtras();
        BM::singleton()->getObject('temp')->parseOutput();
        print BM::singleton()->getObject('temp')->getPage()->getContent();
    }

    public function show_info() {
        pagina_simple('info.html', 'Zonas restringidas');
    }

}

?>