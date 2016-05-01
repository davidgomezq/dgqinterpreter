<?php

class DGQPregMatcher {

    protected $_document = null;

    function __construct() {}

    /**
     * init:
     */
    public function init() {
        $this->_document = $this->getDoc(true);

        // Translations
        preg_match_all('/\\_\\((.*?)\\)/', $this->getDoc(), $matches);
        $this->pregTranslations($matches[1]);

        // Lang links
        preg_match_all('/\{{lang\[(.*?)\\]}}/', $this->getDoc(), $matches);
        $this->pregLangLinks($matches[1]);
    }

    /**
     * pregTranslations:
     *
     * @param $tags
     */
    private function pregTranslations($tags) {
        $doc = $this->getDoc();
        foreach ($tags as $tag)
            $doc = str_replace("_($tag)", JText::_($tag), $doc);
        $this->setDoc($doc);
    }

    /**
     * pregLangLinks:
     *
     * @param $tags
     */
    private function pregLangLinks($tags) {
        $doc = $this->getDoc();
        $lang_codes = JLanguageHelper::getLanguages('lang_code');
        $lang = JFactory::getLanguage();

        foreach ($tags as $tag) {
            switch ($tag) {
                case 'sef':
                    $doc = str_replace("{{lang[$tag]}}", $lang_codes[$lang->getTag()]->sef, $doc);
                    break;
                case 'title_native':
                    $doc = str_replace("{{lang[$tag]}}", $lang_codes[$lang->getTag()]->title_native, $doc);
                    break;
                default:
                    $doc = str_replace("{{lang[$tag]}}", DGQJoomla2::getUri('lang', $lang_codes[$tag]->sef), $doc);
                    break;
            }
        }
        $this->setDoc($doc);
    }
    /**
     * setDoc:
     *
     * @param $doc
     */
    private function setDoc($doc) {
        $this->_document = $doc;
        DGQController::getInstance()->setDocument($doc);
    }

    /**
     * getDoc:
     *
     * @param bool $external
     * @return null|string
     */
    private function getDoc($external = false) {
        return ($external) ? $this->_document : DGQController::getInstance()->getDocument();
    }


    /********************************
     *     SINGLETON (DON'T TOUCH)
     ********************************/
    protected static $instance = null;

    public static function getInstance() {
        if (self::$instance == null)
            self::$instance = new DGQPregMatcher();
        return self::$instance;
    }
}
