<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class plgSystemBootstrapzer extends JPlugin {
	protected $autoloadLanguage = true;

	function onContentPrepareForm($form, $data) {
		$app = JFactory::getApplication();
		$option = $app->input->get('option');

		switch($option) {

			case 'com_menus': {
				if ($app->isAdmin()) {
					JForm::addFormPath(__DIR__ . '/joomla');
					$form->loadFile('menu', false);
				}
				return true;
			}
			case 'com_content': {
				if ($app->isAdmin()) {
					JForm::addFormPath(__DIR__ . '/joomla');
					$form->loadFile('content', false);
				}
				return true;
			}
			case 'com_categories': {
				if ($app->isAdmin()) {
					JForm::addFormPath(__DIR__ . '/joomla');
					$form->loadFile('categories', false);
				}
				return true;
			}
		}
		return true;
	}

	public function onAfterRoute() {
		include_once dirname(__FILE__).'/src/defines.php';
		include_once dirname(__FILE__).'/src/include.php';
	}

	public function onUserLogout() {
		DGQLogin::delCredentialsCookie();
		$session =& JFactory::getSession();
		$session->clear('dgq_auth');
	}
}
