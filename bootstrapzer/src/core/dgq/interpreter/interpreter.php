<?php

/**
 * Class DGQInterpreter
 *
 * This class is responsible for interpreting the html 'custom' code.
 * Common.php required dependencies and tools Joomlas for operation.
 *
 */
class DGQInterpreter
{

    protected $_document = null;
    protected $_buffer = null;
    protected $_prepares = 1;
    protected $_user = null;

    public function __construct()
    {
        $this->_user = &JFactory::getUser();
    }

    /**
     * init:
     *
     * @param $buffer
     */
    public function init($buffer)
    {
        do {
            $this->preparedDoc($buffer);
            $this->parseTypes();

            // Prepare loops and parseTypes of loop
            for ($i = 0; $i < $this->_prepares; $i++) {
                $this->preparedLoops();
                $this->parseTypes();
            }
        } while ($this->hasDGQTags());
    }

    public function hasDGQTags() {
        $doc = $this->getDoc(true);

        $tags = $doc->find('dgq[type!=ajaxloop]');
        return !empty($tags);
    }

    /********************************
     *      PREPARES FUNCTIONS
     ********************************/

    /**
     * preparedDoc:
     *
     * @param $buffer
     */
    private function preparedDoc($buffer)
    {
        // import blocks
        $bufferBlocks = str_get_html($buffer);
        foreach ($bufferBlocks->find('dgq[type=block') as $tag) {
            if (!isset($tag->file)) continue;

            $this->typeBlock($tag);
        }

        $this->setDoc($bufferBlocks->save());
    }

    /**
     * preparedLoops:
     *
     */
    private function preparedLoops()
    {
        $doc = $this->getDoc(true);

        foreach ($doc->find('dgq[type=loop]') as $tag) {
            if (!isset($tag->of) || !isset($tag->rel)) continue;
            if (isset($tag->from))
                $joomElements = DGQJoomla2::getDBFrom($tag->of, $tag->from, $tag->rel);
            else
                $joomElements = DGQJoomla2::getDBElements($tag->of, $tag->rel);
            $content = '';
            for ($i = 0; $i < count($joomElements); $i++) {
                $tag->find('dgq-limit', 0)->setAttribute('element-id', $i);
                $content .= $tag->innertext;
            }

            $tag->innertext = $content;
        }

        $this->setDoc($doc);

    }

    /**
     * rePrepared
     */
    private function rePrepared()
    {
        $this->_prepares++;
    }

    /********************************
     *      TYPE/PARSING FUNCTIONS
     ********************************/

    /**
     * parseTypes
     */
    private function parseTypes()
    {
        $doc = $this->getDoc(true);

        foreach ($doc->find('dgq[type]') as $tag) {
            switch ($tag->type) {
                case 'user':
                    break;
                case 'block':
                    continue;
                    break;
                case 'loopajax':
                    if (!isset($tag->id)) continue;
                    break;
                case 'form':
                    if (!isset($tag->of)) continue;
                    break;
                case 'js':
                case 'css':
                    if (!isset($tag->file)) continue;
                    break;
                default:
                    if (!isset($tag->of) || !isset($tag->rel)) continue;
                    break;
            }

            $method = 'type' . $tag->type;
            if (!method_exists(__CLASS__, $method)) continue;
            $this->$method($tag);
        }

        $this->setDoc($doc);
    }

    /**
     * typeBlock:
     *
     * @param $tag
     */
    private function typeBlock($tag)
    {
        $theme = JFactory::getApplication()->getTemplate();
        $path = (defined('DGQ_THEME_BLOCK_FILES')) ? JPATH_SITE . DGQ_THEME_CSS_FILES : JPATH_SITE . '/templates/' . $theme . '/layouts/blocks/';
        $tag->outertext = file_get_contents($path . $tag->file);
    }

