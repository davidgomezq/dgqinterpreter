<?php

// No direct access
defined('_JEXEC') or die;

class DGQAjaxRegister
{
    public function checkField() {
        $ajax = DGQAjaxController::getInstance();
        $db =& JFactory::getDBO();
        $app = JFactory::getApplication();
        $post = $app->input->post;

        switch ($app->input->get('field')) {
            case 'username':
                $query = "SELECT COUNT(*) FROM #__users WHERE username = '" . $post->get('register_username', null, 'USERNAME') . "'";
                break;
            case 'email':
                $query = "SELECT COUNT(*) FROM #__users WHERE email = '" . $post->get('register_email', null, 'STRING') . "'";
                break;
        }

        $db->setQuery($query);
        $count = $db->loadResult();
        $response = ($count > 0) ? DGQ_AJAX_RESPONSE_WARNING : DGQ_AJAX_RESPONSE_SUCCESS;
        $ajax->sendResponse($response);
    }

    public function register()
    {
        $ajax = DGQAjaxController::getInstance();
        $post = JFactory::getApplication()->input->post;

        // Go to register joomla
        $user = new JUser();
        $data['name'] = $post->get('register_name');
        $data['username'] = $post->get('register_username', null, 'USERNAME');
        $data['email1'] = $post->get('register_email', null, 'STRING');
        $data['password1'] = $post->get('register_password');
        $data['password2'] = $post->get('register_password2');

        JLoader::register('UsersModelRegistration', JPATH_ROOT . '/components/com_users/models/registration.php');
        $registration = new UsersModelRegistration();
        if (!$registration->register($data))
            $ajax->sendResponse(DGQ_AJAX_RESPONSE_WARNING);

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
