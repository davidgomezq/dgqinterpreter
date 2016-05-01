<?php

// No direct access
defined('_JEXEC') or die;

class DGQLogin {

    public static function loginUser() {
        $app = JFactory::getApplication();
        $plug_params = DGQController::getParamsPlugin();
        $session =& JFactory::getSession();
        $byCookie = true;
        $credentials = array();

        // for session
        if ($session->get('dgq_auth', null) != null)
            $credentials = unserialize($session->get('dgq_auth'));

        // if not session, for cookie
        if (count($credentials) <= 0 && $plug_params->get('dgq_auth_cookie', '0') == '1') {
            $creds = unserialize(self::getCredentialsCookie());
            $credentials = (!$creds) ? array () : $creds;
        }

        // if no, post
        if (count($credentials) <= 0) {
            $byCookie = false;
            $credentials['username'] = $app->input->get->post->get('login_username', null);
            $credentials['password'] = $app->input->get->post->get('login_password', null);
            $credentials['remember'] = $app->input->get->post->get('login_remember', null);
        }

        // Get a database object
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, password')
            ->from('#__users')
            ->where('username =' . $db->quote($credentials['username']) . ' AND block = 0');

        $db->setQuery($query);
        $result = $db->loadObject();

        if ($result) {
            $match = JUserHelper::verifyPassword($credentials['password'], $result->password, $result->id);
            if ($match) {
                // Bring this in line with the rest of the system
                $user = JUser::getInstance($result->id);

                //perform the login action
                $error = $app->login($credentials);

                // Set session
                $session->set('dgq_auth', serialize($credentials));

                // Set cookie
                if (!$byCookie && $credentials['remember'] == 'yes')
                    self::setCredentialsCookie($credentials);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public static function logoutUser() {
        $app = JFactory::getApplication();

        $cookie_creds = unserialize(self::getCredentialsCookie());
        $user_id = self::getUserId($cookie_creds['username']);

        $app->logout($user_id);
    }

    public static function setCredentialsCookie($credentials)
    {
        $app = JFactory::getApplication();
        $plug_params = DGQController::getParamsPlugin();

        if ($plug_params->get('dgq_auth_cookie', '0') == '0')
            return;

        // Get the cookie lifetime we want.
        $cookie_expire = (int) time() + 60 * $plug_params->get('dgq_auth_cooktime', 0);
        if ($plug_params->get('dgq_auth_expire', '0') == '1')
            $cookie_expire = time() + 365 * 86400;


        // Create a cookie.
        $cookie_domain = $app->get('cookie_domain');
        $cookie_path   = $app->get('cookie_path', '/');
        $cookie_secure = $app->isSSLConnection();
        $cookieName = JApplicationHelper::getHash(JUserHelper::getShortHashedUserAgent());
        $app->input->cookie->set($cookieName, serialize($credentials), $cookie_expire, $cookie_path, $cookie_domain, $cookie_secure);
    }

    public static function delCredentialsCookie() {
        $app = JFactory::getApplication();
        $cookie_domain = $app->get('cookie_domain');
        $cookie_path   = $app->get('cookie_path', '/');
        $cookie_secure = $app->isSSLConnection();
        $cookieName = JApplicationHelper::getHash(JUserHelper::getShortHashedUserAgent());
        $app->input->cookie->set($cookieName, '', time() - 1000, $cookie_path, $cookie_domain, $cookie_secure);
    }

    public static function getCredentialsCookie() {
        $app = JFactory::getApplication();
        $cookieName = JApplicationHelper::getHash(JUserHelper::getShortHashedUserAgent());
        $credentials = $app->input->cookie->getString($cookieName);
        return $credentials;

    }

    public static function getUserId($username) {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, password')
            ->from('#__users')
            ->where('username=' . $db->quote($username));

        $db->setQuery($query);
        $result = $db->loadObject();
        return ($result) ? $result->id : null;
    }

    public static function getCredentials() {
        $session =& JFactory::getSession();
        return unserialize($session->get('dgq_auth', array()));
    }
}
