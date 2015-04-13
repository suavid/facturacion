<?php

if (!defined('BUSINESS_MANAGER')) {
    echo 'WRONG CALL PROCEDURE';
    exit();
}

/**
 * This class provides method to generate html from templates
 *
 */
class template {

    /** Page generator @type Object $page. */
    private $page; # page generator 

    /**
     * load page module
     * 
     * @return none
     * 
     */

    public function __construct() {
        $this->page = new page();
    }

    /**
     * assoc estigma with respective template path
     * 
     * @param string $estigma estigma tag 
     * @param string $bit filepath 
     * @return none
     * 
     */
    public function addTemplateBit($estigma, $bit) {
        if (strpos($bit, 'static/html/') === false):
            $bit = 'static/html/' . BM::getSetting('skin') . '/templates/' . $bit;
        endif;
        $this->page->addTemplateBit($estigma, $bit);
    }

    /**
     * replace estigmas with bit content
     * 
     * @return none
     * 
     */
    private function replaceBits() {
        $bits = $this->page->getBits();
        foreach ($bits as $estigma => $template):
            $templateContent = file_get_contents($bits[$estigma]); # get content from template
            $newContent = str_replace('{' . $estigma . '}', $templateContent, $this->page->getContent());
            $this->page->setContent($newContent); # update content
        endforeach;
    }

    /**
     * replace tags with query results, cache data or string data
     * 
     * @return none
     * 
     */
    private function replaceTags() {
        $estigmas = $this->page->getTags(); # get the tags
        $mods = $this->page->getMods(); # get access

        foreach ($estigmas as $estigma => $data):
            if (is_array($data)):
                if ($data[0] == 'SQL'):
                    $this->replaceDBTags($estigma, $data[1], $mods[$estigma]); # BD data
                elseif ($data[0] == 'DATA'):
                    $this->replaceDataEstigmas($estigma, $data[1], $mods[$estigma]); # cache data
                endif;
            else:
                // replace content          
                $newContent = str_replace('{' . $estigma . '}', $data, $this->page->getContent());
                // update content
                $this->page->setContent($newContent);
            endif;
        endforeach;
    }

    /**
     * Query result data handler
     * 
     * @param string $estigma tag to replace
     * @param int $cacheId query cache position
     * @param boolean $mod is public access? 
     * @return none
     * 
     */
    private function replaceDBTags($estigma, $cacheId, $mod) {
        $block = '';
        $blockOld = $this->page->getBlock($estigma);
        // go through cache data
        while ($estigmas = BM::getObject('db')->resultsFromCache($cacheId)):
            $blockNew = $blockOld;
            // create new block for every given register
            foreach ($estigmas as $ntag => $data):
                $blockNew = str_replace("{" . $ntag . "}", $data, $blockNew);
            endforeach;
            $block .= $blockNew;
        endwhile;
        $pageContent = $this->page->getContent();
        if (!$mod):$block = "Access denied";
        endif;
        // delete <!-- --> tags
        $newContent = str_replace('<!-- START ' . $estigma . ' -->' . $blockOld . '<!-- END ' . $estigma . ' -->', $block, $pageContent);
        // update content
        $this->page->setContent($newContent);
    }

    /**
     * Cache data handler
     * 
     * @param string $estigma tag to replace
     * @param int $cacheId query cache position
     * @param boolean $mod is public access? 
     * @return none
     * 
     */
    private function replaceDataEstigmas($estigma, $cacheId, $mod) {
        $block = $this->page->getBlock($estigma);
        $blockOld = $block;
        while ($estigmas = BM::getObject('db')->dataFromCache($cacheId)):
            foreach ($estigmas as $estigma => $data):
                $blockNew = $blockOld;
                $blockNew = str_replace("{" . $estigma . "}", $data, $blockNew);
            endforeach;
            $block .= $blockNew;
        endwhile;
        $pageContent = $this->page->getContent();
        if (!$mod):$block = "Access denied";
        endif;
        $newContent = str_replace($blockOld, $block, $pageContent);
        $this->page->setContent($newContent);
    }

    /**
     * bring public access to $this->page private attribute 
     * 
     * @return Object
     * 
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Load template
     * 
     * @return none
     * 
     */
    public function buildFromTemplates() {
        $bits = func_get_args();
        $content = "";
        echo $content;
        foreach ($bits as $bit):
            if (strpos($bit, APP_PATH . 'static/html/') === false):
                $bit = APP_PATH . 'static/html/' . BM::getSetting('skin') . '/templates/' . $bit;
            endif;
            if (file_exists($bit) == true):
                $content .= file_get_contents($bit);
            endif;
        endforeach;
        $this->page->setContent($content); // update content in page builder
    }

    /**
     * transform pair key=>value into estigmas
     * 
     * @param Array $data
     * @param string $prefix
     * @return none
     * 
     */
    public function dataToEstigmas($data, $prefix) {
        foreach ($data as $key => $content):
            $this->page->addEstigma($key . $prefix, $content); // set new estigma
        endforeach;
    }

    /**
     * set new title, this also can be done from template 
     * 
     * @return none
     * 
     */
    public function parseTitle() {
        $newContent = str_replace('<title>', '<title>' . $this->page->getTitle(), $this->page->getContent());
        $this->page->setContent($newContent); // update content
    }

    /**
     * append new css style sheet, this also can be done from template 
     * 
     * @return none
     * 
     */
    public function parseCss() {
        for ($i = 0; $i < (count($this->page->getCss())); $i++):
            $css = $this->page->getCss();
            $newContent = str_replace('</head>', PHP_EOL . '<link rel="stylesheet" type="text/css" href="http://' . $_SERVER['HTTP_HOST'] . '/' . WEB_DIR . $css[$i] . '" media="screen" />' . '</head>', $this->page->getContent());
            $this->page->setContent($newContent);
        endfor;
    }

    /**
     * append new Js file, this also can be done from template 
     * 
     * @return none
     * 
     */
    public function parseJs() {
        for ($i = 0; $i < (count($this->page->getJs())); $i++):
            $js = $this->page->getJs();
            $newContent = str_replace('</head>', PHP_EOL . '<script type="text/javascript" src="http://' . $_SERVER['HTTP_HOST'] . '/' . WEB_DIR . $js[$i] . '"></script>' . '</head>', $this->page->getContent());
            $this->page->setContent($newContent);
        endfor;
    }

    /**
     * parse template document
     * 
     * @return none
     * 
     */
    public function parseOutput() {
        $this->replaceBits(); // any bit? replace it
        $this->replaceTags(); // any estigma? replace it 
    }

    /**
     * parse template extras
     * 
     * @return none
     * 
     */
    public function parseExtras() {
        $this->parseTitle(); // new title? set new title
        $this->parseCss();
        $this->parseJs();
    }

}

?>