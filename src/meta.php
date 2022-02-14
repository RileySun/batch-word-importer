<?php


BatchImport::init();

class BatchImport {

	public static function init() {
		//Init
		add_action('admin_menu', array(__CLASS__, 'adminPage'));
		add_action('admin_post_nopriv_batchImport', array(__CLASS__, 'submitAdminPage'));
		add_action('admin_post_batchImport', array(__CLASS__, 'submitAdminPage'));
	}
	
	public static function adminPage() {
		$hooknameTop = add_menu_page('Batch Import', 'Batch Import', 'manage_options', 'batchimport', array(__CLASS__, 'mainPage'), 'dashicons-book', 25);
		$hooknameSub = add_submenu_page('batchimport', 'Settings', 'Settings', 'manage_options', 'settings', array(__CLASS__, 'settingsPage') );
	}
	
	public static function submitAdminPage() {
		include 'importer.php';
		if (isset($_POST['BatchImport'])) {
			$importer = new Importer;
			$result = $importer::init($_FILES['zip']);
		}
		//wp_redirect( $_SERVER['HTTP_REFERER'] );
		wp_redirect(get_admin_url(null, 'edit.php'));
		exit;
	}
	
	public static function mainPage() {
		include 'templates/main.php';
	}
}