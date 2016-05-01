<?php

// No direct access
defined('_JEXEC') or die;

class DGQController {

    /*************
     * Attributs *
     *************/

    protected $_template = null;
    protected $_app = null;
    protected $_document = '';
    protected $_joom_doc = null;

    /*************
     * SINGLETON *
     *************/
    protected static $instance = null;

    public static function getInstance() {
        if (self::$instance == null)
            self::$instance = new DGQController();
        return self::$instance;
    }

    /*************
     * Construct *
     *************/

    public function __construct() {
        $this->_app      = JFactory::getApplication();
        $this->_template = $this->_app->getTemplate();
        $this->_joom_doc = JFactory::getDocument();
    }

    /**************************
     *   INSTANCE FUNCTIONS   *
     **************************/

    /**
     * init:
     *
     * @param bool $loadFiles
     */
    public function init($loadFiles = true) {
        switch ($this->_app->input->get('dgq_option')) {
            case 'dgq_ajax':
                call_user_func(array(DGQAjaxController::getInstance(), $this->_app->input->get('method')));
                break;
            default:
                $this->loadLayout($loadFiles);
                break;
        }
    }

    /**
     * loadLayout: Controller of all page, is called from index.php of _template
     */

    public function loadLayout($loadFiles) {
        // set language
        DGQLang::load();

        // Login??
        DGQLogin::loginUser();

        // generate path
        $path = DGQ_TEMPLATES_PATH.$this->_template."/layouts/".$this->getStyle().".php";

        if ($loadFiles)
            $this->loadDGQFiles();

        if (is_file($path)) {
            ob_start();
            include $path;
            $buffer = ob_get_contents();
            ob_end_clean();
            DGQInterpreter::getInstance()->init($buffer);
            DGQPregMatcher::getInstance()->init();
            //output
            echo $this->getDocument();
        } else
            self::raiseJoomla('DGQ - INCORRECT RELATIVE PATH OF THE LAYOUTS');
    }

    /**
     * getStyle: return de layout file, configured in joomla admin panel
     *
     * @return null
     * @throws Exception
     */

    private function getStyle() {
        $style = null;
        $set_title = true;

        $menu = $this->_app->getMenu();
        $item_active = $menu->getActive();
        $params = $menu->getParams($item_active->id);
        $style = $params->get('dgq_layout_style', null);

        $url =& JURI::getInstance();
        switch ($url->getVar('view', null)) {
            case 'article':
                $article = DGQJoomla2::getDBElements('content', $url->getVar('id', 0), 'search-id');
                if (count($article) == 0)
                    self::raiseJoomla('DGQ - THE ARTICLE CAN NOT BE LOADED');

                $attribs = DGQJoomla2::getJSON('attribs', $article[0]);
                $style = $attribs->get('dgq_layout_style', null);
                $set_title = false;
                break;
        }

        // set title page for menu item
        $item_lang = DGQJoomla2::getDBElements('menu', $params->get('dgq_item_tag', ''), 'search-menu-lang');
        if (!empty($item_lang) && $set_title) {
            $params_lang = DGQJoomla2::getJSON('params', $item_lang[0]);
            $doc = JFactory::getDocument();
            $doc->setTitle($params_lang->get('page_title', ''));
        }

        if ($style == null)
            self::raiseJoomla('DGQ - THE LAYOUT STYLE CAN NOT BE LOADED');

        return $style;
    }

    /**
     * loadDGQFiles:
     */
    public function loadDGQFiles() {
        // Load required scripts and css
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/jquery/jquery-2.2.0.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/jquery/jquery.url.min.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/jquery/jquery.ajaxqueue.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/bootstrap/bootstrap.min.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/jquery/bootpag.min.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/dgq/dgq.core.js');
        $this->loadJSFile(DGQ_FRAMEWORKS_REL . '/bootstrap/bootstrap-formhelpers.min.js');
        $this->loadCSSFile(DGQ_FRAMEWORKS_REL . '/bootstrap/bootstrap.min.css');
        $this->loadCSSFile(DGQ_FRAMEWORKS_REL . '/bootstrap/bootstrap-formhelpers.min.css');
        $this->loadCSSFile(DGQ_FRAMEWORKS_REL . '/bootstrap/bootstrap.elements.css');
    }

    /**
     * disableJoomlaScripts: Disable the joomla scripts and css styles
     */
    public function disableJoomlaFiles() {
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.min.js']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/system/js/modal.js']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/jquery.min.js']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-noconflict.js']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/bootstrap.min.js']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/jquery-migrate.min.js']);
        unset($this->_joom_doc->_styleSheets[JURI::root(true) . '/media/jui/css/chosen.css']);
        unset($this->_joom_doc->_scripts[JURI::root(true) . '/media/jui/js/chosen.jquery.min.js']);
    }

    /**
     * loadJSFile: This function add js files and languages keys to jdoc:include
     *
     * @param $path
     */
    public function loadJSFile($path) {
        $this->_joom_doc->addScript(JUri::root(true) . $path);
        loadJSLanguageKeys(JPATH_SITE . $path);
    }

    /**
     * loadJSDeclaration: This function add js files and languages keys to jdoc:include
     *
     * @param $path
     */
    public function loadJSDeclaration($str) {
        $this->_joom_doc->addScriptDeclaration($str);
    }

    /**
     * loadCSSFile: This function add css files to jdoc:include
     *
     * @param $path
     */
    public function loadCSSFile($path) {
        $this->_joom_doc->addStyleSheet(JUri::root(true) . $path);
    }

    /**************************
     *    STATIC FUNCTIONS    *
     **************************/

    public static function raiseJoomla($text) {
        if (JError::$legacy) {
            JError::setErrorHandling(E_ERROR, 'die');
            JError::raiseError(500, JText::_($text));
            exit;
        } else
            throw new Exception(JText::_($text), 500);
    }

    /**
     * getParamsPlugin: return de params of bootstrapzer in JRegistry type.
     *
     * @return JRegistry
     */

    public static function getParamsPlugin() {
        $plugin = JPluginHelper::getPlugin('system', 'bootstrapzer');
        $params = new JRegistry($plugin->params);
        return $params;
    }

    /**
     * @return string
     */
    public function getDocument() {
        return $this->_document;
    }

    /**
     * @param string $document
     */
    public function setDocument($document) {
        $this->_document = $document;
    }



}