    /**
     * typeCSS:
     *
     * @param $tag
     * @throws Exception
     */
    private function typeCSS($tag)
    {
        $theme = JFactory::getApplication()->getTemplate();
        $path = (defined('DGQ_THEME_CSS_FILES')) ? DGQ_THEME_CSS_FILES : JURI::root(true) . '/templates/' . $theme . '/layouts/css/';
        $path .= $tag->file . '.css';
        $tag->outertext = "<link rel='stylesheet' href='$path'>";
    }

    private function typeUser($tag)
    {
        if ($this->getUser()->guest) {
            $tag->outertext = '';
            return;
        }

        foreach ($tag->find('dgq[field]') as $field) {
            switch ($field->field) {
                case 'logout':
                    $form = new DGQDom('form');
                    $form->setAttribute('id', 'logout-form');
                    if (isset($field->{'props-form'})) $form->setProps($field->{'props-form'});

                    $button = new DGQDom('button');
                    $button->setAttribute('type', 'submit');
                    if (isset($field->{'props-button'})) $button->setProps($field->{'props-button'});
                    $button->innerHTML((isset($field->{'inner-button'})) ? $field->{'inner-button'} : JText::_(JLOGOUT));

                    $form->append($button);
                    $field->outertext = $form->outerHTML();
                    break;
                default:
                    $field->outertext = $this->getUser()->{$field->field};
                    break;
            }
        }
        $this->cleanHtml($tag);
    }

    /**
     * typeForm:
     *
     * @param $tag
     */
    private function typeForm($tag)
    {
        $bz = DGQController::getInstance();
        $bz->loadJSFile(DGQ_FRAMEWORKS_REL . '/dgq/dgq.core.js');
        loadExtLang('com_users');
        $prefix = '';

        switch ($tag->of) {
            case 'register':
                $prefix = 'register_';
                $bz->loadJSFile(DGQ_FRAMEWORKS_REL . '/jquery/jquery.validate-1.14.0.js');
                $bz->loadJSFile(DGQ_AJAX_PATH . 'users/dgq.ajaxregister.js');
                $inputs = array(
                    'name' => 'text',
                    'username' => 'text',
                    'email' => 'text',
                    'password' => 'password',
                    'password2' => 'password'
                );
                break;
            case 'login':
                $prefix = 'login_';
                $bz->loadJSFile(DGQ_FRAMEWORKS_REL . '/dgq/dgq.core.js');
                $bz->loadJSFile(DGQ_AJAX_PATH . 'users/dgq.ajaxlogin.js');
                if (DGQLogin::loginUser()) {
                    $tag->outertext = '';
                    return;
                }
                $inputs = array(
                    'username' => 'text',
                    'password' => 'password',
                    'remember' => 'checkbox',
                );
                break;
        }
        $contentForm = '';
        foreach ($inputs as $inp => $type) {
            // Group
            $group = new DGQDom('div');
            if (isset($tag->{"props-group-$inp"})) $group->setProps($tag->{"props-group-$inp"});
            // label
            $label = new DGQDom('label');
            if (isset($tag->{"props-label-$inp"})) $label->setProps($tag->{"props-label-$inp"});
            if (isset($tag->{"inner-label-$inp"})) $label->innerHTML($tag->{"inner-label-$inp"});
            $label->setAttribute('for', $inp);
            // input
            $input = new DGQDom('input');
            if (isset($tag->{"props-input-$inp"})) $input->setProps($tag->{"props-input-$inp"});
            $input->setAttribute(array(
                'type' => $type,
                'id' => $prefix . $inp,
                'name' => $prefix . $inp,
            ));

            if (isset($tag->{"props-feedback-$inp"})) {
                $fb = new DGQDom('span');
                $fb->setAttribute('id', $prefix . $inp . '1');
                $fb->setProps($tag->{"props-feedback-$inp"});
            }


            // Append to group
            $group->append($label);
            $group->append($input);
            if (isset($fb)) $group->append($fb);
            $contentForm .= $group->outerHTML();
        }

        $group = new DGQDom('div');
        if (isset($tag->{'props-group-submit'})) $group->setProps($tag->{'props-group-submit'});

        $submit = new DGQDom('button');
        $submit->setAttribute('type', 'submit');
        if (isset($tag->{'props-input-submit'})) $submit->setProps($tag->{'props-input-submit'});
        if (isset($tag->{'inner-submit'})) $submit->innerHTML($tag->{'inner-submit'});

        $group->append($submit);
        $contentForm .= $group->outerHTML();

        $form = new DGQDom('form');
        $form->setAttribute('id', $tag->of . '-form');
        $form->setProps($tag->{'props-form'});
        $form->innerHTML($contentForm);
        $tag->outertext = $form->outerHTML();
    }

