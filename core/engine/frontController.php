<?php

interface frontControllerInterface {

    public function setController($controller);

    public function setAction($action);

    public function setParams(array $params);

    public function run();
}

class frontController implements frontControllerInterface {

    protected $controller = "";
    protected $action = "";
    protected $params = array();
    protected $basePath = WEB_DIR;

    public function __construct(array $options) {
        if (empty($options)): $this->parseURI();
        else:
            if (isset($options['controller']))
                $this->setController($options['controller']);
            if (isset($options['action']))
                $this->setAction($options['action']);
            if (isset($options['params']))
                $this->setParams($options['params']);
        endif;
    }

    protected function parseURI() {
        $this->basePath = '/' . $this->basePath;
        $path = '/' . trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
        $path = preg_replace('/[^a-zA-Z0-9]\//', "", $path);
        if (strpos($path, $this->basePath) === 0):
            $path = substr($path, strlen($this->basePath));
        endif;

        @list($controller, $action, $params) = explode("/", $path, 3);

        if (isset($controller)):
            $this->setController($controller);
        endif;
        if (isset($action)):
            $this->setAction($action);
        endif;
        if (isset($params)):
            $this->setParams(explode("/", $params));
        endif;
    }

    public function setController($controller) {
        $controller = str_replace('%20', '_', $controller);
        $controller = str_replace(' ', '_', $controller);
        try {
            import('mdl.' . $controller);
        } catch (Exception $e) {
            try {
                import('mdl.controller.' . $controller);
            } catch (Exception $e) {
                ##
            }
        }
        $controller = "{$controller}Controller";
        if (!class_exists($controller)):
            HttpHandler::redirect($this->basePath . 'error/not_found');
        endif;
        $this->controller = $controller;
        if ($this->controller == 'Controller'):
            HttpHandler::redirect(DEFAULT_DIR);
        endif;
        return $this;
    }

    public function setAction($action) {
        $reflector = new \ReflectionClass($this->controller);
        if (!$reflector->hasMethod($action)):
            HttpHandler::redirect($this->basePath . 'error/not_found');
        endif;
        $this->action = $action;
        return $this;
    }

    public function setParams(array $params) {
        $this->params = $params;
        return $this;
    }

    public function run() {
        if (is_callable(array(new $this->controller, $this->action))):
            call_user_func_array(array(new $this->controller, $this->action), $this->params);
        else:
            HttpHandler::redirect($this->basePath . 'error/not_found');
        endif;
    }

}

?>