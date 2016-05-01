<?php

class DGQDom {

    protected $_element = null;
    protected $_innerHTML = '';
    protected $_attrs = array();

    function __construct($element) {
        $this->_element = $element;
    }

    public function innerHTML($str = '') {
        if ($str != '')
            $this->_innerHTML = $str;
        else
            return $this->_innerHTML;
    }

    public function outerHTML() {
        return "<$this->_element".$this->attrsToString().">$this->_innerHTML</$this->_element>";
    }

    public function attrs() {
        return $this->_attrs;
    }

    public function append($element) {
        $this->_innerHTML .= (gettype($element) == 'string') ? $element : $element->outerHTML();
    }

    public function prepend($element) {
        $this->_innerHTML = (gettype($element) == 'string') ? $element.$this->_innerHTML : $element->outerHTML().$this->_innerHTML;
    }

    public function setAttribute($attr, $value = '') {
        if (gettype($attr) == 'string')
            $this->_attrs[$attr] = $value;
        else {
            foreach ($attr as $key => $value)
                $this->_attrs[$key] = $value;
        }
    }

    public function setProps($str) {
        if ($str == null) return;
        foreach ($this->parseAttrs($str) as $name => $value)
            $this->_attrs[$name] = $value;
    }

    function parseAttrs($attrs) {
        $html = str_get_html("<element $attrs >");
        return $html->find('element', 0)->attr;
    }

    public function getAttribute($name) {
        return $this->_attrs[$name];
    }

    private function attrsToString() {
        return implode(' ', array_map(function ($k, $v) {
            return " ". $k .'="'. htmlspecialchars($v) .'"';
        }, array_keys($this->_attrs), $this->_attrs));
    }
}