    /**
     * typeContent:
     *
     * @param $tag
     */
    private function typeContent($tag)
    {
        $joomElement = DGQJoomla2::getDBElements($tag->of, $tag->rel);
        if (empty($joomElement)) return;
        $this->parseField($tag, $tag->rel, $joomElement[0]);
        $this->cleanHtml($tag);
    }

    /**
     * typeUrl:
     *
     * @param $tag
     */
    private function typeUrl($tag)
    {
        $url =& JURI::getInstance();
        $option = $url->getVar('option', false);
        $id = $url->getVar('id', false);
        if (!$option || !$id) return;

        $of = mb_substr($option, 4);
        $joomElement = DGQJoomla2::getDBElements($of, $id, 'search-id');

        if (empty($joomElement)) return;
        $this->parseField($tag, $tag->rel, $joomElement[0]);
        $this->cleanHtml($tag);
    }

    /**
     * typeList:
     *
     * @param $tag
     */
    private function typeList($tag)
    {
        $content = '';
        switch ($tag->of) {
            case 'categories':
                $master = DGQJoomla2::getDBElements($tag->of, $tag->rel, 'search-alias');
                if (count($master) <= 0) {
                    $this->cleanHtml($tag);
                    return;
                }
                $master = $master[0];
                $content .= $this->parseList($tag, $master, array(
                    'prop-first-list' => $tag->{'prop-first-list'},
                    'back-top' => $tag->{'back-top'},
                ));
                break;
            case 'menu':
                $content .= $this->parseMenu($tag, array(
                    'prop-first-list' => $tag->{'prop-first-list'},
                    'prop-stack-li' => $tag->{'prop-stack-li'},
                    'prop-stack-a' => $tag->{'prop-stack-a'},
                    'inner-stack-a' => $tag->{'inner-stack-a'},
                ));
                break;
        }

        $tag->innertext = $content;
        $this->cleanHtml($tag);
    }

    /**
     * parseMenu:
     *
     * @param $tag
     * @param $vals
     * @return string
     * @throws Exception
     */
    private function parseMenu($tag, $vals)
    {
        $parents = DGQJoomla2::getDBElements($tag->of, $tag->rel);
        if (empty($parents)) return '';
        $vals = (gettype($vals) == 'array') ? arrayToObj($vals) : $vals;
        $menu = JFactory::getApplication()->getMenu();
        $menuIdActive = $menu->getActive()->id;

        $ul = new DGQDom('ul');
        $ul->setProps(getObj($vals, 'prop-first-list', ''));
        foreach ($parents as $parent) {
            $params = DGQJoomla2::getJSON('params', $parent);
            $parentLang = DGQJoomla2::getDBElements('menu', $params->get('dgq_item_tag', ''), 'search-menu-lang');
            $childs = DGQJoomla2::getDBElements($tag->of, $parent->id, 'search-menu-childs');
            $title = (empty($parentLang)) ? $parent->title : $parentLang[0]->title;

            $li = new DGQDom('li');
            $a = new DGQDom('a');

            if (empty($childs)) {
                $a->innerHTML($title);
                $a->setAttribute('href', DGQJoomla2::getUri('menu', $parent));
            } else {
                $li->setProps(getObj($vals, 'prop-stack-li', ''));
                $a->setProps(getObj($vals, 'prop-stack-a', ''));
                $a->innerHTML($title . getObj($vals, 'inner-stack-a', ''));
                $childsHTML = $this->parseMenuChilds($parent, $childs, array(
                    'prop-stack-list' => $tag->{'prop-stack-list'},
                    'html-divider' => $tag->{'html-divider'},
                    'class-subtitle' => $tag->{'class-subtitle'},
                ));
            }
            if ($parent->id == $menuIdActive) $li->setAttribute('class', 'active');

            $li->append($a);
            if (isset($childsHTML))
                $li->append($childsHTML);

            $ul->append($li);
        }

        return (isset($tag->{'without-first-list'})) ? $ul->innerHTML() : $ul->outerHTML();
    }

