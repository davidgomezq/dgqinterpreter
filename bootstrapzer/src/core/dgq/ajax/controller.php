<?php

// No direct access
defined('_JEXEC') or die;

class DGQAjaxController {

    // Singleton
    protected static $instance = null;

    public static function getInstance() {
        if (self::$instance == null)
            self::$instance = new DGQAjaxController();
        return self::$instance;
    }

    /**
     * sendResponse: Send the ajax response to client.
     *
     * JSON Pattern of response is...
     *
     *      state: success/fail/warning....
     *      errors: array of errors (depends of petition types...)
     *      extra: is json object of other informations (depends of petition types...)
     *
     * @param $state
     * @param $errors
     * @param $extra
     */
    public function sendResponse($state, $errors = array(), $extra = array()) {
        echo json_encode(array(
            'state' => $state,
            'errors' => $errors,
            'extra' => $extra
        ));
        exit;
    }

    public function __construct() {}

    public function register() {
        DGQAjaxRegister::getInstance()->register();
    }

    public function checkRegFields() {
        DGQAjaxRegister::getInstance()->checkField();
    }

    public function login() {
        DGQAjaxLogin::getInstance()->login();
    }

    public function logout() {
        DGQAjaxLogin::getInstance()->logout();
    }

    public function getContentLoop() {
        DGQAjaxInterpreter::getInstance()->getContentLoop();
    }
}

