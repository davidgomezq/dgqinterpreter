<?php

function getAttr($value, $else = '') {
    return (is_null($value)) ? $else : $value;
}

function getObj($obj, $property, $else = null) {
    return (isset($obj->{$property})) ? $obj->{$property} : $else;
}

function getArray($array, $index, $else = null) {
    return (in_array($index, $array)) ? $array[$index] : $else;
}

function arrayToObj($array) {
    $object = new stdClass();
    foreach ($array as $key => $value) {
        if (is_array($value))
            $value = arrayToObj($value);
        $object->$key = $value;
    }
    return $object;
}

function loadExtLang($extension) {
    $language =& JFactory::getLanguage();
    $language->load($extension, JPATH_BASE, $language->getTag(), true);
}

function utf8Urldecode($value)
{
    $value = preg_replace('/%([0-9a-f]{2})/ie', 'chr(hexdec($1))', (string) $value);
    return $value;
}


/**
 * contains: Comprueba que existe el contenido dentro de un string
 *
 * @param $needle
 * @param $haystack
 * @return bool
 */
function contains($needle, $haystack) {
    return strpos($haystack, $needle) !== false;
}

function paginate($array, $pageSize, $page = 1) {
    $page = $page < 1 ? 1 : $page;
    $start = ($page - 1) * $pageSize;
    return array_slice($array, $start, $pageSize);
}

function loadJSLanguageKeys($file) {
    if ($jsContents = file_get_contents($file)) {
        $languageKeys = array();
        preg_match_all('/Joomla\.JText\._\(\'(.*?)\'\)\)?/', $jsContents, $languageKeys);
        $languageKeys = $languageKeys[1];
        $loadCore = false;

        foreach ($languageKeys as $lkey) {
            $loadCore = true;
            JText::script($lkey);
        }

        if ($loadCore) {
            $bz = DGQController::getInstance();
            $bz->loadJSFile('/media/system/js/core.js');
            $bz->loadJSFile('/media/system/js/mootools-core.js');
        }
    }
}

function parseFields($struct, $joomElement) {
    foreach ($struct->attr as $key => $val) {
        if ($key != 'field') continue;
        $struct->outertext .= $joomElement->{$val};
    }
}