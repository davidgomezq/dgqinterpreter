<?php

// No direct access
defined('_JEXEC') or die;

class DGQLang {

    public static function load() {
        $app = JFactory::getApplication();
        $lang_codes = JLanguageHelper::getLanguages('lang_code');

        // get var has priority
        $lang = $app->input->get->get('lang', null);
        $lang = self::getTagSef($lang, $lang_codes);

        // if not get var, cookie has priority
        $lang = ($lang == null) ? self::getLanguageCookie() : $lang;

        $exists = array_key_exists($lang, $lang_codes);

        // if null, lang set is by default
        $lang = ($lang == null || !$exists) ? JFactory::getLanguage()->getTag() : $lang;

        self::changeLanguage($lang);

        if ($app->input->get->get('lang', null) == null) {
            $url =& JURI::getInstance();
            $url->setVar('lang', $lang_codes[$lang]->sef);
            $app->redirect($url);
        }
    }

    public static function changeLanguage($lang_code) {
        $app = JFactory::getApplication();

        // Change joomla language
        $lang = JFactory::getLanguage();
        if ($lang->getTag() != $lang_code) {
            $lang->setLanguage($lang_code);
            $newLang = JLanguage::getInstance($lang_code);

            foreach ($lang->getPaths() as $extension => $files)
                $newLang->load($extension);

            JFactory::$language = $newLang;
            $app->loadLanguage($newLang);
        }

        JFactory::getLanguage()->load('plg_system_bootstrapzer', DGQ_PLUGIN_PATH);

        // cookie
        if (self::getLanguageCookie() != $lang_code)
            self::setLanguageCookie($lang_code);
    }

    public static function getTagSef($lang, $lang_codes) {
        foreach ($lang_codes as $lang_code)
            if ($lang_code->sef == $lang)
                return $lang_code->lang_code;
        return null;
    }

    public static function getExistsSef($lang, $lang_codes) {
        foreach ($lang_codes as $lang_code)
            if ($lang_code->sef == $lang)
                return true;
        return false;
    }


    /**
     * setLanguageCookie: change language of website and create a cookie. Function is copied of Joomla 3.4.8
     *
     * @param $lang_code
     */

    private static function setLanguageCookie($lang_code)
    {
        $app = JFactory::getApplication();
        // Get the cookie lifetime we want.
        //$cookie_expire = 0;
        //if ($this->params->get('lang_cookie', 1) == 1)
        //{
        $cookie_expire = time() + 365 * 86400;
        //}

        // Create a cookie.
        $cookie_domain = $app->get('cookie_domain');
        $cookie_path   = $app->get('cookie_path', '/');
        $cookie_secure = $app->isSSLConnection();
        $app->input->cookie->set(JApplicationHelper::getHash('language'), $lang_code, $cookie_expire, $cookie_path, $cookie_domain, $cookie_secure);
    }

    /**
     * getLanguageCookie: return de value of lang cookie. Function is copied of Joomla 3.4.8
     *
     * @return null|string
     */

    public static function getLanguageCookie()
    {
        $lang_codes = JLanguageHelper::getLanguages('lang_code');
        $app = JFactory::getApplication();
        $lang_code = $app->input->cookie->getString(JApplicationHelper::getHash('language'));

        // Let's be sure we got a valid language code. Fallback to null.
        if (!array_key_exists($lang_code, $lang_codes))
        {
            $lang_code = null;
        }

        return $lang_code;
    }

}
