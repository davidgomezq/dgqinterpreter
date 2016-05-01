<?php

// No direct access
defined('_JEXEC') or die;

/**
 * PLUGIN DEFINES
 */
define('DGQ_PLUGIN', 'plg_system_dgq');
define('DGQ_FOLDER_PLUGIN', 'bootstrapzer');
define('DGQ_PLUGIN_PATH_REL', '/plugins/system/' . DGQ_FOLDER_PLUGIN);
define('DGQ_FRAMEWORKS', JUri::root(true) . '/plugins/system/' . DGQ_FOLDER_PLUGIN. '/fw-web/');
define('DGQ_FRAMEWORKS_REL', '/plugins/system/' . DGQ_FOLDER_PLUGIN. '/fw-web');
define('DGQ_TEMPLATES_PATH', JPATH_BASE . '/templates/');
define('DGQ_PLUGIN_PATH', JPATH_BASE . '/plugins/system/' . DGQ_FOLDER_PLUGIN);

/**
 * AJAX DEFINES
 */

define('DGQ_AJAX_PATH', DGQ_PLUGIN_PATH_REL.'/src/core/dgq/ajax/');
define('DGQ_PLUGIN_AJAX_JS', JUri::root(true) . '/plugins/system/' . DGQ_FOLDER_PLUGIN . '/src/core/dgq/ajax/js/');
define('DGQ_PLUGIN_AJAX_JS_REL', '/plugins/system/' . DGQ_FOLDER_PLUGIN . '/src/core/dgq/ajax/js');
define('DGQ_AJAX_URL', 'index.php?dgq_option=dgq_ajax&method=');
define('DGQ_AJAX_RESPONSE_SUCCESS', 'success');
define('DGQ_AJAX_RESPONSE_WARNING', 'warning');
define('DGQ_AJAX_RESPONSE_FAIL', 'fail');