<?php

// No direct access
defined('_JEXEC') or die;

class DGQAjaxLogin {

    public function login() {
        $ajax = DGQAjaxController::getInstance();
        $response = (DGQLogin::loginUser()) ? DGQ_AJAX_RESPONSE_SUCCESS : DGQ_AJAX_RESPONSE_FAIL;
        $ajax->sendResponse($response);
    }

    public function logout() {
        $ajax = DGQAjaxController::getInstance();
        DGQLogin::logoutUser();
        $ajax->sendResponse(DGQ_AJAX_RESPONSE_SUCCESS);
    }

    // Singleton
    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new static;
        return self::$instance;
    }

}
