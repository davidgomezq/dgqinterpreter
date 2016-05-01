<?php

// No direct access
defined('_JEXEC') or die;


$Directory = new RecursiveDirectoryIterator(dirname(__FILE__).'/resources');
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach($Regex as $key => $item) require_once $key;

$Directory = new RecursiveDirectoryIterator(dirname(__FILE__).'/core');
$Iterator = new RecursiveIteratorIterator($Directory);
$Regex = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
foreach($Regex as $key => $item) require_once $key;