    /**
     * parseMenuChilds:
     *
     * @param $childs
     * @param $vals
     * @return string
     * @throws Exception
     */
    private function parseMenuChilds($parent, $childs, $vals)
    {
        $menu = JFactory::getApplication()->getMenu();
        $menuIdActive = $menu->getActive()->id;
        $vals = (gettype($vals) == 'array') ? arrayToObj($vals) : $vals;

        $ul = new DGQDom('ul');
        $ul->setProps(getObj($vals, 'prop-stack-list', ''));
        foreach ($childs as $child) {
            $li = new DGQDom('li');
            $a = new DGQDom('a');
            $params = DGQJoomla2::getJSON('params', $child);
            $childLang = DGQJoomla2::getDBElements('menu', $params->get('dgq_item_tag', ''), 'search-menu-lang');
            $title = (empty($childLang)) ? $child->title : $childLang[0]->title;

            // TODO: Implement Childs of child...
            if ($params->get('dgq_subtitle', false)) {
                $li->setAttribute('class', getObj($vals, 'class-subtitle', ''));
                $li->innerHTML($title);
            } else {
                $a->innerHTML($title);
                $a->setAttribute('href', DGQJoomla2::getUri('menu', $child));
                if ($child->id == $menuIdActive) $li->setAttribute('class', 'active');
                $li->append($a);
            }

            $ul->append($li);
            if ($params->get('dgq_divider', false))
                $ul->append(getObj($vals, 'html-divider', ''));
        }

        return $ul->outerHTML();
    }

    /**
     * parseList:
     * - [Called from]: typeList
     *
     * @param $tag
     * @param $parent
     * @param array $vals
     * @return string
     */
    private function parseList($tag, $parent, $vals = array())
    {
        $childs = DGQJoomla2::getDBElements($tag->of, $parent->id, 'search-catergory-childs');
        if (empty($childs)) return '';
        $vals = (gettype($vals) == 'array') ? arrayToObj($vals) : $vals;

        $ul = new DGQDom('ul');
        $ul->setProps(getObj($vals, 'prop-first-list', ''));

        if (isset($vals->{'back-top'})) {
            $back = new DGQDom('a');
            $back->innerHTML($tag->{'back-top'});
            $back->setProps($tag->{'prop-back-top'});
        }

        foreach ($childs as $ch) {
            $params = ($tag->of == 'content') ? DGQJoomla2::getJSON('attribs', $ch) : DGQJoomla2::getJSON('params', $ch);

            $li = new DGQDom('li');
            $a = new DGQDom('a');
            $a->innerHTML($ch->title);
            $a->setAttribute('href', "#" . $params->get('dgq_id_rel', '#'));
            $li->append($a);
            $li->append($this->parseList($tag, $ch, array(
                'prop-first-list' => $tag->{'prop-stack-list'}
            )));
            $ul->append($li);
        }

        if (isset($back))
            $ul->append($back);

        return $ul->outerHTML();
    }

