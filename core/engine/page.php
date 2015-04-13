<?php

/**
 * This class manage html content
 *
 */
class page {

    /** css includes @type ArrayObject $css. */
    private $css = array(); // css include 
    /** js includes @type ArrayObject $js. */
    private $js = array(); // js include
    /** bodytag @type string $bodyTag. */
    private $bodyTag = ''; // body
    /** new iserts @type string $bodyTagInsert. */
    private $bodyTagInsert = ''; // new tag
    /** access flag @type boolean $authorised. */
    private $authorised = false;

    /** access key @type string $password. */
    private $password = '';

    /** page title @type string $title. */
    private $title = ''; // page title
    /** estigmas @type ArrayObject $estigmas. */
    private $estigmas = array(); // estigmas
    /** no parsed tags @type ArrayObject $postParseTags. */
    private $postParseTags = array(); // tags
    /** html includes @type ArrayObject $bits . */
    private $bits = array(); // bits (html includes)
    /** page content @type string $content . */
    private $content = "";

    /**
     * class constructor
     *
     */
    function __construct() {
        $this->tags['systemModuleName'] = MODULE;
        $this->mod['systemModuleName']  = true;
    }

    /**
     * returns page title
     *
     * @return string
     *
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * set new password
     *
     * @param string $password new password
     *
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * set new title
     *
     * @param string $title new title
     *
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * add new css include
     *
     * @param string $css css file name
     *
     */
    public function setCss($css) {
        $this->css[count($this->css)] = $css;
    }

    /**
     * get css includes array
     *
     * @return ArrayObject $css css includes
     *
     */
    public function getCss() {
        return $this->css;
    }

    /**
     * add new js include
     *
     * @param string $js js file name
     *
     */
    public function setJs($js) {
        $this->js[count($this->js)] = $js;
    }

    /**
     * get js includes array
     *
     * @return ArrayObject $js js includes
     *
     */
    public function getJs() {
        return $this->js;
    }

    /**
     * updates page content
     *
     * @param string $content new content
     *
     */
    public function setContent($content) {
        $this->content = $content;
    }

    /**
     * add new estigma
     *
     * @param string $key estigma label
     * @param string $data estigma value
     * @param boolean $allow block content
     *
     */
    public function addEstigma($key, $data, $allow = true) {
        $this->mod[$key] = $allow;
        $this->tags[$key] = $data;
    }

    /**
     * gets stored tags
     *
     * @return ArrayObject $tags
     *
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * gets access tokens
     *
     * @return ArrayObject $mod
     *
     */
    public function getMods() {
        return $this->mod;
    }

    /**
     * add no parse tag
     *
     * @param string $key tag name
     * @param string $data tag value
     *
     */
    public function addPPTag($key, $data) {
        $this->postParseTags[$key] = $data;
    }

    /**
     * get no parse tags
     *
     * @return ArrayObject $postParseTags
     *
     */
    public function getPPTags() {
        return $this->postParseTags;
    }

    /**
     * add new html include
     *
     * @param string $estigma label name
     * @param string $bit html file path 
     *
     */
    public function addTemplateBit($estigma, $bit) {
        $this->bits[$estigma] = $bit;
    }

    /**
     * gets all bits
     *
     * @return ArrayObject $bits
     *
     */
    public function getBits() {
        return $this->bits;
    }

    /**
     * gets estigma content
     *
     * @param string $estigma estigma label
     *
     */
    public function getBlock($estigma) {
        preg_match('#<!-- START ' . $estigma . ' -->(.+?)<!-- END ' . $estigma . ' -->#si', $this->content, $tor);
        $tor = str_replace('<!-- START ' . $estigma . ' -->', "", $tor[0]);
        $tor = str_replace('<!-- END ' . $estigma . ' -->', "", $tor);
        return $tor;
    }

    /**
     * get page content
     *
     * @return string $content page content
     *
     */
    public function getContent() {
        return $this->content;
    }

}

?>