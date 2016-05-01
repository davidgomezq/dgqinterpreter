<?php
/**
 *  Autor = David Gomez Quilon
 *  Fecha = 3/4/16
 *  Licencia = GPL v3
 *  Versión = 1.0
 *  Descripción =
 */

class DGQAjaxInterpreter {

    public function getContentLoop() {
        $ajax = DGQAjaxController::getInstance();
        $post = JFactory::getApplication()->input->post;

        $rel = $post->get('rel');
        $lang = $post->get('lang');
        $pageAmount = $post->get('amount');
        $page = $post->get('page');

        $content = DGQJoomla2::getDBFrom('content', 'categories', $rel, '', true, $lang);
        $content = paginate($content, $pageAmount, $page);
        $ajax->sendResponse(DGQ_AJAX_RESPONSE_SUCCESS, array(), array('content' => $content, 'id' =>  $post->get('id')));
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