    /**
     * typeLoopAjax:
     *
     * @param $tag
     */
    private function typeLoopAjax($tag)
    {
        $bz = DGQController::getInstance();
        $bz->loadJSFile(DGQ_FRAMEWORKS_REL . '/dgq/dgq.core.js');
        $bz->loadJSFile(DGQ_AJAX_PATH . 'interpreter/dgq.ajaxloop.js');

        $content = DGQJoomla2::getDBFrom('content', 'categories', $tag->rel);
        $cat = DGQJoomla2::getDBElements('categories', $tag->rel);
        $cat_params = DGQJoomla2::getJSON('params', $cat[0]);
        $data = array(
            'rel' => $tag->rel,
            'id' => $tag->id,
            'pagAmount' => (int)$cat_params->get('dgq_amount', 10),
            'pagTotal' => ceil(count($content) / $cat_params->get('dgq_amount', 10)),
        );

        JFactory::getDocument()->addScriptDeclaration("jQuery(document).ready(function($) { $('#$tag->id').loopAjaxController.init(" . json_encode($data) . "); });");
        $tag->outertext = "<div id='$tag->id'>$tag->innertext</div>";
    }

    /**
     * typeLoop:
     *
     * @param $tag
     */
    private function typeLoop($tag)
    {
        if (isset($tag->from))
            $joomElements = DGQJoomla2::getDBFrom($tag->of, $tag->from, $tag->rel);
        else
            $joomElements = DGQJoomla2::getDBElements($tag->of, $tag->rel);

        if (empty($joomElements)) return;
        $invalidLimits = $tag->find("dgq-limit[!element-id]");
        $validLimits = $tag->find("dgq-limit[element-id]");

        if (!empty($validLimits)) {
            foreach ($validLimits as $limit) {
                $joom = $joomElements[$limit->{'element-id'}];
                $this->parseParams($limit, $tag->of, $joom);
                $this->parseField($limit, $tag->rel, $joom, $tag->of);
                $this->cleanHtml($limit);
            }
            $this->cleanHtml($tag);
        } elseif (!empty($invalidLimits))
            $this->rePrepared();
    }

    /********************************
     *   PARSING COMMON FUNCTIONS
     ********************************/

    /**
     * parseParams
     *
     * @param $node
     * @param $type
     * @param $joomElement
     */
    private function parseParams($node, $type, $joomElement)
    {
        $params = ($type == 'content') ? DGQJoomla2::getJSON('attribs', $joomElement) : DGQJoomla2::getJSON('params', $joomElement);
        if ($params->get('dgq_parent_class', false))
            $node->firstChild()->setAttribute('class', $params->get('dgq_parent_class', ''));
        if ($params->get('dgq_id_rel', false))
            $node->firstChild()->setAttribute('id', $params->get('dgq_id_rel', ''));
    }

    /**
     * parseFields:
     *
     * @param $node
     * @param $joomElement
     * @param $rel
     */
    private function parseField($node, $rel, $joomElement, $of = '')
    {
        foreach ($node->find('dgq[field]') as $field) {
            if ($field->for != $rel) continue;
            foreach ($field->attr as $key => $val) {
                $expVal = explode(',', $val);
                if (!isset($joomElement->{$val}) && count($expVal) == 1) {
                    $of = (isset($node->of)) ? $node->of : $of;
                    switch (true) {
                        case (contains('button', $val)):
                            $this->parseButtons($field, $of, $joomElement);
                            break;
                    }
                    continue;
                }
                if (count($expVal) == 1)
                    $field->outertext = $this->parseFieldOptions($node->of, $joomElement, $val, $field);
                else {
                    $params = DGQJoomla2::getJSON($expVal[0], $joomElement);
                    $field->outertext = $this->parseFieldOptions($node->of, $params, $expVal[1], $field);
                }
            }
        }
    }

