<?php

class DGQJoomla2 {

    public static function getDBElements($type, $rel, $typeForced = '', $lang = true, $langForced = '', $access = true) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $result = array();
        $lang_codes = JLanguageHelper::getLanguages('lang_code');
        $user = & JFactory::getUser();
        $userAccessLevels = $user->getAuthorisedViewLevels();

        $query->select('*');
        $query->from('#__'.$type);
        if ($typeForced != '') $type = $typeForced;

        $langForced = ($langForced == '') ? JFactory::getLanguage()->getTag() : DGQLang::getTagSef($langForced, $lang_codes);

        // This query is common
        $langQuery = ($lang) ? ' AND ('.$db->quoteName('language').' LIKE '.$db->quote('*').' OR
                '.$db->quoteName('language').' LIKE '.$db->quote($langForced).')' : '';
        $accessQuery = ($access) ? ' AND '.$db->quoteName('access').' IN ('.implode(',', $userAccessLevels).')' : '';

        switch ($type) {
            case 'content':
                $query->where($db->quoteName('state').' = '.$db->quote('1').$langQuery.' AND
                '.$db->quoteName('attribs').' LIKE '.$db->quote('%dgq_art_tag":"'.$rel.'"%').$accessQuery);
                $query->order('ordering ASC');
                break;
            case 'categories':
                $query->where($db->quoteName('published').' = '.$db->quote('1').$langQuery.' AND
                '.$db->quoteName('params').' LIKE '.$db->quote('%dgq_tag":"'.$rel.'"%').$accessQuery);
                $query->order('lft ASC');
                break;
            case 'search-alias':
                $query->where($db->quoteName('alias').' = '.$db->quote($rel).$langQuery);
                break;
            case 'search-catergory-childs':
                $query->where($db->quoteName('published').' = '.$db->quote('1').' AND
                '.$db->quoteName('parent_id').' = '.$db->quote($rel).$langQuery.$accessQuery);
                $query->order('lft ASC');
                break;
            case 'search-content-catid':
                $query->where($db->quoteName('state').' = '.$db->quote('1').' AND
                '.$db->quoteName('catid').' = '.$db->quote($rel).$langQuery.$accessQuery);
                $query->order('ordering ASC');
                break;
            case 'menu':
                $query->where($db->quoteName('published').' = '.$db->quote('1').$langQuery.' AND
                '.$db->quoteName('menutype').' = '.$db->quote($rel).' AND
                '.$db->quoteName('parent_id').' = '.$db->quote('1').$accessQuery);
                $query->order('lft ASC');
                break;
            case 'search-id':
                $query->where($db->quoteName('id').' = '.$db->quote($rel));
                break;
            case 'search-menu-childs':
                $query->where($db->quoteName('published').' = '.$db->quote('1').$langQuery.' AND
                '.$db->quoteName('parent_id').' = '.$rel.$accessQuery);
                $query->order('lft ASC');
                break;
            case 'search-menu-lang':
                $query->where($db->quoteName('language').' LIKE '.$db->quote(JFactory::getLanguage()->getTag()).' AND
                '.$db->quoteName('params').' LIKE '.$db->quote('%dgq_item_tag":"'.$rel.'"%').$accessQuery);
                break;
        }

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }

    public static function getDBFrom($of, $from, $rel, $typeForced = '', $lang = true, $langForced = '') {
        $fromElem = self::getDBElements($from, $rel, $typeForced, $lang, $langForced);
        switch ($from) {
            case 'categories':
                if (empty($fromElem)) return array();
                return self::getDBElements($of, $fromElem[0]->id, 'search-content-catid', $lang, $langForced);
        }
    }

    /**
     * getJSON:
     *
     * @param $type
     * @param $element
     * @return JRegistry
     */
    public static function getJSON($type, $element) {
        switch ($type){
            case 'attribs':
            case 'params':
                return new JRegistry($element->{$type});
                break;
            default:
                $registry = new JRegistry();
                $column = $registry->loadString($element->{$type});
                return arrayToObj($column->toArray());
                break;
        }
    }

    /**
     * getUri
     *
     * @param $type
     * @param $value
     * @param bool $current
     * @return string
     */
    public static function getUri($type, $value) {
        $menu = JFactory::getApplication()->getMenu();
        $currUrl =& JURI::getInstance();
        $url =& JURI::getInstance('index.php');
        foreach ($currUrl->getQuery(true) as $key => $val) $url->setVar($key, $val);

        $lang_codes = JLanguageHelper::getLanguages('lang_code');
        $url->setVar("lang", $lang_codes[JFactory::getLanguage()->getTag()]->sef);

        switch($type) {
            case 'menu':
                $url =& JURI::getInstance($value->link);
                $url->setVar('Itemid', $value->id);
                $url->setVar("lang", $lang_codes[JFactory::getLanguage()->getTag()]->sef);
                break;
            case 'content':
                $url = ContentHelperRoute::getArticleRoute($value->id, $value->catid, $lang_codes[JFactory::getLanguage()->getTag()]->sef);
                break;
            case 'categories':
                $url = ContentHelperRoute::getCategoryRoute($value->id, $lang_codes[JFactory::getLanguage()->getTag()]->sef);
                break;
            case 'lang':
                $url->setVar('lang', $value);
                break;
        }

        // make a friendly url
        /*$lang = $url->getVar('lang', '');
        $url = JRoute::_($url);
        $url_explode = explode('?', $url, -1);
        $new_url = $url_explode[0].'/'.$lang;*/

        return JRoute::_($url);
    }
}
