<?php

class ErrorController 
{
    public function not_found() 
    {
        BM::singleton()->getObject('temp')->buildFromTemplates(DEFAULT_LAYOUT);
        template()->addTemplateBit('content', 'error.html');
        BM::singleton()->getObject('temp')->getPage()->setTitle("Recurso no encontrado");
        BM::singleton()->getObject('temp')->getPage()->addEstigma("username", Session::singleton()->getUser());
        BM::singleton()->getObject('temp')->getPage()->addEstigma("TITULO", "Error 404");
        BM::singleton()->getObject('temp')->parseExtras();
        BM::singleton()->getObject('temp')->parseOutput();
        print BM::singleton()->getObject('temp')->getPage()->getContent();
    }

    public function e403() 
    {
        BM::singleton()->getObject('temp')->buildFromTemplates(DEFAULT_LAYOUT);
        template()->addTemplateBit('content', 'e403.html');
        BM::singleton()->getObject('temp')->getPage()->setTitle("Acceso restringido");
        BM::singleton()->getObject('temp')->getPage()->addEstigma("username", Session::singleton()->getUser());
        BM::singleton()->getObject('temp')->getPage()->addEstigma("TITULO", "Error 403");
        BM::singleton()->getObject('temp')->parseExtras();
        BM::singleton()->getObject('temp')->parseOutput();
        print BM::singleton()->getObject('temp')->getPage()->getContent();
    }
}

?>