    /**
     * parseFieldOptions
     *
     * @param $of
     * @param $params
     * @param $val
     * @param $tag
     * @return string
     */
    private function parseFieldOptions($of, $params, $val, $tag)
    {
        switch ($of) {
            case 'content':
                switch ($val) {
                    case 'image_intro':
                        $img = new DGQDom('img');
                        $img->setAttribute(array(
                            'src' => $params->image_intro,
                            'alt' => $params->image_intro_alt
                        ));
                        return $img->outerHTML();
                        break;
                    case 'image_fulltext':
                        $img = new DGQDom('img');
                        $img->setAttribute(array(
                            'src' => $params->image_fulltext,
                            'alt' => $params->image_fulltext_alt
                        ));
                        return $img->outerHTML();
                        break;
                    case 'title':
                        $attribs = DGQJoomla2::getJSON('attribs', $params);
                        if ($attribs->get('link_titles', 0) == 1 && !isset($tag->nolink)) {
                            $a = new DGQDom('a');
                            $a->setAttribute('href', DGQJoomla2::getUri('content', $params));
                            $a->innerHTML($params->title);
                            return $a->outerHTML();
                        } else return $params->title;
                        break;
                    case 'cat_name':
                        echo "aaasds";
                        $cat = DGQJoomla2::getDBElements('categories', $params->catid, 'search-id');
                        return $cat->title;
                        break;
                    case 'introtext':
                        return (isset($tag->size)) ? substr($params->introtext, 0, $tag->size) : $params->introtext;
                        break;
                    default:
                        return (isset($params->{$val})) ? $params->{$val} : '';
                        break;
                }
                break;
            default:
                return (isset($params->{$val})) ? $params->{$val} : '';
                break;
        }
    }

    /**
     * parseButtons:
     *
     * @param $node
     * @param $of
     * @param $joomElement
     */
    private function parseButtons($node, $of, $joomElement)
    {
        preg_match('/button\[(?P<id>\d+)\]/', $node->field, $num);
        if (!isset($num['id'])) return;
        $num = $num['id'];

        $params = ($of == 'content') ? DGQJoomla2::getJSON('attribs', $joomElement) : DGQJoomla2::getJSON('params', $joomElement);
        if ($params == null) return;
        $link = $params->get('dgq_button' . $num . '_link');

        $a = new DGQDom('a');
        $a->setAttribute(array(
            'class' => $params->get('dgq_button' . $num . '_css'),
            'href' => (mb_strlen($link) == 0) ? DGQJoomla2::getUri($of, $joomElement) : $link
        ));
        $a->innerHTML($params->get('dgq_button' . $num . '_name', ''));
        $node->outertext = $a->outerHTML();
    }

    /********************************
     * GETTERS AND SETTERS FUNCTIONS
     ********************************/

    /**
     * getDoc: return doc of multiple forms
     *
     * @param $fromBuffer
     * @param $controller
     * @return string
     */
    public function getDoc($fromBuffer = false, $controller = false)
    {
        if ($fromBuffer) return str_get_html($this->_buffer);
        else return ($controller) ? DGQController::getInstance()->getDocument() : $this->_document;
    }

    /**
     * setDoc: send document to DGQ controller
     *
     * @param $doc
     */
    public function setDoc($doc)
    {
        $this->_buffer = (gettype($doc) == 'object') ? $doc->save() : $doc;
        $this->_document = str_get_html($doc);
        DGQController::getInstance()->setDocument($this->_document);
    }

    /**
     * getUser
     *
     * @return JUser|null
     */
    public function getUser()
    {
        return $this->_user;
    }

    /********************************
     *       LIBS FUNCTIONS
     ********************************/

    /**
     * cleanHTML
     *
     * @param $tag
     */
    private function cleanHtml($tag)
    {
        $tag->outertext = $tag->innertext;
    }


    /********************************
     *     SINGLETON (DON'T TOUCH)
     ********************************/
    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new DGQInterpreter();
        return self::$instance;
    }
}
